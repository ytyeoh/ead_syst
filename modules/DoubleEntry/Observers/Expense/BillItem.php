<?php

namespace Modules\DoubleEntry\Observers\Expense;

use App\Models\Expense\BillItem as Model;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\Ledger;

class BillItem
{
    /**
     * Listen to the created event.
     *
     * @param  Model  $item
     * @return void
     */
    public function created(Model $item)
    {
        $account_id = null;

        $r_items = request()->input('item');
        if (is_array($r_items)) {
            foreach ($r_items as $r_item) {
                if ($r_item['name'] != $item->name) {
                    continue;
                }

                $account_id = $r_item['de_account_id'];

                break;
            }
        }

        if (empty($account_id)) {
            $account_id = Account::code(setting('double-entry.accounts_expenses', 628))->pluck('id')->first();
        }

        $ledger = Ledger::create([
            'company_id' => session('company_id'),
            'account_id' => $account_id,
            'ledgerable_id' => $item->id,
            'ledgerable_type' => get_class($item),
            'issued_at' => $item->bill->billed_at,
            'entry_type' => 'item',
            'debit' => $item->total,
        ]);
    }

    /**
     * Listen to the created event.
     *
     * @param  Model  $item
     * @return void
     */
    public function updated(Model $item)
    {
        $ledger = Ledger::record($item->id, get_class($item))->first();

        if (empty($ledger)) {
            return;
        }

        $ledger->update([
            'company_id' => session('company_id'),
            'account_id' => 1,
            'ledgerable_id' => $item->id,
            'ledgerable_type' => get_class($item),
            'issued_at' => $item->bill->billed_at,
            'entry_type' => 'item',
            'debit' => $item->total,
        ]);
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $item
     * @return void
     */
    public function deleted(Model $item)
    {
        Ledger::record($item->id, get_class($item))->delete();
    }
}