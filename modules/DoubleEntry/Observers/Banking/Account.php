<?php

namespace Modules\DoubleEntry\Observers\Banking;

use App\Models\Banking\Account as Model;
use Modules\DoubleEntry\Models\Account as Chart;
use Modules\DoubleEntry\Models\AccountBank;

class Account
{
    /**
     * Listen to the created event.
     *
     * @param  Model  $account
     * @return void
     */
    public function created(Model $account)
    {
        if ($account->bank_name == 'chart-of-accounts') {
            $account->bank_name = '';
            $account->save();
            return;
        }

        $chart = Chart::create([
            'company_id' => session('company_id'),
            'type_id' => setting('double-entry.types_bank', 6),
            'code' => Chart::max('code') + 1,
            'name' => $account->name,
            'enabled' => $account->enabled,
        ]);

        AccountBank::create([
            'company_id' => session('company_id'),
            'account_id' => $chart->id,
            'bank_id' => $account->id,
        ]);
    }

    /**
     * Listen to the created event.
     *
     * @param  Model  $account
     * @return void
     */
    public function updated(Model $account)
    {
        $rel = AccountBank::where('bank_id', $account->id)->first();

        if (!$rel) {
            return;
        }

        $chart = $rel->account;

        $chart->update([
            'name' => $account->name,
            'code' => $chart->code,
            'type_id' => $chart->type_id,
            'enabled' => $account->enabled,
        ]);
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $account
     * @return void
     */
    public function deleted(Model $account)
    {
        $rel = AccountBank::where('bank_id', $account->id)->first();

        if (!$rel) {
            return;
        }

        $rel->account->delete();
    }
}