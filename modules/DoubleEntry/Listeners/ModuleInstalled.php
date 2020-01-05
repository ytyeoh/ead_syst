<?php

namespace Modules\DoubleEntry\Listeners;

use App\Events\ModuleInstalled as Event;
use App\Models\Auth\Role;
use App\Models\Auth\Permission;
use App\Models\Banking\Account;
use App\Models\Banking\Transfer;
use App\Models\Income\Invoice;
use App\Models\Income\Revenue;
use App\Models\Expense\Bill;
use App\Models\Expense\Payment;
use App\Models\Setting\Tax;
use Artisan;
use File;
use Modules\DoubleEntry\Models\Account as ChartAccount;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Models\AccountTax;
use Modules\DoubleEntry\Models\Journal;
use Modules\DoubleEntry\Models\Ledger;

class ModuleInstalled
{
    public $company_id;
    
    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        if ($event->alias != 'double-entry') {
            return;
        }

        $this->company_id = $event->company_id;

        // Create seeds
        Artisan::call('double-entry:seed', [
            'company' => $event->company_id
        ]);

        // Update permissions
        $this->updatePermissions();

        // Save settings
        $this->saveSettings();

        // Copy current data
        $this->copyData();

        File::copy(base_path('modules/DoubleEntry/Resources/assets/chart-of-accounts.xlsx'), base_path('public/files/import/chart-of-accounts.xlsx'));
    }

    protected function updatePermissions()
    {
        // Check if already exists
        if ($p = Permission::where('name', 'read-double-entry-balance-sheet')->pluck('id')->first()) {
            return;
        }

        $permissions = [];

        $permissions[] = Permission::firstOrCreate([
            'name' => 'read-double-entry-balance-sheet',
            'display_name' => 'Read Double-Entry Balance Sheet',
            'description' => 'Read Double-Entry Balance Sheet',
        ]);

        $permissions[] = Permission::firstOrCreate([
            'name' => 'create-double-entry-chart-of-accounts',
            'display_name' => 'Create Double-Entry Chart of Accounts',
            'description' => 'Create Double-Entry Chart of Accounts',
        ]);

        $permissions[] = Permission::firstOrCreate([
            'name' => 'read-double-entry-chart-of-accounts',
            'display_name' => 'Read Double-Entry Chart of Accounts',
            'description' => 'Read Double-Entry Chart of Accounts',
        ]);

        $permissions[] = Permission::firstOrCreate([
            'name' => 'update-double-entry-chart-of-accounts',
            'display_name' => 'Update Double-Entry Chart of Accounts',
            'description' => 'Update Double-Entry Chart of Accounts',
        ]);

        $permissions[] = Permission::firstOrCreate([
            'name' => 'delete-double-entry-chart-of-accounts',
            'display_name' => 'Delete Double-Entry Chart of Accounts',
            'description' => 'Delete Double-Entry Chart of Accounts',
        ]);

        $permissions[] = Permission::firstOrCreate([
            'name' => 'read-double-entry-general-ledger',
            'display_name' => 'Read Double-Entry General Ledger',
            'description' => 'Read Double-Entry General Ledger',
        ]);

        $permissions[] = Permission::firstOrCreate([
            'name' => 'create-double-entry-journal-entry',
            'display_name' => 'Create Double-Entry Journal Entry',
            'description' => 'Create Double-Entry Journal Entry',
        ]);

        $permissions[] = Permission::firstOrCreate([
            'name' => 'read-double-entry-journal-entry',
            'display_name' => 'Read Double-Entry Journal Entry',
            'description' => 'Read Double-Entry Journal Entry',
        ]);

        $permissions[] = Permission::firstOrCreate([
            'name' => 'update-double-entry-journal-entry',
            'display_name' => 'Update Double-Entry Journal Entry',
            'description' => 'Update Double-Entry Journal Entry',
        ]);

        $permissions[] = Permission::firstOrCreate([
            'name' => 'delete-double-entry-journal-entry',
            'display_name' => 'Delete Double-Entry Journal Entry',
            'description' => 'Delete Double-Entry Journal Entry',
        ]);

        $permissions[] = Permission::firstOrCreate([
            'name' => 'read-double-entry-trial-balance',
            'display_name' => 'Read Double-Entry Trial Balance',
            'description' => 'Read Double-Entry Trial Balance',
        ]);

        // Attach permission to roles
        $roles = Role::all();

        foreach ($roles as $role) {
            $allowed = ['admin', 'manager'];

            if (!in_array($role->name, $allowed)) {
                continue;
            }

            foreach ($permissions as $permission) {
                $role->attachPermission($permission);
            }
        }
    }

    protected function saveSettings()
    {
        setting()->forgetAll();
        setting()->setExtraColumns(['company_id' => session('company_id')]);
        setting()->load(true);

        setting()->set('double-entry.accounts_receivable', 120);
        setting()->set('double-entry.accounts_payable', 200);
        setting()->set('double-entry.accounts_sales', 400);
        setting()->set('double-entry.accounts_expenses', 628);
        setting()->set('double-entry.types_bank', 6);
        setting()->set('double-entry.types_tax', 17);
        setting()->save();

        setting()->forgetAll();
    }

    protected function copyData()
    {
        $this->copyAccounts();
        $this->copyTransfers();
        $this->copyTaxes();
        $this->copyInvoices();
        $this->copyRevenues();
        $this->copyBills();
        $this->copyPayments();
    }

    protected function copyAccounts()
    {
        Account::all()->each(function ($bank) {
            $chart = ChartAccount::firstOrCreate([
                'company_id' => $this->company_id,
                'type_id' => setting('double-entry.types_bank', 6),
                'code' => ChartAccount::max('code') + 1,
                'name' => $bank->name,
                'enabled' => 1,
            ]);

            AccountBank::firstOrCreate([
                'company_id' => $this->company_id,
                'account_id' => $chart->id,
                'bank_id' => $bank->id,
            ]);
        });
    }

    protected function copyTransfers()
    {
        Transfer::all()->each(function ($transfer) {
            $payment = $transfer->payment;
            $revenue = $transfer->revenue;

            $payment_account_id = AccountBank::where('bank_id', $payment->account_id)->pluck('account_id')->first();
            $revenue_account_id = AccountBank::where('bank_id', $revenue->account_id)->pluck('account_id')->first();

            if (empty($payment_account_id) || empty($revenue_account_id)) {
                return;
            }

            $journal = Journal::firstOrCreate([
                'company_id' => $transfer->company_id,
                'amount' => $payment->amount,
                'paid_at' => $payment->paid_at,
                'description' => $payment->description ?: '...',
                'reference' => 'transfer:' . $transfer->id,
            ]);

            $l1 = $journal->ledger()->firstOrCreate([
                'company_id' => $transfer->company_id,
                'account_id' => $payment_account_id,
                'issued_at' => $journal->paid_at,
                'entry_type' => 'item',
                'credit' => $journal->amount,
            ]);
            $payment->reference = 'journal-entry-ledger:' . $l1->id;
            $payment->save();

            $l2 = $journal->ledger()->firstOrCreate([
                'company_id' => $transfer->company_id,
                'account_id' => $revenue_account_id,
                'issued_at' => $journal->paid_at,
                'entry_type' => 'item',
                'debit' => $journal->amount,
            ]);
            $revenue->reference = 'journal-entry-ledger:' . $l2->id;
            $revenue->save();
        });
    }

    protected function copyTaxes()
    {
        Tax::all()->each(function ($tax) {
            $chart = ChartAccount::firstOrCreate([
                'company_id' => $this->company_id,
                'type_id' => setting('double-entry.types_tax', 17),
                'code' => ChartAccount::max('code') + 1,
                'name' => $tax->name,
                'enabled' => 1,
            ]);

            AccountTax::firstOrCreate([
                'company_id' => $this->company_id,
                'account_id' => $chart->id,
                'tax_id' => $tax->id,
            ]);
        });
    }

    protected function copyInvoices()
    {
        Invoice::with(['items', 'payments'])->get()->each(function ($invoice) {
            $accounts_receivable_id = ChartAccount::code(setting('double-entry.accounts_receivable', 120))->pluck('id')->first();

            $ledger = Ledger::firstOrCreate([
                'company_id' => $this->company_id,
                'account_id' => $accounts_receivable_id,
                'ledgerable_id' => $invoice->id,
                'ledgerable_type' => get_class($invoice),
                'issued_at' => $invoice->invoiced_at,
                'entry_type' => 'total',
                'debit' => $invoice->amount,
            ]);

            $invoice->items()->each(function ($item) use($invoice) {
                $account_id = ChartAccount::code(setting('double-entry.accounts_sales', 400))->pluck('id')->first();

                $ledger = Ledger::firstOrCreate([
                    'company_id' => $this->company_id,
                    'account_id' => $account_id,
                    'ledgerable_id' => $item->id,
                    'ledgerable_type' => get_class($item),
                    'issued_at' => $invoice->invoiced_at,
                    'entry_type' => 'item',
                    'credit' => $item->total,
                ]);
            });

            $invoice->item_taxes()->each(function ($item_tax) use($invoice) {
                $account_id = AccountTax::where('tax_id', $item_tax->tax_id)->pluck('account_id')->first();

                $ledger = Ledger::firstOrCreate([
                    'company_id' => $this->company_id,
                    'account_id' => $account_id,
                    'ledgerable_id' => $item_tax->id,
                    'ledgerable_type' => get_class($item_tax),
                    'issued_at' => $invoice->invoiced_at,
                    'entry_type' => 'item',
                    'credit' => $item_tax->amount,
                ]);
            });

            $invoice->payments()->each(function ($payment) use($accounts_receivable_id) {
                $account_id = AccountBank::where('bank_id', $payment->account_id)->pluck('account_id')->first();

                $ledger = Ledger::firstOrCreate([
                    'company_id' => $this->company_id,
                    'account_id' => $account_id,
                    'ledgerable_id' => $payment->id,
                    'ledgerable_type' => get_class($payment),
                    'issued_at' => $payment->paid_at,
                    'entry_type' => 'total',
                    'debit' => $payment->amount,
                ]);

                $ledger = Ledger::firstOrCreate([
                    'company_id' => $this->company_id,
                    'account_id' => $accounts_receivable_id,
                    'ledgerable_id' => $payment->id,
                    'ledgerable_type' => get_class($payment),
                    'issued_at' => $payment->paid_at,
                    'entry_type' => 'item',
                    'credit' => $payment->amount,
                ]);
            });

        });
    }

    protected function copyRevenues()
    {
        Revenue::isNotTransfer()->get()->each(function ($revenue) {
            $account_id = AccountBank::where('bank_id', $revenue->account_id)->pluck('account_id')->first();

            $ledger = Ledger::firstOrCreate([
                'company_id' => $this->company_id,
                'account_id' => $account_id,
                'ledgerable_id' => $revenue->id,
                'ledgerable_type' => get_class($revenue),
                'issued_at' => $revenue->paid_at,
                'entry_type' => 'total',
                'debit' => $revenue->amount,
            ]);

            $accounts_receivable_id = ChartAccount::code(setting('double-entry.accounts_receivable', 120))->pluck('id')->first();

            $ledger = Ledger::firstOrCreate([
                'company_id' => $this->company_id,
                'account_id' => $accounts_receivable_id,
                'ledgerable_id' => $revenue->id,
                'ledgerable_type' => get_class($revenue),
                'issued_at' => $revenue->paid_at,
                'entry_type' => 'item',
                'credit' => $revenue->amount,
            ]);
        });
    }

    protected function copyBills()
    {
        Bill::with(['items', 'payments'])->get()->each(function ($bill) {
            $accounts_payable_id = ChartAccount::code(setting('double-entry.accounts_payable', 200))->pluck('id')->first();

            $ledger = Ledger::firstOrCreate([
                'company_id' => $this->company_id,
                'account_id' => $accounts_payable_id,
                'ledgerable_id' => $bill->id,
                'ledgerable_type' => get_class($bill),
                'issued_at' => $bill->billed_at,
                'entry_type' => 'total',
                'credit' => $bill->amount,
            ]);

            $bill->items()->each(function ($item) use($bill) {
                $account_id = ChartAccount::code(setting('double-entry.accounts_expenses', 628))->pluck('id')->first();

                $ledger = Ledger::firstOrCreate([
                    'company_id' => $this->company_id,
                    'account_id' => $account_id,
                    'ledgerable_id' => $item->id,
                    'ledgerable_type' => get_class($item),
                    'issued_at' => $bill->billed_at,
                    'entry_type' => 'item',
                    'debit' => $item->total,
                ]);
            });

            $bill->item_taxes()->each(function ($item_tax) use($bill) {
                $account_id = AccountTax::where('tax_id', $item_tax->tax_id)->pluck('account_id')->first();

                $ledger = Ledger::firstOrCreate([
                    'company_id' => $this->company_id,
                    'account_id' => $account_id,
                    'ledgerable_id' => $item_tax->id,
                    'ledgerable_type' => get_class($item_tax),
                    'issued_at' => $bill->billed_at,
                    'entry_type' => 'item',
                    'debit' => $item_tax->amount,
                ]);
            });

            $bill->payments()->each(function ($payment) use($accounts_payable_id) {
                $account_id = AccountBank::where('bank_id', $payment->account_id)->pluck('account_id')->first();

                $ledger = Ledger::firstOrCreate([
                    'company_id' => $this->company_id,
                    'account_id' => $account_id,
                    'ledgerable_id' => $payment->id,
                    'ledgerable_type' => get_class($payment),
                    'issued_at' => $payment->paid_at,
                    'entry_type' => 'total',
                    'credit' => $payment->amount,
                ]);

                $ledger = Ledger::firstOrCreate([
                    'company_id' => $this->company_id,
                    'account_id' => $accounts_payable_id,
                    'ledgerable_id' => $payment->id,
                    'ledgerable_type' => get_class($payment),
                    'issued_at' => $payment->paid_at,
                    'entry_type' => 'item',
                    'debit' => $payment->amount,
                ]);
            });
        });
    }

    protected function copyPayments()
    {
        Payment::isNotTransfer()->get()->each(function ($payment) {
            $account_id = AccountBank::where('bank_id', $payment->account_id)->pluck('account_id')->first();

            $ledger = Ledger::firstOrCreate([
                'company_id' => $this->company_id,
                'account_id' => $account_id,
                'ledgerable_id' => $payment->id,
                'ledgerable_type' => get_class($payment),
                'issued_at' => $payment->paid_at,
                'entry_type' => 'total',
                'credit' => $payment->amount,
            ]);

            $accounts_payable_id = ChartAccount::code(setting('double-entry.accounts_payable', 200))->pluck('id')->first();

            $ledger = Ledger::firstOrCreate([
                'company_id' => $this->company_id,
                'account_id' => $accounts_payable_id,
                'ledgerable_id' => $payment->id,
                'ledgerable_type' => get_class($payment),
                'issued_at' => $payment->paid_at,
                'entry_type' => 'item',
                'debit' => $payment->amount,
            ]);
        });
    }
}
