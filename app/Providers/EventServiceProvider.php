<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\DeletingEmployeeWageEvent' => [
            'App\Listeners\DeletingEmployeeWageEventListener',
        ],
        'App\Events\DeletingExcavatorReadingEvent' => [
            'App\Listeners\DeletingExcavatorReadingEventListener',
        ],
        'App\Events\DeletingExcavatorRentEvent' => [
            'App\Listeners\DeletingExcavatorRentEventListener',
        ],
        'App\Events\DeletingExpenseEvent' => [
            'App\Listeners\DeletingExpenseEventListener',
        ],
        'App\Events\DeletingVoucherEvent' => [
            'App\Listeners\DeletingVoucherEventListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
