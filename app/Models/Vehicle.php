<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Vehicle extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'client_id',
        'brand',
        'model',
        'year',
        'color',
        'plate',
        'chassis',
        'fuel_type', // 'gasoline', 'ethanol', 'diesel', 'flex'
        'engine',
        'transmission', // 'manual', 'automatic'
        'category', // 'car', 'truck', 'motorcycle', 'van'
        'current_km',
        'acquisition_date',
        'license_expiration',
        'insurance_expiration',
        'status', // 'active', 'maintenance', 'inactive'
        'observations',
    ];

    /**
     * Get the tenant that owns the vehicle.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the company that owns the vehicle.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the client that owns the vehicle.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    protected $casts = [
        'acquisition_date' => 'date',
        'license_expiration' => 'date',
        'insurance_expiration' => 'date',
    ];

    /**
     * Get the services for the vehicle.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the checklists for the vehicle.
     */
    public function checklists()
    {
        return $this->morphMany(Checklist::class, 'checklistable');
    }

    /**
     * Get the fuel records for the vehicle.
     */
    public function fuelRecords()
    {
        return $this->hasMany(FuelRecord::class);
    }

    /**
     * Get the tire records for the vehicle.
     */
    public function tireRecords()
    {
        return $this->hasMany(TireRecord::class);
    }

    /**
     * Get the maintenance records for the vehicle.
     */
    public function maintenanceRecords()
    {
        return $this->morphMany(MaintenanceRecord::class, 'maintainable');
    }

    /**
     * Get the maintenance schedules for the vehicle.
     */
    public function maintenanceSchedules()
    {
        return $this->morphMany(MaintenanceSchedule::class, 'maintainable');
    }

    /**
     * Get the latest fuel record.
     */
    public function latestFuelRecord()
    {
        return $this->hasOne(FuelRecord::class)->latestOfMany('fuel_date');
    }

    /**
     * Calculate average fuel consumption.
     */
    public function averageFuelConsumption($days = 30)
    {
        $records = $this->fuelRecords()
            ->where('fuel_date', '>=', now()->subDays($days))
            ->orderBy('fuel_date')
            ->get();

        if ($records->count() < 2) {
            return null;
        }

        $totalDistance = 0;
        $totalFuel = 0;

        for ($i = 1; $i < $records->count(); $i++) {
            $distance = $records[$i]->odometer_reading - $records[$i-1]->odometer_reading;
            if ($distance > 0) {
                $totalDistance += $distance;
                $totalFuel += $records[$i]->liters;
            }
        }

        return $totalFuel > 0 ? round($totalDistance / $totalFuel, 2) : null;
    }
}
