<?php

namespace App\Observers;

use App\Models\Menu;
use App\Events\AuditEvent;
use Illuminate\Support\Facades\Log;

class MenuObserver
{
    /**
     * Handle the Menu "created" event.
     */
    public function created(Menu $menu): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::created(
                auditableType: get_class($menu),
                auditableId: $menu->id,
                newValues: $menu->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Menu', [
                'error' => $e->getMessage(),
                'menu_id' => $menu->id
            ]);
        }
    }

    /**
     * Handle the Menu "updated" event.
     */
    public function updated(Menu $menu): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::updated(
                auditableType: get_class($menu),
                auditableId: $menu->id,
                oldValues: $menu->getOriginal(),
                newValues: $menu->getAttributes(),
                changedFields: $menu->getDirty()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Menu', [
                'error' => $e->getMessage(),
                'menu_id' => $menu->id
            ]);
        }
    }

    /**
     * Handle the Menu "deleted" event.
     */
    public function deleted(Menu $menu): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($menu),
                auditableId: $menu->id,
                oldValues: $menu->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Menu', [
                'error' => $e->getMessage(),
                'menu_id' => $menu->id
            ]);
        }
    }

    /**
     * Handle the Menu "restored" event.
     */
    public function restored(Menu $menu): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::restored(
                auditableType: get_class($menu),
                auditableId: $menu->id,
                newValues: $menu->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Menu', [
                'error' => $e->getMessage(),
                'menu_id' => $menu->id
            ]);
        }
    }

    /**
     * Handle the Menu "force deleted" event.
     */
    public function forceDeleted(Menu $menu): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($menu),
                auditableId: $menu->id,
                oldValues: $menu->getAttributes()
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para Menu', [
                'error' => $e->getMessage(),
                'menu_id' => $menu->id
            ]);
        }
    }
}
