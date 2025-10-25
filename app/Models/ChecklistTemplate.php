<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // 'preventive', 'routine', 'corrective'
        'category', // 'vehicle', 'equipment'
        'items',
        'is_active',
        'description',
    ];

    protected $casts = [
        'items' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the checklist template.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the office that owns the checklist template.
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the checklists that use this template.
     */
    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'template_id');
    }
}
