<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuditEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $eventType,
        public string $auditableType,
        public int $auditableId,
        public ?int $userId,
        public ?string $userEmail,
        public ?string $ipAddress,
        public ?string $userAgent,
        public ?string $routeName,
        public string $method,
        public ?array $oldValues = null,
        public ?array $newValues = null,
        public ?array $changedFields = null,
        public ?string $description = null,
        public ?array $metadata = null
    ) {}

    /**
     * Criar evento de criação
     */
    public static function created(string $auditableType, int $auditableId, ?array $newValues = null): self
    {
        return new self(
            eventType: 'created',
            auditableType: $auditableType,
            auditableId: $auditableId,
            userId: auth()->id(),
            userEmail: auth()->user()?->email,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
            routeName: request()->route()?->getName(),
            method: request()->method(),
            newValues: $newValues,
            description: "Criação de {$auditableType}",
            metadata: [
                'model_class' => $auditableType,
                'event_type' => 'created',
                'timestamp' => now()->toISOString()
            ]
        );
    }

    /**
     * Criar evento de atualização
     */
    public static function updated(string $auditableType, int $auditableId, ?array $oldValues = null, ?array $newValues = null, ?array $changedFields = null): self
    {
        return new self(
            eventType: 'updated',
            auditableType: $auditableType,
            auditableId: $auditableId,
            userId: auth()->id(),
            userEmail: auth()->user()?->email,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
            routeName: request()->route()?->getName(),
            method: request()->method(),
            oldValues: $oldValues,
            newValues: $newValues,
            changedFields: $changedFields,
            description: "Atualização de {$auditableType}",
            metadata: [
                'model_class' => $auditableType,
                'event_type' => 'updated',
                'timestamp' => now()->toISOString()
            ]
        );
    }

    /**
     * Criar evento de exclusão
     */
    public static function deleted(string $auditableType, int $auditableId, ?array $oldValues = null): self
    {
        return new self(
            eventType: 'deleted',
            auditableType: $auditableType,
            auditableId: $auditableId,
            userId: auth()->id(),
            userEmail: auth()->user()?->email,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
            routeName: request()->route()?->getName(),
            method: request()->method(),
            oldValues: $oldValues,
            description: "Exclusão de {$auditableType}",
            metadata: [
                'model_class' => $auditableType,
                'event_type' => 'deleted',
                'timestamp' => now()->toISOString()
            ]
        );
    }

    /**
     * Criar evento de restauração
     */
    public static function restored(string $auditableType, int $auditableId, ?array $newValues = null): self
    {
        return new self(
            eventType: 'restored',
            auditableType: $auditableType,
            auditableId: $auditableId,
            userId: auth()->id(),
            userEmail: auth()->user()?->email,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
            routeName: request()->route()?->getName(),
            method: request()->method(),
            newValues: $newValues,
            description: "Restauração de {$auditableType}",
            metadata: [
                'model_class' => $auditableType,
                'event_type' => 'restored',
                'timestamp' => now()->toISOString()
            ]
        );
    }
}
