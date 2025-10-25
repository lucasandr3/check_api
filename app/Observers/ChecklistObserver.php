<?php

namespace App\Observers;

use App\Models\Checklist;
use App\Events\AuditEvent;
use Illuminate\Support\Facades\Log;

class ChecklistObserver
{
    /**
     * Handle the Checklist "created" event.
     */
    public function created(Checklist $checklist): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::created(
                auditableType: get_class($checklist),
                auditableId: $checklist->id,
                newValues: $checklist->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Checklist', [
                'error' => $e->getMessage(),
                'checklist_id' => $checklist->id
            ]);
        }
    }

    /**
     * Handle the Checklist "updated" event.
     */
    public function updated(Checklist $checklist): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::updated(
                auditableType: get_class($checklist),
                auditableId: $checklist->id,
                oldValues: $checklist->getOriginal(),
                newValues: $checklist->getAttributes(),
                changedFields: $checklist->getDirty()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Checklist', [
                'error' => $e->getMessage(),
                'checklist_id' => $checklist->id
            ]);
        }
    }

    /**
     * Handle the Checklist "deleted" event.
     */
    public function deleted(Checklist $checklist): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($checklist),
                auditableId: $checklist->id,
                oldValues: $checklist->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Checklist', [
                'error' => $e->getMessage(),
                'checklist_id' => $checklist->id
            ]);
        }
    }

    /**
     * Handle the Checklist "restored" event.
     */
    public function restored(Checklist $checklist): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::restored(
                auditableType: get_class($checklist),
                auditableId: $checklist->id,
                newValues: $checklist->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Checklist', [
                'error' => $e->getMessage(),
                'checklist_id' => $checklist->id
            ]);
        }
    }

    /**
     * Handle the Checklist "force deleted" event.
     */
    public function forceDeleted(Checklist $checklist): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($checklist),
                auditableId: $checklist->id,
                oldValues: $checklist->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Checklist', [
                'error' => $e->getMessage(),
                'checklist_id' => $checklist->id
            ]);
        }
    }
}
