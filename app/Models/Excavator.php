<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Excavator extends Model
{
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
}
