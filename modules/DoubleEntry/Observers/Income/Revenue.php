<?php

namespace Modules\DoubleEntry\Observers\Income;

use App\Models\Income\Revenue as Model;
use App\Models\Setting\Category;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Models\Ledger;

class Revenue
{
    /**
     * Listen to the created event.
     *
     * @param  Model  $revenue
     * @return void
     */
    public function created(Model $revenue)
    {
        if ($this->isJournal($revenue) || $this->isTransfer($revenue)) {
            return;
        }
        
        $account_id = AccountBank::where('bank_id', $revenue->account_id)->pluck('account_id')->first();

        if (empty($account_id)) {
            return;
        }

        Ledger::create([
            'company_id' => session('company_id'),
            'account_id' => $account_id,
            'ledgerable_id' => $revenue->id,
            'ledgerable_type' => get_class($revenue),
            'issued_at' => $revenue->paid_at,
            'entry_type' => 'total',
            'debit' => $revenue->amount,
        ]);

        Ledger::create([
            'company_id' => session('company_id'),
            'account_id' => Account::code(setting('double-entry.accounts_sales', 400))->pluck('id')->first(),
            'ledgerable_id' => $revenue->id,
            'ledgerable_type' => get_class($revenue),
            'issued_at' => $revenue->paid_at,
            'entry_type' => 'item',
            'credit' => $revenue->amount,
        ]);
    }

    /**
     * Listen to the created event.
     *
     * @param  Model  $revenue
     * @return void
     */
    public function updated(Model $revenue)
    {
        if ($this->isJournal($revenue) || $this->isTransfer($revenue)) {
            return;
        }
        
        $ledger = Ledger::record($revenue->id, get_class($revenue))->where('entry_type', 'total')->first();

        if (empty($ledger)) {
            return;
        }

        $account_id = AccountBank::where('bank_id', $revenue->account_id)->pluck('account_id')->first();

        if (empty($account_id)) {
            return;
        }

        $ledger->update([
            'company_id' => session('company_id'),
            'account_id' => $account_id,
            'ledgerable_id' => $revenue->id,
            'ledgerable_type' => get_class($revenue),
            'issued_at' => $revenue->paid_at,
            'entry_type' => 'total',
            'debit' => $revenue->amount,
        ]);

        $ledger = Ledger::record($revenue->id, get_class($revenue))->where('entry_type', 'item')->first();

        if (empty($ledger)) {
            return;
        }

        $ledger->update([
            'company_id' => session('company_id'),
            'account_id' => Account::code(setting('double-entry.accounts_sales', 400))->pluck('id')->first(),
            'ledgerable_id' => $revenue->id,
            'ledgerable_type' => get_class($revenue),
            'issued_at' => $revenue->paid_at,
            'entry_type' => 'item',
            'credit' => $revenue->amount,
        ]);
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $revenue
     * @return void
     */
    public function deleted(Model $revenue)
    {
        if ($this->isJournal($revenue) || $this->isTransfer($revenue)) {
            return;
        }
        
        Ledger::record($revenue->id, get_class($revenue))->delete();
    }

    protected function isJournal($revenue)
    {
        if (empty($revenue->reference)) {
            return false;
        }

        if (!str_contains($revenue->reference, 'journal-entry-ledger:')) {
            return false;
        }

        return true;
    }

    protected function isTransfer($revenue)
    {
        if ($revenue->category_id != Category::transfer()) {
            return false;
        }

        return true;
    }
}