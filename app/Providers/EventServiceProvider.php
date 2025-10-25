<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\ServiceStatusUpdated;
use App\Events\AuditEvent;
use App\Listeners\SendWhatsAppNotification;
use App\Listeners\AuditLogListener;
use App\Listeners\AuthAuditListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ServiceStatusUpdated::class => [
            SendWhatsAppNotification::class,
        ],
        // Eventos de auditoria
        AuditEvent::class => [
            AuditLogListener::class,
        ],
        // Eventos de autenticação
        Login::class => [
            AuthAuditListener::class . '@handleLogin',
        ],
        Logout::class => [
            AuthAuditListener::class . '@handleLogout',
        ],
        Failed::class => [
            AuthAuditListener::class . '@handleFailed',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Registrar observers para auditoria
        $this->registerObservers();
    }

    /**
     * Registrar observers para auditoria
     */
    private function registerObservers(): void
    {
        // Observers para modelos importantes
        \App\Models\Menu::observe(\App\Observers\MenuObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Role::observe(\App\Observers\RoleObserver::class);
        \App\Models\Permission::observe(\App\Observers\PermissionObserver::class);
        \App\Models\Service::observe(\App\Observers\ServiceObserver::class);
        \App\Models\Checklist::observe(\App\Observers\ChecklistObserver::class);
        \App\Models\Office::observe(\App\Observers\OfficeObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
