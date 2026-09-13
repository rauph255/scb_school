<?php

namespace App\Providers;

use App\Models\AdmissionEnquiry;
use App\Models\ContactMessage;
use App\Models\Download;
use App\Models\Event as SchoolEvent;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Programme;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\StaffMember;
use App\Models\User;
use App\Policies\AdmissionEnquiryPolicy;
use App\Policies\ContactMessagePolicy;
use App\Policies\DownloadPolicy;
use App\Policies\EventPolicy;
use App\Policies\FaqPolicy;
use App\Policies\GalleryPolicy;
use App\Policies\MediaPolicy;
use App\Policies\PagePolicy;
use App\Policies\PostPolicy;
use App\Policies\ProgrammePolicy;
use App\Policies\RolePolicy;
use App\Policies\SiteSettingPolicy;
use App\Policies\StaffMemberPolicy;
use App\Policies\UserPolicy;
use App\Support\AdminAlertFeed;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Config::set('database.connections', [
            'mysql' => Config::get('database.connections.mysql'),
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(SchoolEvent::class, EventPolicy::class);
        Gate::policy(Faq::class, FaqPolicy::class);
        Gate::policy(Gallery::class, GalleryPolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(SiteSetting::class, SiteSettingPolicy::class);
        Gate::policy(AdmissionEnquiry::class, AdmissionEnquiryPolicy::class);
        Gate::policy(ContactMessage::class, ContactMessagePolicy::class);
        Gate::policy(Download::class, DownloadPolicy::class);
        Gate::policy(StaffMember::class, StaffMemberPolicy::class);
        Gate::policy(Programme::class, ProgrammePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        RateLimiter::for('public-forms', fn (Request $request): array => [
            Limit::perMinute(5)->by('public-form-ip:'.$request->ip()),
            Limit::perHour(10)->by('public-form-email:'.hash('sha256', mb_strtolower((string) $request->input('email')))),
        ]);
        RateLimiter::for('authentication', fn (Request $request): array => [
            Limit::perMinute(5)->by('auth-ip:'.$request->ip()),
            Limit::perMinute(5)->by('auth-account:'.hash('sha256', mb_strtolower((string) $request->input('email')))),
        ]);
        RateLimiter::for('search', fn (Request $request): Limit => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('media', fn (Request $request): Limit => Limit::perMinute(180)->by($request->ip()));
        RateLimiter::for('downloads', fn (Request $request): Limit => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('admin-mail', fn (Request $request): Limit => Limit::perMinute(10)->by((string) $request->user()?->id));

        View::composer('admin.layout', function (\Illuminate\View\View $view): void {
            $view->with('adminAlerts', app(AdminAlertFeed::class)->for(auth()->user()));
        });

        if (! $this->app->runningInConsole()) {
            return;
        }

        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            if (! in_array($event->command, [
                'db:seed',
                'db:wipe',
                'migrate:fresh',
                'migrate:refresh',
                'migrate:reset',
                'migrate:rollback',
            ], true)) {
                return;
            }

            $environmentAllowed = $this->app->environment(['local', 'testing']);
            $databaseAllowed = in_array(DB::connection()->getDatabaseName(), ['scb_school', 'scb_school_test'], true);

            if (! $environmentAllowed || ! $databaseAllowed) {
                throw new \RuntimeException(
                    'Destructive database and development seed commands are allowed only for approved local/testing MySQL schemas.'
                );
            }
        });
    }
}
