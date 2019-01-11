<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes;

    /**
     * Scope a query to only include active employees.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    /**
     * Get the user record who created the account.
     */
    public function creator()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get the user record who last updated the account.
     */
    public function updater()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get the user record who deleted the account.
     */
    public function deleter()
    {
        return $this->belongsTo('App\Models\User');
    }
}
