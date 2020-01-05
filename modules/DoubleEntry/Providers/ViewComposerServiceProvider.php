<?php

namespace Modules\DoubleEntry\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        // Invoice
        View::composer(['incomes.invoices.create', 'incomes.invoices.edit'], 'Modules\DoubleEntry\Http\ViewComposers\InvoiceTable');
        View::composer(['incomes.invoices.create', 'incomes.invoices.edit'], 'Modules\DoubleEntry\Http\ViewComposers\InvoiceInput');
        View::composer(['incomes.invoices.item'], 'Modules\DoubleEntry\Http\ViewComposers\InvoiceInput');

        // Bill
        View::composer(['expenses.bills.create', 'expenses.bills.edit'], 'Modules\DoubleEntry\Http\ViewComposers\BillTable');
        View::composer(['expenses.bills.create', 'expenses.bills.edit'], 'Modules\DoubleEntry\Http\ViewComposers\BillInput');
        View::composer(['expenses.bills.item'], 'Modules\DoubleEntry\Http\ViewComposers\BillInput');
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
