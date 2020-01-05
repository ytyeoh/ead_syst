<?php

namespace Modules\DoubleEntry\Observers\Income;

use App\Models\Income\Invoice as Model;
use App\Models\Income\InvoiceItem;
use App\Models\Income\InvoicePayment;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\Ledger;

class Invoice
{
    /**
     * Listen to the created event.
     *
     * @param  Model  $invoice
     * @return void
     */
    public function created(Model $invoice)
    {
        Ledger::create([
            'company_id' => session('company_id'),
            'account_id' => Account::code(setting('double-entry.accounts_receivable', 120))->pluck('id')->first(),
            'ledgerable_id' => $invoice->id,
            'ledgerable_type' => get_class($invoice),
            'issued_at' => $invoice->invoiced_at,
            'entry_type' => 'total',
            'debit' => $invoice->amount,
        ]);
    }

    /**
     * Listen to the created event.
     *
     * @param  Model  $invoice
     * @return void
     */
    public function updated(Model $invoice)
    {
        $ledger = Ledger::record($invoice->id, get_class($invoice))->first();

        if (empty($ledger)) {
            return;
        }

        $amount = $invoice->amount;

        if ($invoice->payments->count()) {
            $paid = 0;

            foreach ($invoice->payments as $payment) {
                $paid += $payment->amount;
            }

            $amount = $amount - $paid;
        }

        $ledger->update([
            'company_id' => session('company_id'),
            'account_id' => Account::code(setting('double-entry.accounts_receivable', 120))->pluck('id')->first(),
            'ledgerable_id' => $invoice->id,
            'ledgerable_type' => get_class($invoice),
            'issued_at' => $invoice->invoiced_at,
            'entry_type' => 'total',
            'debit' => $amount,
        ]);
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $invoice
     * @return void
     */
    public function deleted(Model $invoice)
    {
        Ledger::record($invoice->id, get_class($invoice))->delete();
    }
}