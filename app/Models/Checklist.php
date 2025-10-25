<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Checklist extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'office_id',
        'template_id',
        'checklistable_type',
        'checklistable_id',
        'user_id',
        'type', // 'preventive', 'routine', 'corrective'
        'status',
        'items',
        'started_at',
        'completed_at',
        'observations',
    ];

    protected $casts = [
        'items' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the checklist.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the office that owns the checklist.
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the template for the checklist.
     */
    public function template()
    {
        return $this->belongsTo(ChecklistTemplate::class, 'template_id');
    }

    /**
     * Get the checklistable entity (Vehicle or Equipment).
     */
    public function checklistable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that created the checklist.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the photos for the checklist.
     */
    public function photos()
    {
        return $this->hasMany(ChecklistPhoto::class);
    }

    /**
     * Calculate completion percentage.
     */
    public function getCompletionPercentageAttribute()
    {
        if (!$this->items || !is_array($this->items)) {
            return 0;
        }

        $totalItems = count($this->items);
        if ($totalItems === 0) {
            return 0;
        }

        $completedItems = count(array_filter($this->items, function ($item) {
            return isset($item['checked']) && $item['checked'] === true;
        }));

        return round(($completedItems / $totalItems) * 100, 2);
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationAttribute()
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }
}
