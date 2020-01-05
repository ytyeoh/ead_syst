<?php

namespace Modules\DoubleEntry\Listeners\Updates;

use App\Events\UpdateFinished;
use App\Listeners\Updates\Listener;
use Artisan;

class Version111 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '1.1.1';

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(UpdateFinished $event)
    {
        // Check if should listen
        if (!$this->check($event)) {
            return;
        }

        // Update database
        Artisan::call('migrate', ['--force' => true]);
    }
}
