<?php

namespace Modules\DoubleEntry\Listeners\Updates;

use App\Events\UpdateFinished;
use App\Listeners\Updates\Listener;
use App\Models\Module\Module;

class Version1024 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '1.0.24';

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

        $company_id = session('company_id');

        $modules = Module::where('company_id', '<>', '0')->where('alias', 'double-entry')->get();

        foreach ($modules as $module) {
            session(['company_id' => $module->company_id]);

            setting()->forgetAll();
            setting()->setExtraColumns(['company_id' => $module->company_id]);
            setting()->load(true);

            setting()->set('double-entry.accounts_receivable', 120);
            setting()->set('double-entry.accounts_payable', 200);
            setting()->set('double-entry.accounts_sales', 400);
            setting()->set('double-entry.accounts_expenses', 628);

            setting()->save();
        }

        session(['company_id' => $company_id]);

        setting()->forgetAll();
        setting()->setExtraColumns(['company_id' => $company_id]);
        setting()->load(true);
    }
}
