<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        Carbon::setLocale('id');
        $this->configureRateLimiting();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Configure named rate limiters for registration and password reset.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('registration', function (Request $request) {
            return [
                Limit::perMinute(5)
                    ->by('registration-minute:'.$request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response(
                            'Terlalu banyak percobaan pendaftaran. Silakan coba kembali beberapa saat lagi.',
                            429,
                            $headers
                        );
                    }),
                Limit::perHour(30)
                    ->by('registration-hour:'.$request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response(
                            'Terlalu banyak percobaan pendaftaran. Silakan coba kembali beberapa saat lagi.',
                            429,
                            $headers
                        );
                    }),
            ];
        });

        RateLimiter::for('password-reset-link', function (Request $request) {
            $limits = [
                Limit::perMinute(5)
                    ->by('password-reset-minute:'.$request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response(
                            'Terlalu banyak permintaan reset password. Silakan coba kembali beberapa saat lagi.',
                            429,
                            $headers
                        );
                    }),
                Limit::perHour(30)
                    ->by('password-reset-hour:'.$request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response(
                            'Terlalu banyak permintaan reset password. Silakan coba kembali beberapa saat lagi.',
                            429,
                            $headers
                        );
                    }),
            ];

            $rawIdentifier = trim((string) $request->input('identifier'));
            if ($rawIdentifier !== '') {
                $hashedIdentifier = hash('sha256', strtolower($rawIdentifier));
                $limits[] = Limit::perMinute(3)
                    ->by('password-reset-target:'.$hashedIdentifier)
                    ->response(function (Request $request, array $headers) {
                        return response(
                            'Terlalu banyak permintaan reset password. Silakan coba kembali beberapa saat lagi.',
                            429,
                            $headers
                        );
                    });
            }

            return $limits;
        });
    }
}
