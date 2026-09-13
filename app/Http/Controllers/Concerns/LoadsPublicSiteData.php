<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;

trait LoadsPublicSiteData
{
    /**
     * @return array<string, mixed>
     */
    protected function publicSiteData(Page $page): array
    {
        return [
            ...$this->publicChromeData(),
            'page' => $page,
            'blocks' => $page->blocks->keyBy('block_type'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function publicChromeData(): array
    {
        $settings = SiteSetting::query()
            ->where('is_public', true)
            ->get()
            ->mapWithKeys(fn (SiteSetting $setting) => [
                $setting->group_name.'.'.$setting->setting_key => $setting->value_json['value'] ?? null,
            ]);

        $menus = Menu::query()
            ->where('status', 'active')
            ->whereIn('location', ['primary', 'footer-school', 'footer-resources', 'footer-legal'])
            ->with(['items' => fn ($query) => $query->active()->with('page')])
            ->get()
            ->keyBy('location');

        $latestNewsAlert = Post::query()
            ->published()
            ->where('published_at', '>=', now()->subDays(14))
            ->latest('published_at')
            ->first(['id', 'title', 'slug', 'published_at']);

        return [
            'settings' => $settings,
            'publicContact' => [
                'address' => $this->safePublicSetting($settings->get('contact.address')),
                'telephone' => $this->safePublicSetting($settings->get('contact.telephone')),
                'email' => $this->safePublicSetting($settings->get('contact.primary_email'), true),
            ],
            'menus' => $menus,
            'logo' => asset('assets/images/brand/scb-logo-original.jpg'),
            'latestNewsAlert' => $latestNewsAlert,
        ];
    }

    private function safePublicSetting(mixed $value, bool $email = false): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);
        $unsafeMarkers = ['placeholder', 'unverified', 'example.test'];

        foreach ($unsafeMarkers as $marker) {
            if (str_contains(strtolower($value), $marker)) {
                return null;
            }
        }

        if ($email && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return null;
        }

        return $value;
    }

    protected function publicPage(string $slug): Page
    {
        return Page::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'featuredMedia' => fn ($query) => $query->publiclyVisible(),
                'blocks' => fn ($query) => $query
                    ->visible()
                    ->with(['media' => fn ($query) => $query->publiclyVisible()]),
            ])
            ->firstOrFail();
    }
}
