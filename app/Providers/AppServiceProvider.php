<?php

namespace App\Providers;

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
        // Auditoría central (D-20): EscribirAuditoria se registra por el
        // auto-descubrimiento de listeners de app/Listeners (Laravel 12); no
        // registrarlo también aquí o cada evento auditaría dos veces.
    }
}
