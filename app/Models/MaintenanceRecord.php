<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintainable_type',
        'maintainable_id',
        'type', // 'preventive', 'corrective', 'routine'
        'description',
        'parts_used',
        'labor_hours',
        'total_cost',
        'performed_by',
        'performed_at',
        'next_maintenance_date',
        'next_maintenance_km',
        'status',
        'observations',
    ];

    protected $casts = [
        'parts_used' => 'array',
        'performed_at' => 'datetime',
        'next_maintenance_date' => 'date',
        'labor_hours' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the maintenance record.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the office that owns the maintenance record.
     */
    public function office()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the maintainable entity (Vehicle or Equipment).
     */
    public function maintainable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the maintenance.
     */
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
