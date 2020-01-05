<?php

namespace Modules\DoubleEntry\Observers\Expense;

use App\Models\Expense\BillPayment as Model;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Models\Ledger;
use Date;

class BillPayment
{
    /**
     * Listen to the created event.
     *
     * @param  Model  $payment
     * @return void
     */
    public function created(Model $payment)
    {
        $bill = $payment->bill;

        $bill_ledger = Ledger::record($bill->id, get_class($bill))->first();

        if (empty($bill_ledger)) {
            return;
        }

        $account_id = AccountBank::where('bank_id', $payment->account_id)->pluck('account_id')->first();

        if (empty($account_id)) {
            return;
        }

        Ledger::create([
            'company_id' => session('company_id'),
            'account_id' => $account_id,
            'ledgerable_id' => $payment->id,
            'ledgerable_type' => get_class($payment),
            'issued_at' => $payment->paid_at,
            'entry_type' => 'total',
            'credit' => $payment->amount,
        ]);

        $remainder = $bill->amount - $payment->amount;
        if ($remainder == 0) {
            $bill_ledger->delete();
        } else {
            $bill_ledger->update([
                'company_id' => session('company_id'),
                'account_id' => Account::code(setting('double-entry.accounts_payable', 200))->pluck('id')->first(),
                'ledgerable_id' => $bill->id,
                'ledgerable_type' => get_class($bill),
                'issued_at' => $bill->billed_at,
                'entry_type' => 'total',
                'credit' => $remainder,
            ]);
        }
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $payment
     * @return void
     */
    public function deleted(Model $payment)
    {
        Ledger::record($payment->id, get_class($payment))->delete();

        $bill = $payment->bill;

        $bill_ledger = Ledger::record($bill->id, get_class($bill))->first();

        if (empty($bill_ledger)) {
            return;
        }

        $bill_ledger->update([
            'company_id' => session('company_id'),
            'account_id' => Account::code(setting('double-entry.accounts_payable', 200))->pluck('id')->first(),
            'ledgerable_id' => $bill->id,
            'ledgerable_type' => get_class($bill),
            'issued_at' => $bill->billed_at,
            'entry_type' => 'total',
            'credit' => $bill_ledger->credit + $payment->amount,
        ]);
    }
}