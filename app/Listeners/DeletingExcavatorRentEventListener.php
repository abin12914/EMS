<?php

namespace App\Listeners;

use App\Events\DeletingExcavatorRentEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeletingExcavatorRentEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  DeletingExcavatorRentEvent  $event
     * @return void
     */
    public function handle(DeletingExcavatorRentEvent $event)
    {
        $transaction = $event->excavatorRent->transaction()->firstOrFail();

        $event->excavatorRent->isForceDeleting() ? $transaction->forceDelete() : $transaction->delete();
    }
}
