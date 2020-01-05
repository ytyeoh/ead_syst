<?php

namespace Modules\DoubleEntry\Observers\Expense;

use App\Models\Expense\Bill as Model;
use App\Models\Expense\BillItem;
use App\Models\Expense\BillPayment;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\Ledger;

class Bill
{
    /**
     * Listen to the created event.
     *
     * @param  Model  $bill
     * @return void
     */
    public function created(Model $bill)
    {
        Ledger::create([
            'company_id' => session('company_id'),
            'account_id' => Account::code(setting('double-entry.accounts_payable', 200))->pluck('id')->first(),
            'ledgerable_id' => $bill->id,
            'ledgerable_type' => get_class($bill),
            'issued_at' => $bill->billed_at,
            'entry_type' => 'total',
            'credit' => $bill->amount,
        ]);
    }

    /**
     * Listen to the created event.
     *
     * @param  Model  $bill
     * @return void
     */
    public function updated(Model $bill)
    {
        $ledger = Ledger::record($bill->id, get_class($bill))->first();

        if (empty($ledger)) {
            return;
        }

        $amount = $bill->amount;

        if ($bill->payments->count()) {
            $paid = 0;

            foreach ($bill->payments as $payment) {
                $paid += $payment->amount;
            }

            $amount = $amount - $paid;
        }

        $ledger->update([
            'company_id' => session('company_id'),
            'account_id' => Account::code(setting('double-entry.accounts_payable', 200))->pluck('id')->first(),
            'ledgerable_id' => $bill->id,
            'ledgerable_type' => get_class($bill),
            'issued_at' => $bill->billed_at,
            'entry_type' => 'total',
            'credit' => $amount,
        ]);
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $bill
     * @return void
     */
    public function deleted(Model $bill)
    {
        Ledger::record($bill->id, get_class($bill))->delete();
    }
}