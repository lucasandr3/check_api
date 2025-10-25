<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'domain',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Relacionamento com tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Encontrar tenant por domínio
     */
    public static function findTenantByDomain(string $domain): ?Tenant
    {
        $tenantDomain = self::where('domain', $domain)->first();
        
        return $tenantDomain ? $tenantDomain->tenant : null;
    }

    /**
     * Encontrar tenant por subdomínio
     */
    public static function findTenantBySubdomain(string $host): ?Tenant
    {
        // Extrair subdomínio (ex: 1000.check-api.com -> 1000)
        if (preg_match('/^(\d+)\./', $host, $matches)) {
            return Tenant::find($matches[1]);
        }
        
        return null;
    }

    /**
     * Scope para domínios primários
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
