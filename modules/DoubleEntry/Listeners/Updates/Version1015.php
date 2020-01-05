<?php

namespace Modules\DoubleEntry\Listeners\Updates;

use App\Events\UpdateFinished;
use App\Listeners\Updates\Listener;
use App\Models\Module\Module;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\Type;
use Modules\DoubleEntry\Models\DEClass;

class Version1015 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '1.0.15';

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

        $this->updateTypes();
        $this->updateClasses();
        $this->updateAccounts();
    }

    protected function updateTypes()
    {
        $lang = array_flip(trans('double-entry::types'));

        $types = Type::all();

        foreach ($types as $type) {
            if (!isset($lang[$type->name])) {
                continue;
            }

            $type->name = 'double-entry::types.' . $lang[$type->name];
            $type->save();
        }
    }

    protected function updateClasses()
    {
        $lang = array_flip(trans('double-entry::classes'));

        $classes = DEClass::all();

        foreach ($classes as $class) {
            if (!isset($lang[$class->name])) {
                continue;
            }

            $class->name = 'double-entry::classes.' . $lang[$class->name];
            $class->save();
        }
    }

    protected function updateAccounts()
    {
        $current_company_id = session('company_id');

        $modules = Module::where('company_id', '<>', '0')->where('alias', 'double-entry')->get();

        foreach ($modules as $module) {
            // Set company id
            session(['company_id' => $module->company_id]);

            $lang = array_flip(trans('double-entry::accounts'));

            $accounts = Account::all();

            foreach ($accounts as $account) {
                if (!isset($lang[$account->name])) {
                    continue;
                }

                $account->name = 'double-entry::accounts.' . $lang[$account->name];
                $account->save();
            }
        }

        session(['company_id' => $current_company_id]);
    }
}
