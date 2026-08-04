<?php

namespace Tests\Feature;

use App\Models\AdmissionEnquiry;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MySqlFoundationTest extends TestCase
{
    public function test_mysql_connection_is_live_for_tests(): void
    {
        $this->skipIfMySqlIsUnavailable();

        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('scb_school_test', DB::connection()->getDatabaseName());
        $this->assertNotNull(DB::selectOne('select version() as version')->version);
    }

    public function test_seed_data_creates_required_domain_records(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $this->assertDatabaseHas('site_settings', ['group_name' => 'identity', 'setting_key' => 'school_name']);
        $this->assertDatabaseHas('menus', ['location' => 'primary', 'status' => 'active']);
        $this->assertDatabaseHas('pages', ['slug' => 'home', 'status' => 'published']);
        $this->assertDatabaseHas('media', ['stored_name' => 'scb-logo-original.jpg', 'is_protected_asset' => true]);
        $this->assertDatabaseHas('roles', ['slug' => 'super-administrator']);
        $this->assertDatabaseHas('admission_enquiries', ['reference_code' => 'ADM-LOCAL-0001']);

        $this->assertGreaterThanOrEqual(1, Role::query()->whereHas('permissions')->count());
    }

    public function test_publication_and_media_safeguarding_scopes_filter_records(): void
    {
        $this->prepareMySqlSchema();

        Page::factory()->create(['slug' => 'published-page', 'status' => 'published', 'published_at' => now()->subMinute()]);
        Page::factory()->create(['slug' => 'draft-page', 'status' => 'draft', 'published_at' => now()->subMinute()]);
        Post::factory()->create(['slug' => 'future-post', 'status' => 'published', 'published_at' => now()->addDay()]);

        Media::factory()->create(['stored_name' => 'safe.jpg', 'visibility' => 'public', 'consent_required' => true, 'consent_confirmed' => true]);
        Media::factory()->create(['stored_name' => 'unsafe.jpg', 'visibility' => 'public', 'consent_required' => true, 'consent_confirmed' => false]);

        $this->assertTrue(Page::query()->published()->where('slug', 'published-page')->exists());
        $this->assertFalse(Page::query()->published()->where('slug', 'draft-page')->exists());
        $this->assertFalse(Post::query()->published()->where('slug', 'future-post')->exists());
        $this->assertTrue(Media::query()->publiclyVisible()->where('stored_name', 'safe.jpg')->exists());
        $this->assertFalse(Media::query()->publiclyVisible()->where('stored_name', 'unsafe.jpg')->exists());
    }

    public function test_foreign_keys_and_unique_constraints_are_enforced(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $this->expectException(QueryException::class);

        AdmissionEnquiry::query()->create([
            'reference_code' => 'ADM-LOCAL-0001',
            'guardian_name' => 'Duplicate',
            'email' => 'duplicate@example.test',
            'telephone' => '+27 00 000 0003',
            'consent_confirmed' => true,
        ]);
    }
}
