<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        if (empty(config('services.google.redirect'))) {
            config(['services.google.redirect' => rtrim((string) config('app.url'), '/').'/auth/google/callback']);
        }

        if (empty(config('services.facebook.redirect'))) {
            config(['services.facebook.redirect' => rtrim((string) config('app.url'), '/').'/auth/facebook/callback']);
        }

        \Illuminate\Support\Facades\DB::prohibitDestructiveCommands(
            $this->app->isProduction()
        );
    }
}
