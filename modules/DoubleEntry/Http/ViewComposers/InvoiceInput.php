<?php

namespace Modules\DoubleEntry\Http\ViewComposers;

use App\Models\Income\InvoiceItem;
use Illuminate\View\View;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Models\Type;

class InvoiceInput
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $de_accounts = [];

        $types = Type::where('class_id', 4)->pluck('name', 'id')->map(function ($name) {
            return trans($name);
        })->toArray();

        $accounts = Account::with(['type'])->enabled()->orderBy('code')->get();

        foreach ($accounts as $account) {
            if (!isset($types[$account->type_id])) {
                continue;
            }

            $de_accounts[$types[$account->type_id]][$account->id] = $account->code . ' - ' . trans($account->name);
        }

        ksort($de_accounts);

        $view->with(['de_accounts' => $de_accounts]);

        $url = explode('/', request()->url());
        $action = array_pop($url);

        $item_accounts = [];

        if ($action == 'edit') {
            $invoice_id = end($url);

            $items = InvoiceItem::where('invoice_id', $invoice_id)->pluck('id');

            foreach ($items as $item_id) {
                $account_id = Ledger::record($item_id, 'App\Models\Income\InvoiceItem')->pluck('account_id');

                if (empty($account_id)) {
                    continue;
                }

                $item_accounts[$item_id] = $account_id;
            }

            $view->with(['item_accounts' => $item_accounts]);
        }

        if ($view->getName() == 'incomes.invoices.item') {
            $item_row = 0;
            $item = [];

            $data = $view->getData();

            if (isset($data['item_row'])) {
                $item_row = $data['item_row'];
            }

            if (isset($data['item'])) {
                $item = $data['item'];
            }

            // Push to a stack
            $view->getFactory()->startPush('name_td_start', view('double-entry::partials.input_td', compact('de_accounts', 'item_row', 'item_accounts', 'item')));
        }
    }
}
