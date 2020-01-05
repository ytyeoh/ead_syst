<?php

namespace Modules\DoubleEntry\Observers\Income;

use App\Models\Income\InvoicePayment as Model;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Models\Ledger;

class InvoicePayment
{
    /**
     * Listen to the created event.
     *
     * @param  Model  $payment
     * @return void
     */
    public function created(Model $payment)
    {
        $invoice = $payment->invoice;

        $invoice_ledger = Ledger::record($invoice->id, get_class($invoice))->first();

        if (empty($invoice_ledger)) {
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
            'debit' => $payment->amount,
        ]);

        $remainder = $invoice->amount - $payment->amount;
        if ($remainder == 0) {
            $invoice_ledger->delete();
        } else {
            $invoice_ledger->update([
                'company_id' => session('company_id'),
                'account_id' => Account::code(setting('double-entry.accounts_receivable', 120))->pluck('id')->first(),
                'ledgerable_id' => $invoice->id,
                'ledgerable_type' => get_class($invoice),
                'issued_at' => $invoice->invoiced_at,
                'entry_type' => 'total',
                'debit' => $remainder,
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

        $invoice = $payment->invoice;

        $invoice_ledger = Ledger::record($invoice->id, get_class($invoice))->first();

        if (empty($invoice_ledger)) {
            return;
        }

        $invoice_ledger->update([
            'company_id' => session('company_id'),
            'account_id' => Account::code(setting('double-entry.accounts_receivable', 120))->pluck('id')->first(),
            'ledgerable_id' => $invoice->id,
            'ledgerable_type' => get_class($invoice),
            'issued_at' => $invoice->invoiced_at,
            'entry_type' => 'total',
            'debit' => $invoice_ledger->debit + $payment->amount,
        ]);
    }
}