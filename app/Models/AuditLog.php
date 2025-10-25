<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'event_type',
        'auditable_type',
        'auditable_id',
        'user_id',
        'user_email',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'changed_fields',
        'route_name',
        'method',
        'description',
        'metadata'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'metadata' => 'array'
    ];

    /**
     * Relacionamento com o usuário que executou a ação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento polimórfico com o modelo auditado
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Scope para filtrar por tipo de evento
     */
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope para filtrar por modelo
     */
    public function scopeByModel($query, $modelClass)
    {
        return $query->where('auditable_type', $modelClass);
    }

    /**
     * Scope para filtrar por usuário
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por período
     */
    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Obter descrição legível do evento
     */
    public function getEventDescriptionAttribute(): string
    {
        $modelName = class_basename($this->auditable_type);
        
        return match($this->event_type) {
            'created' => "Criou {$modelName}",
            'updated' => "Atualizou {$modelName}",
            'deleted' => "Excluiu {$modelName}",
            'restored' => "Restaurou {$modelName}",
            default => "Ação em {$modelName}"
        };
    }

    /**
     * Verificar se o log contém mudanças
     */
    public function hasAuditChanges(): bool
    {
        return !empty($this->changed_fields) || !empty($this->old_values) || !empty($this->new_values);
    }

    /**
     * Obter campos que foram alterados
     */
    public function getChangedFieldsListAttribute(): array
    {
        if (empty($this->changed_fields)) {
            return [];
        }

        return array_keys($this->changed_fields);
    }
}
