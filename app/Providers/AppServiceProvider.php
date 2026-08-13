<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        if ($this->app->environment('production') || isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        $this->configureRateLimiting();
        $this->configurePasswordDefaults();
    }

    /**
     * Definir los limitadores de peticiones del sistema para mitigar
     * fuerza bruta y abuso (DoS) en los puntos de entrada expuestos.
     */
    protected function configureRateLimiting(): void
    {
        // Login web (sesión)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Login API (App móvil) — atado también al correo para evitar llenado de IPs
        RateLimiter::for('api-login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));
            return Limit::perMinute(5)->by($request->ip().'|'.$email);
        });

        // Rutas públicas QR (solicitudes y seguimiento) — anti-spam / anti-DoS
        RateLimiter::for('public-qr', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }

    /**
     * Política de contraseñas fuerte y uniforme en toda la aplicación.
     */
    protected function configurePasswordDefaults(): void
    {
        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });
    }
}
