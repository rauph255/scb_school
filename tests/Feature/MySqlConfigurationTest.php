<?php

namespace Tests\Feature;

use Tests\TestCase;

class MySqlConfigurationTest extends TestCase
{
    public function test_application_uses_mysql_as_the_only_relational_connection(): void
    {
        $this->assertSame('mysql', config('database.default'));
        $this->assertSame(['mysql'], array_keys(config('database.connections')));
        $this->assertSame('InnoDB', config('database.connections.mysql.engine'));
        $this->assertSame('utf8mb4', config('database.connections.mysql.charset'));
        $this->assertSame('utf8mb4_unicode_ci', config('database.connections.mysql.collation'));
    }

    public function test_database_backed_runtime_drivers_are_configured(): void
    {
        $this->assertSame('database', config('session.driver'));
        $this->assertSame('database', config('cache.default'));
        $this->assertSame('database', config('queue.default'));
    }
}
