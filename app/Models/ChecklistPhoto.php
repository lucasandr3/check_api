<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ChecklistPhoto extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'checklist_id',
        'filename',
        'path',
        'mime_type',
        'size',
        'description',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Get the tenant that owns the photo.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the checklist that owns the photo.
     */
    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }
}
