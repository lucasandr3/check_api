<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintainable_type',
        'maintainable_id',
        'type', // 'preventive', 'corrective'
        'name',
        'description',
        'frequency_type', // 'days', 'km', 'hours'
        'frequency_value',
        'last_performed_at',
        'last_performed_km',
        'next_due_date',
        'next_due_km',
        'is_active',
        'priority', // 'low', 'medium', 'high', 'critical'
        'estimated_cost',
        'estimated_hours',
    ];

    protected $casts = [
        'last_performed_at' => 'date',
        'next_due_date' => 'date',
        'is_active' => 'boolean',
        'estimated_cost' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the maintenance schedule.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the office that owns the maintenance schedule.
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the maintainable entity (Vehicle or Equipment).
     */
    public function maintainable()
    {
        return $this->morphTo();
    }

    /**
     * Get the maintenance records for this schedule.
     */
    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class, 'schedule_id');
    }
}
