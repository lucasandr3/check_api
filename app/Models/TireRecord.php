<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TireRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'vehicle_id',
        'tire_position', // 'front_left', 'front_right', 'rear_left', 'rear_right', 'spare'
        'tire_brand',
        'tire_model',
        'tire_size',
        'installation_date',
        'installation_km',
        'removal_date',
        'removal_km',
        'removal_reason', // 'wear', 'damage', 'rotation', 'replacement'
        'tread_depth_new',
        'tread_depth_removal',
        'cost',
        'warranty_km',
        'status', // 'active', 'removed', 'rotated'
        'observations',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'removal_date' => 'date',
        'tread_depth_new' => 'decimal:2',
        'tread_depth_removal' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the tire record.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the office that owns the tire record.
     */
    public function office()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the vehicle for this tire record.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Calculate tire usage in kilometers.
     */
    public function getUsageKmAttribute()
    {
        if (!$this->removal_km || !$this->installation_km) {
            return null;
        }

        return $this->removal_km - $this->installation_km;
    }

    /**
     * Calculate tire wear percentage.
     */
    public function getWearPercentageAttribute()
    {
        if (!$this->tread_depth_new || !$this->tread_depth_removal) {
            return null;
        }

        $wearAmount = $this->tread_depth_new - $this->tread_depth_removal;
        return round(($wearAmount / $this->tread_depth_new) * 100, 2);
    }

    /**
     * Check if tire needs replacement based on tread depth.
     */
    public function needsReplacement($minimumTread = 1.6)
    {
        if (!$this->tread_depth_removal) {
            return false;
        }

        return $this->tread_depth_removal <= $minimumTread;
    }
}
