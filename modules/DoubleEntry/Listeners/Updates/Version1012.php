<?php

namespace Modules\DoubleEntry\Listeners\Updates;

use App\Events\UpdateFinished;
use App\Listeners\Updates\Listener;
use App\Models\Setting\Tax;
use Artisan;
use Modules\DoubleEntry\Models\Account as ChartAccount;
use Modules\DoubleEntry\Models\AccountTax;
use Modules\DoubleEntry\Models\Type;

class Version1012 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '1.0.12';

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

        $this->createTypes();

        $this->copyTaxes();
    }

    protected function createTypes()
    {
        Type::create([
            'name'     => 'Tax',
            'class_id' => '2',
        ]);
    }

    protected function copyTaxes()
    {
        Tax::all()->each(function ($tax) {
            $chart = ChartAccount::create([
                'company_id' => session('company_id'),
                'type_id' => 17,
                'code' => ChartAccount::max('code') + 1,
                'name' => $tax->name,
                'system' => 1,
                'enabled' => 1,
            ]);

            AccountTax::create([
                'company_id' => session('company_id'),
                'account_id' => $chart->id,
                'tax_id' => $tax->id,
            ]);
        });
    }
}
