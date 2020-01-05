<?php

namespace Modules\DoubleEntry\Observers\Expense;

use App\Models\Expense\Payment as Model;
use App\Models\Setting\Category;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Models\Ledger;

class Payment
{
    /**
     * Listen to the created event.
     *
     * @param  Model  $payment
     * @return void
     */
    public function created(Model $payment)
    {
        if ($this->isJournal($payment) || $this->isTransfer($payment)) {
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

        Ledger::create([
            'company_id' => session('company_id'),
            'account_id' => Account::code(setting('double-entry.accounts_expenses', 628))->pluck('id')->first(),
            'ledgerable_id' => $payment->id,
            'ledgerable_type' => get_class($payment),
            'issued_at' => $payment->paid_at,
            'entry_type' => 'item',
            'debit' => $payment->amount,
        ]);
    }

    /**
     * Listen to the created event.
     *
     * @param  Model  $payment
     * @return void
     */
    public function updated(Model $payment)
    {
        if ($this->isJournal($payment) || $this->isTransfer($payment)) {
            return;
        }

        $ledger = Ledger::record($payment->id, get_class($payment))->where('entry_type', 'total')->first();

        if (empty($ledger)) {
            return;
        }

        $account_id = AccountBank::where('bank_id', $payment->account_id)->pluck('account_id')->first();

        if (empty($account_id)) {
            return;
        }

        $ledger->update([
            'company_id' => session('company_id'),
            'account_id' => $account_id,
            'ledgerable_id' => $payment->id,
            'ledgerable_type' => get_class($payment),
            'issued_at' => $payment->paid_at,
            'entry_type' => 'total',
            'credit' => $payment->amount,
        ]);

        $ledger = Ledger::record($payment->id, get_class($payment))->where('entry_type', 'item')->first();

        if (empty($ledger)) {
            return;
        }

        $ledger->update([
            'company_id' => session('company_id'),
            'account_id' => Account::code(setting('double-entry.accounts_expenses', 628))->pluck('id')->first(),
            'ledgerable_id' => $payment->id,
            'ledgerable_type' => get_class($payment),
            'issued_at' => $payment->paid_at,
            'entry_type' => 'item',
            'debit' => $payment->amount,
        ]);
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $payment
     * @return void
     */
    public function deleted(Model $payment)
    {
        if ($this->isJournal($payment) || $this->isTransfer($payment)) {
            return;
        }

        Ledger::record($payment->id, get_class($payment))->delete();
    }

    protected function isJournal($payment)
    {
        if (empty($payment->reference)) {
            return false;
        }

        if (!str_contains($payment->reference, 'journal-entry-ledger:')) {
            return false;
        }

        return true;
    }

    protected function isTransfer($payment)
    {
        if ($payment->category_id != Category::transfer()) {
            return false;
        }

        return true;
    }
}
