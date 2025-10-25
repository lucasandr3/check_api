<?php

namespace App\Models\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Boot do trait
     */
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAuditEvent('created');
        });

        static::updated(function ($model) {
            $model->logAuditEvent('updated');
        });

        static::deleted(function ($model) {
            $model->logAuditEvent('deleted');
        });

        static::restored(function ($model) {
            $model->logAuditEvent('restored');
        });
    }

    /**
     * Log de evento de auditoria
     */
    protected function logAuditEvent(string $eventType): void
    {
        try {
            $user = Auth::user();
            $request = Request::instance();
            
            $auditData = [
                'event_type' => $eventType,
                'auditable_type' => get_class($this),
                'auditable_id' => $this->getKey(),
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route_name' => $request->route()?->getName(),
                'method' => $request->method(),
                'description' => $this->getAuditDescription($eventType),
                'metadata' => $this->getAuditMetadata($eventType)
            ];

            // Para updates, capturar valores antigos e novos
            if ($eventType === 'updated') {
                $auditData['old_values'] = $this->getOriginal();
                $auditData['new_values'] = $this->getAttributes();
                $auditData['changed_fields'] = $this->getDirty();
            }

            // Para creates, capturar valores novos
            if ($eventType === 'created') {
                $auditData['new_values'] = $this->getAttributes();
            }

            // Criar log de auditoria de forma assíncrona
            dispatch(function () use ($auditData) {
                AuditLog::create($auditData);
            })->afterResponse();

        } catch (\Exception $e) {
            // Log do erro mas não interromper a execução
            \Log::error('Erro ao criar log de auditoria', [
                'error' => $e->getMessage(),
                'model' => get_class($this),
                'event' => $eventType
            ]);
        }
    }

    /**
     * Obter descrição da auditoria
     */
    protected function getAuditDescription(string $eventType): string
    {
        $modelName = class_basename($this);
        
        return match($eventType) {
            'created' => "Criação de {$modelName}",
            'updated' => "Atualização de {$modelName}",
            'deleted' => "Exclusão de {$modelName}",
            'restored' => "Restauração de {$modelName}",
            default => "Ação em {$modelName}"
        };
    }

    /**
     * Obter metadados da auditoria
     */
    protected function getAuditMetadata(string $eventType): array
    {
        $metadata = [
            'model_class' => get_class($this),
            'event_type' => $eventType,
            'timestamp' => now()->toISOString()
        ];

        // Adicionar metadados específicos do modelo se existirem
        if (method_exists($this, 'getAuditMetadata')) {
            $metadata = array_merge($metadata, $this->getAuditMetadata());
        }

        return $metadata;
    }

    /**
     * Relacionamento com logs de auditoria
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Obter último log de auditoria
     */
    public function getLastAuditLog()
    {
        return $this->auditLogs()->latest()->first();
    }

    /**
     * Obter logs de auditoria por tipo de evento
     */
    public function getAuditLogsByEvent(string $eventType)
    {
        return $this->auditLogs()->where('event_type', $eventType)->get();
    }
}
