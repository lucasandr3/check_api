<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AuthAuditListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        try {
            $user = $event->user;
            $request = request();

            AuditLog::create([
                'event_type' => 'login',
                'auditable_type' => get_class($user),
                'auditable_id' => $user->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route_name' => $request->route()?->getName(),
                'method' => $request->method(),
                'description' => 'Login realizado com sucesso',
                'metadata' => [
                    'model_class' => get_class($user),
                    'event_type' => 'login',
                    'timestamp' => now()->toISOString(),
                    'guard' => $event->guard ?? 'web'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao criar log de auditoria de login', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null
            ]);
        }
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event): void
    {
        try {
            $user = $event->user;
            $request = request();

            if ($user) {
                AuditLog::create([
                    'event_type' => 'logout',
                    'auditable_type' => get_class($user),
                    'auditable_id' => $user->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'route_name' => $request->route()?->getName(),
                    'method' => $request->method(),
                    'description' => 'Logout realizado',
                    'metadata' => [
                        'model_class' => get_class($user),
                        'event_type' => 'logout',
                        'timestamp' => now()->toISOString(),
                        'guard' => $event->guard ?? 'web'
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao criar log de auditoria de logout', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null
            ]);
        }
    }

    /**
     * Handle failed login attempts.
     */
    public function handleFailed(Failed $event): void
    {
        try {
            $request = request();

            AuditLog::create([
                'event_type' => 'login_failed',
                'auditable_type' => 'User',
                'auditable_id' => 0,
                'user_id' => null,
                'user_email' => $event->credentials['email'] ?? 'unknown',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route_name' => $request->route()?->getName(),
                'method' => $request->method(),
                'description' => 'Tentativa de login falhou',
                'metadata' => [
                    'model_class' => 'User',
                    'event_type' => 'login_failed',
                    'timestamp' => now()->toISOString(),
                    'guard' => $event->guard ?? 'web',
                    'credentials' => array_keys($event->credentials)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao criar log de auditoria de login falhou', [
                'error' => $e->getMessage(),
                'email' => $event->credentials['email'] ?? 'unknown'
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, \Throwable $exception): void
    {
        Log::error('Falha ao processar evento de auditoria de autenticação', [
            'error' => $exception->getMessage(),
            'event' => $event
        ]);
    }
}
