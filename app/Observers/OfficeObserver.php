<?php

namespace App\Observers;

use App\Models\Office;
use App\Events\AuditEvent;
use Illuminate\Support\Facades\Log;

class OfficeObserver
{
    /**
     * Handle the Office "created" event.
     */
    public function created(Office $office): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::created(
                auditableType: get_class($office),
                auditableId: $office->id,
                newValues: $office->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Office', [
                'error' => $e->getMessage(),
                'office_id' => $office->id
            ]);
        }
    }

    /**
     * Handle the Office "updated" event.
     */
    public function updated(Office $office): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::updated(
                auditableType: get_class($office),
                auditableId: $office->id,
                oldValues: $office->getOriginal(),
                newValues: $office->getAttributes(),
                changedFields: $office->getDirty()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Office', [
                'error' => $e->getMessage(),
                'office_id' => $office->id
            ]);
        }
    }

    /**
     * Handle the Office "deleted" event.
     */
    public function deleted(Office $office): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($office),
                auditableId: $office->id,
                oldValues: $office->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Office', [
                'error' => $e->getMessage(),
                'office_id' => $office->id
            ]);
        }
    }

    /**
     * Handle the Office "restored" event.
     */
    public function restored(Office $office): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::restored(
                auditableType: get_class($office),
                auditableId: $office->id,
                newValues: $office->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Office', [
                'error' => $e->getMessage(),
                'office_id' => $office->id
            ]);
        }
    }

    /**
     * Handle the Office "force deleted" event.
     */
    public function forceDeleted(Office $office): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($office),
                auditableId: $office->id,
                oldValues: $office->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Office', [
                'error' => $e->getMessage(),
                'office_id' => $office->id
            ]);
        }
    }
}
