<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Office extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'cnpj',
    ];

    /**
     * Get the tenant that owns the office.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the users for the office.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the clients for the office.
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get the vehicles for the office.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get the services for the office.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the checklists for the office.
     */
    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    /**
     * Get the quotes for the office.
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }
}
