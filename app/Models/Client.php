<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Client extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'cpf_cnpj',
        'address',
    ];

    /**
     * Get the tenant that owns the client.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the office that owns the client.
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the vehicles for the client.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
