<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'type',
        'description',
        'estimated_cost',
        'final_cost',
        'start_date',
        'end_date',
        'observations',
        'status',
        'user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'final_cost' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the service.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the office that owns the service.
     */
    public function office()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the vehicle for the service.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user assigned to the service.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the checklists for the service.
     */
    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    /**
     * Get the quotes for the service.
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Get the client through the vehicle.
     */
    public function client()
    {
        return $this->hasOneThrough(Client::class, Vehicle::class);
    }
}
