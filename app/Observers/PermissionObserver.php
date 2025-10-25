<?php

namespace App\Observers;

use App\Models\Permission;
use App\Events\AuditEvent;
use Illuminate\Support\Facades\Log;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     */
    public function created(Permission $permission): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::created(
                auditableType: get_class($permission),
                auditableId: $permission->id,
                newValues: $permission->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Permission', [
                'error' => $e->getMessage(),
                'permission_id' => $permission->id
            ]);
        }
    }

    /**
     * Handle the Permission "updated" event.
     */
    public function updated(Permission $permission): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::updated(
                auditableType: get_class($permission),
                auditableId: $permission->id,
                oldValues: $permission->getOriginal(),
                newValues: $permission->getAttributes(),
                changedFields: $permission->getDirty()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Permission', [
                'error' => $e->getMessage(),
                'permission_id' => $permission->id
            ]);
        }
    }

    /**
     * Handle the Permission "deleted" event.
     */
    public function deleted(Permission $permission): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($permission),
                auditableId: $permission->id,
                oldValues: $permission->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Permission', [
                'error' => $e->getMessage(),
                'permission_id' => $permission->id
            ]);
        }
    }

    /**
     * Handle the Permission "restored" event.
     */
    public function restored(Permission $permission): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::restored(
                auditableType: get_class($permission),
                auditableId: $permission->id,
                newValues: $permission->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Permission', [
                'error' => $e->getMessage(),
                'permission_id' => $permission->id
            ]);
        }
    }

    /**
     * Handle the Permission "force deleted" event.
     */
    public function forceDeleted(Permission $permission): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($permission),
                auditableId: $permission->id,
                oldValues: $permission->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Permission', [
                'error' => $e->getMessage(),
                'permission_id' => $permission->id
            ]);
        }
    }
}
