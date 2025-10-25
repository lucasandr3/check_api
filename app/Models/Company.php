<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'tenant_id',
        'name',
        'address',
        'phone',
        'email',
        'cnpj',
    ];

    /**
     * Get the tenant that owns the company.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the users for the company.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    /**
     * Get the clients for the company.
     */
    public function clients()
    {
        return $this->hasMany(Client::class, 'company_id');
    }

    /**
     * Get the vehicles for the company.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'company_id');
    }

    /**
     * Get the equipment for the company.
     */
    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'company_id');
    }

    /**
     * Get the checklist templates for the company.
     */
    public function checklistTemplates()
    {
        return $this->hasMany(ChecklistTemplate::class, 'company_id');
    }

    /**
     * Get the maintenance schedules for the company.
     */
    public function maintenanceSchedules()
    {
        return $this->hasMany(MaintenanceSchedule::class, 'company_id');
    }

    /**
     * Get the maintenance records for the company.
     */
    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class, 'company_id');
    }

    /**
     * Get the fuel records for the company.
     */
    public function fuelRecords()
    {
        return $this->hasMany(FuelRecord::class, 'company_id');
    }

    /**
     * Get the tire records for the company.
     */
    public function tireRecords()
    {
        return $this->hasMany(TireRecord::class, 'company_id');
    }

    /**
     * Get the services for the company.
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'company_id');
    }

    /**
     * Get the checklists for the company.
     */
    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'company_id');
    }

    /**
     * Scope to filter by company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
