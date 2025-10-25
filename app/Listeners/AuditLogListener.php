<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AuditLogListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        try {
            // Criar log de auditoria
            AuditLog::create([
                'event_type' => $event->eventType,
                'auditable_type' => $event->auditableType,
                'auditable_id' => $event->auditableId,
                'user_id' => $event->userId,
                'user_email' => $event->userEmail,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'route_name' => $event->routeName,
                'method' => $event->method,
                'old_values' => $event->oldValues,
                'new_values' => $event->newValues,
                'changed_fields' => $event->changedFields,
                'description' => $event->description,
                'metadata' => $event->metadata
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar evento de auditoria', [
                'error' => $e->getMessage(),
                'event' => $event
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, \Throwable $exception): void
    {
        Log::error('Falha ao processar evento de auditoria', [
            'error' => $exception->getMessage(),
            'event' => $event
        ]);
    }
}
