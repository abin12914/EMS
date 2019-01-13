<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
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

    /**
     * Get the account details related to the employee
     */
    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }

    /**
     * Get the employee wages for the employee.
     */
    public function employeeWages()
    {
        return $this->hasMany('App\Models\EmployeeWage');
    }
}
