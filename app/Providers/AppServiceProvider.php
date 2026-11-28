<?php

namespace App\Providers;

use App\Mail\BrevoTransport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

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
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Mail::extend('brevo', function (array $config = []) {
            return new BrevoTransport((string) ($config['key'] ?? ''));
        });

        // showing a friendly error avoiding the error page 429 too many requests
        RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(5)
            ->by($request->ip())
            ->response(function (Request $request, array $headers) {
                $seconds = $headers['Retry-After'] ?? 60;
                return back()
                    ->withErrors(['dwcc_email' => "Too many login attempts. Please wait {$seconds} seconds."])
                    ->withInput();
            });
    });
    }
}
