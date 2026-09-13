<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class AdminSettingsController extends Controller
{
    /**
     * @var array<string, array{0: string, 1: string, 2: string, 3: bool}>
     */
    private array $editableSettings = [
        'school_name' => ['identity', 'school_name', 'string', true],
        'motto' => ['identity', 'motto', 'string', true],
        'default_title' => ['seo', 'default_title', 'string', true],
        'default_description' => ['seo', 'default_description', 'string', true],
        'primary_email' => ['contact', 'primary_email', 'string', true],
        'telephone' => ['contact', 'telephone', 'string', true],
        'address' => ['contact', 'address', 'string', true],
        'analytics_enabled' => ['analytics', 'enabled', 'boolean', true],
        'analytics_provider' => ['analytics', 'provider', 'string', true],
        'analytics_site_id' => ['analytics', 'site_id', 'string', true],
    ];

    public function update(Request $request): RedirectResponse
    {
        Gate::authorize('update', SiteSetting::class);

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'motto' => ['nullable', 'string', 'max:255'],
            'default_title' => ['required', 'string', 'max:255'],
            'default_description' => ['nullable', 'string', 'max:320'],
            'primary_email' => ['nullable', 'email', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'analytics_enabled' => ['nullable', 'boolean'],
            'analytics_provider' => ['nullable', 'in:none,plausible'],
            'analytics_site_id' => ['nullable', 'string', 'max:255', 'required_if:analytics_provider,plausible'],
        ]);

        $oldValues = $this->currentValues();

        DB::transaction(function () use ($request, $validated, $oldValues): void {
            foreach ($this->editableSettings as $input => [$group, $key, $type, $public]) {
                $settingName = $group.'.'.$key;
                $value = $type === 'boolean'
                    ? ($request->has($input)
                        ? $request->boolean($input)
                        : filter_var($oldValues[$settingName] ?? false, FILTER_VALIDATE_BOOLEAN))
                    : ($validated[$input] ?? $oldValues[$settingName] ?? '');

                SiteSetting::query()->updateOrCreate(
                    ['group_name' => $group, 'setting_key' => $key],
                    [
                        'value_type' => $type,
                        'value_json' => ['value' => $value],
                        'is_public' => $public,
                        'updated_by' => $request->user()?->id,
                    ]
                );
            }

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'settings.updated',
                'subject_type' => SiteSetting::class,
                'subject_id' => null,
                'description' => 'Site settings updated from visible admin settings screen.',
                'old_values' => $oldValues,
                'new_values' => $this->currentValues(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.settings')
            ->with('status', 'Settings saved.');
    }

    /**
     * @return array<string, string>
     */
    private function currentValues(): array
    {
        return SiteSetting::query()
            ->where(function ($query): void {
                foreach ($this->editableSettings as [$group, $key]) {
                    $query->orWhere(fn ($settingQuery) => $settingQuery
                        ->where('group_name', $group)
                        ->where('setting_key', $key));
                }
            })
            ->get()
            ->mapWithKeys(fn (SiteSetting $setting): array => [
                $setting->group_name.'.'.$setting->setting_key => (string) ($setting->value_json['value'] ?? ''),
            ])
            ->all();
    }
}
