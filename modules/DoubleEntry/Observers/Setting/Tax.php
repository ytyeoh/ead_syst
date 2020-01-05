<?php

namespace Modules\DoubleEntry\Observers\Setting;

use App\Models\Setting\Tax as Model;
use Modules\DoubleEntry\Models\Account as Chart;
use Modules\DoubleEntry\Models\AccountTax;

class Tax
{
    /**
     * Listen to the created event.
     *
     * @param  Model  $tax
     * @return void
     */
    public function created(Model $tax)
    {
        $chart = Chart::create([
            'company_id' => session('company_id'),
            'type_id' => setting('double-entry.types_tax', 17),
            'code' => Chart::max('code') + 1,
            'name' => $tax->name,
            'enabled' => $tax->enabled,
        ]);

        AccountTax::create([
            'company_id' => session('company_id'),
            'account_id' => $chart->id,
            'tax_id' => $tax->id,
        ]);
    }

    /**
     * Listen to the created event.
     *
     * @param  Model  $tax
     * @return void
     */
    public function updated(Model $tax)
    {
        $rel = AccountTax::where('tax_id', $tax->id)->first();

        if (!$rel) {
            return;
        }

        $chart = $rel->account;

        $chart->update([
            'name' => $tax->name,
            'code' => $chart->code,
            'type_id' => $chart->type_id,
            'enabled' => $tax->enabled,
        ]);
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $tax
     * @return void
     */
    public function deleted(Model $tax)
    {
        $rel = AccountTax::where('tax_id', $tax->id)->first();

        if (!$rel) {
            return;
        }

        $rel->account->delete();
    }
}