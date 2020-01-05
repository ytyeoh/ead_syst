<?php

namespace Modules\DoubleEntry\Listeners\Updates;

use App\Events\UpdateFinished;
use App\Listeners\Updates\Listener;
use File;

class Version110 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '1.1.0';

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

        File::copy(base_path('modules/DoubleEntry/Resources/assets/chart-of-accounts.xlsx'), base_path('public/files/import/chart-of-accounts.xlsx'));
    }
}
