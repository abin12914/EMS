<?php

namespace App\Listeners;

use App\Events\DeletingExcavatorReadingEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeletingExcavatorReadingEventListener
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
     * @param  DeletingExcavatorReadingEvent  $event
     * @return void
     */
    public function handle(DeletingExcavatorReadingEvent $event)
    {
        $transaction = $event->excavatorReading->transaction()->firstOrFail();

        $event->excavatorReading->isForceDeleting() ? $transaction->forceDelete() : $transaction->delete();
    }
}
