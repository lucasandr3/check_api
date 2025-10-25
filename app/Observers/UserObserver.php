<?php

namespace App\Observers;

use App\Models\User;
use App\Events\AuditEvent;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::created(
                auditableType: get_class($user),
                auditableId: $user->id,
                newValues: $this->getSafeUserAttributes($user)
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para User', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::updated(
                auditableType: get_class($user),
                auditableId: $user->id,
                oldValues: $this->getSafeUserAttributes($user, true),
                newValues: $this->getSafeUserAttributes($user),
                changedFields: $this->getSafeChangedFields($user)
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para User', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($user),
                auditableId: $user->id,
                oldValues: $this->getSafeUserAttributes($user)
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para User', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::restored(
                auditableType: get_class($user),
                auditableId: $user->id,
                newValues: $this->getSafeUserAttributes($user)
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para User', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        try {
            // Disparar evento de auditoria de forma assíncrona
            AuditEvent::deleted(
                auditableType: get_class($user),
                auditableId: $user->id,
                oldValues: $this->getSafeUserAttributes($user)
            )->dispatch()->afterResponse();

        } catch (\Exception $e) {
            Log::error('Erro ao criar evento de auditoria para User', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }
    }

    /**
     * Obter atributos seguros do usuário (sem senha)
     */
    private function getSafeUserAttributes(User $user, bool $isOriginal = false): array
    {
        $attributes = $isOriginal ? $user->getOriginal() : $user->getAttributes();
        
        // Remover campos sensíveis
        unset($attributes['password']);
        unset($attributes['remember_token']);
        unset($attributes['email_verified_at']);
        
        return $attributes;
    }

    /**
     * Obter campos alterados seguros
     */
    private function getSafeChangedFields(User $user): array
    {
        $changedFields = $user->getDirty();
        
        // Remover campos sensíveis
        unset($changedFields['password']);
        unset($changedFields['remember_token']);
        unset($changedFields['email_verified_at']);
        
        return $changedFields;
    }
}
