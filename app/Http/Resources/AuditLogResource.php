<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_type' => $this->event_type,
            'event_description' => $this->event_description,
            'auditable_type' => $this->auditable_type,
            'auditable_id' => $this->auditable_id,
            'user_id' => $this->user_id,
            'user_email' => $this->user_email,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'route_name' => $this->route_name,
            'method' => $this->method,
            'description' => $this->description,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'changed_fields' => $this->changed_fields,
            'changed_fields_list' => $this->changed_fields_list,
            'has_changes' => $this->hasAuditChanges(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relacionamentos
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ];
            }),
            
            // Campos calculados
            'model_name' => class_basename($this->auditable_type),
            'formatted_created_at' => $this->created_at?->format('d/m/Y H:i:s'),
            'time_ago' => $this->created_at?->diffForHumans(),
            
            // Informações de segurança
            'is_sensitive' => $this->isSensitiveEvent(),
            'security_level' => $this->getSecurityLevel()
        ];
    }

    /**
     * Verificar se é um evento sensível
     */
    private function isSensitiveEvent(): bool
    {
        $sensitiveEvents = [
            'login', 'logout', 'login_failed',
            'deleted', 'force_deleted'
        ];

        return in_array($this->event_type, $sensitiveEvents);
    }

    /**
     * Obter nível de segurança do evento
     */
    private function getSecurityLevel(): string
    {
        return match($this->event_type) {
            'login_failed' => 'high',
            'deleted', 'force_deleted' => 'medium',
            'login', 'logout' => 'medium',
            'created', 'updated', 'restored' => 'low',
            default => 'low'
        };
    }
}
