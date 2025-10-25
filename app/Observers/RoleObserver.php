<?php

namespace App\Observers;

use App\Models\Role;
use App\Events\AuditEvent;
use Illuminate\Support\Facades\Log;

class RoleObserver
{
    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::created(
                auditableType: get_class($role),
                auditableId: $role->id,
                newValues: $role->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id
            ]);
        }
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::updated(
                auditableType: get_class($role),
                auditableId: $role->id,
                oldValues: $role->getOriginal(),
                newValues: $role->getAttributes(),
                changedFields: $role->getDirty()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id
            ]);
        }
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($role),
                auditableId: $role->id,
                oldValues: $role->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id
            ]);
        }
    }

    /**
     * Handle the Role "restored" event.
     */
    public function restored(Role $role): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::restored(
                auditableType: get_class($role),
                auditableId: $role->id,
                newValues: $role->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id
            ]);
        }
    }

    /**
     * Handle the Role "force deleted" event.
     */
    public function forceDeleted(Role $role): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($role),
                auditableId: $role->id,
                oldValues: $role->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id
            ]);
        }
    }
}
