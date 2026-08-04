<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
        if (! $this->app->runningInConsole()) {
            return;
        }

        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            if (! in_array($event->command, ['migrate:fresh', 'db:wipe'], true)) {
                return;
            }

            $environmentAllowed = $this->app->environment(['local', 'testing']);
            $databaseAllowed = in_array(DB::connection()->getDatabaseName(), ['scb_school', 'scb_school_test'], true);

            if (! $environmentAllowed || ! $databaseAllowed) {
                throw new \RuntimeException(
                    'Destructive database commands are allowed only for local/testing MySQL schemas.'
                );
            }
        });
    }
}
