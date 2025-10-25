<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Equipment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'type',
        'brand',
        'model',
        'serial_number',
        'acquisition_date',
        'warranty_expiration',
        'status',
        'location',
        'client_id',
        'observations',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'warranty_expiration' => 'date',
    ];

    /**
     * Get the tenant that owns the equipment.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the office that owns the equipment.
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the client that owns the equipment.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the checklists for the equipment.
     */
    public function checklists()
    {
        return $this->morphMany(Checklist::class, 'checklistable');
    }

    /**
     * Get the maintenance records for the equipment.
     */
    public function maintenanceRecords()
    {
        return $this->morphMany(MaintenanceRecord::class, 'maintainable');
    }

    /**
     * Get the maintenance schedules for the equipment.
     */
    public function maintenanceSchedules()
    {
        return $this->morphMany(MaintenanceSchedule::class, 'maintainable');
    }
}
