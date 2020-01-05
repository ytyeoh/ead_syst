<?php

namespace Modules\DoubleEntry\Providers;

use App\Models\Banking\Account;
use App\Models\Banking\Transfer;
use App\Models\Expense\Bill;
use App\Models\Expense\BillItem;
use App\Models\Expense\BillItemTax;
use App\Models\Expense\BillPayment;
use App\Models\Expense\Payment;
use App\Models\Income\Invoice;
use App\Models\Income\InvoiceItem;
use App\Models\Income\InvoiceItemTax;
use App\Models\Income\InvoicePayment;
use App\Models\Income\Revenue;
use App\Models\Setting\Tax;
use Illuminate\Support\ServiceProvider;
use Modules\DoubleEntry\Models\Ledger;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        Account::observe('Modules\DoubleEntry\Observers\Banking\Account');
        Transfer::observe('Modules\DoubleEntry\Observers\Banking\Transfer');
        Bill::observe('Modules\DoubleEntry\Observers\Expense\Bill');
        BillItem::observe('Modules\DoubleEntry\Observers\Expense\BillItem');
        BillItemTax::observe('Modules\DoubleEntry\Observers\Expense\BillItemTax');
        BillPayment::observe('Modules\DoubleEntry\Observers\Expense\BillPayment');
        Payment::observe('Modules\DoubleEntry\Observers\Expense\Payment');
        Invoice::observe('Modules\DoubleEntry\Observers\Income\Invoice');
        InvoiceItem::observe('Modules\DoubleEntry\Observers\Income\InvoiceItem');
        InvoiceItemTax::observe('Modules\DoubleEntry\Observers\Income\InvoiceItemTax');
        InvoicePayment::observe('Modules\DoubleEntry\Observers\Income\InvoicePayment');
        Revenue::observe('Modules\DoubleEntry\Observers\Income\Revenue');
        Ledger::observe('Modules\DoubleEntry\Observers\DoubleEntry\JournalLedger');
        Tax::observe('Modules\DoubleEntry\Observers\Setting\Tax');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}