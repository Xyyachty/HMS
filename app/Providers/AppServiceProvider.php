<?php

namespace App\Providers;

use App\Database\PostgresConnection;
use App\Mail\Transport\BrevoApiTransport;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Mail;
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

        // Every pgsql connection binds booleans as boolean literals rather than the
        // integers Laravel defaults to, which emulated prepares — required by the
        // transaction pooler — inline as a bare integer Postgres will not cast.
        // See App\Database\PostgresConnection.
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new PostgresConnection($connection, $database, $prefix, $config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Laravel has no Brevo transport of its own, so the 'brevo' mailer in
        // config/mail.php only resolves once this is registered.
        Mail::extend('brevo', function (array $config) {
            return new BrevoApiTransport(
                (string) ($config['key'] ?? config('services.brevo.key') ?? '')
            );
        });
    }
}
