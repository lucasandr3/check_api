<?php

namespace App\Traits;

use App\Models\Company;

trait BelongsToCompany
{
    /**
     * Get the company that owns the model.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Scope to filter by company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to filter by current company (if set in app context)
     */
    public function scopeForCurrentCompany($query)
    {
        if (app()->has('current_company_id')) {
            return $query->where('company_id', app('current_company_id'));
        }
        
        return $query;
    }
}
