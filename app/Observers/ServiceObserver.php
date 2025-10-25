<?php

namespace App\Observers;

use App\Models\Service;
use App\Events\AuditEvent;
use Illuminate\Support\Facades\Log;

class ServiceObserver
{
    /**
     * Handle the Service "created" event.
     */
    public function created(Service $service): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::created(
                auditableType: get_class($service),
                auditableId: $service->id,
                newValues: $service->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Service', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);
        }
    }

    /**
     * Handle the Service "updated" event.
     */
    public function updated(Service $service): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::updated(
                auditableType: get_class($service),
                auditableId: $service->id,
                oldValues: $service->getOriginal(),
                newValues: $service->getAttributes(),
                changedFields: $service->getDirty()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Service', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);
        }
    }

    /**
     * Handle the Service "deleted" event.
     */
    public function deleted(Service $service): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($service),
                auditableId: $service->id,
                oldValues: $service->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Service', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);
        }
    }

    /**
     * Handle the Service "restored" event.
     */
    public function restored(Service $service): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::restored(
                auditableType: get_class($service),
                auditableId: $service->id,
                newValues: $service->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Service', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);
        }
    }

    /**
     * Handle the Service "force deleted" event.
     */
    public function forceDeleted(Service $service): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($service),
                auditableId: $service->id,
                oldValues: $service->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Service', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);
        }
    }
}
