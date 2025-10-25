<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

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
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the vehicles for the client.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
