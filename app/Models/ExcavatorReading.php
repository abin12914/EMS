<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Events\DeletingExcavatorReadingEvent;

class ExcavatorReading extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['reading_date', 'deleted_at'];

    public $timestamps = false;

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'deleting' => DeletingExcavatorReadingEvent::class,
    ];
    
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
     * Get the user record who created the record.
     */
    public function creator()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get the user record who last updated the record.
     */
    public function updater()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get the user record who deleted the record.
     */
    public function deleter()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get the excavator details related to the excavator reading
     */
    public function excavator()
    {
        return $this->belongsTo('App\Models\Excavator');
    }

    /**
     * Get the site details related to the excavator reading
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site');
    }

    /**
     * Get the operator details related to the excavator reading
     */
    public function operator()
    {
        return $this->belongsTo('App\Models\Employee');
    }

    /**
     * Get the transaction details related to the excavator reading
     */
    public function transaction()
    {
        return $this->belongsTo('App\Models\Transaction');
    }
}
