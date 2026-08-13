<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Sanctum ships its own personal_access_tokens migration. This app never
        // issues tokens — User does not use HasApiTokens and the only sanctum route
        // is stock scaffolding — and the table was deliberately dropped from the
        // live database, so keep it out of a fresh install too.
        Sanctum::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
