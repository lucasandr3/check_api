<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class FuelRecord extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'vehicle_id',
        'fuel_type', // 'gasoline', 'ethanol', 'diesel', 'flex'
        'liters',
        'price_per_liter',
        'total_cost',
        'odometer_reading',
        'fuel_station',
        'driver_name',
        'fuel_date',
        'observations',
        'receipt_photo',
    ];

    protected $casts = [
        'fuel_date' => 'datetime',
        'liters' => 'decimal:3',
        'price_per_liter' => 'decimal:3',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the fuel record.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the office that owns the fuel record.
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the vehicle for this fuel record.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Calculate fuel efficiency (km/l).
     */
    public function calculateEfficiency()
    {
        $previousRecord = static::where('vehicle_id', $this->vehicle_id)
            ->where('fuel_date', '<', $this->fuel_date)
            ->orderBy('fuel_date', 'desc')
            ->first();

        if (!$previousRecord) {
            return null;
        }

        $distanceTraveled = $this->odometer_reading - $previousRecord->odometer_reading;
        
        if ($distanceTraveled <= 0 || $this->liters <= 0) {
            return null;
        }

        return round($distanceTraveled / $this->liters, 2);
    }
}
