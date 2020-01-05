<?php

namespace Modules\DoubleEntry\Listeners;

use App\Events\AdminMenuCreated as Event;

class AdminMenuCreated
{
    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        $user = auth()->user();

        if (!$user->can([
            'read-double-entry-chart-of-accounts',
            'read-double-entry-journal-entry',
            'read-double-entry-general-ledger',
            'read-double-entry-balance-sheet',
            'read-double-entry-trial-balance',
        ])) {
            return;
        }

        $attr = ['icon' => 'fa fa-angle-double-right'];

        $event->menu->dropdown(trans('double-entry::general.double_entry'), function ($sub) use($user, $attr) {
            if ($user->can('read-double-entry-chart-of-accounts')) {
                $sub->url('double-entry/chart-of-accounts', trans('double-entry::general.chart_of_accounts'), 1, $attr);
            }

            if ($user->can('read-double-entry-journal-entry')) {
                $sub->url('double-entry/journal-entry', trans('double-entry::general.journal_entry'), 2, $attr);
            }

            if ($user->can('read-double-entry-general-ledger')) {
                $sub->url('double-entry/general-ledger', trans('double-entry::general.general_ledger'), 3, $attr);
            }

            if ($user->can('read-double-entry-balance-sheet')) {
                $sub->url('double-entry/balance-sheet', trans('double-entry::general.balance_sheet'), 4, $attr);
            }

            if ($user->can('read-double-entry-trial-balance')) {
                $sub->url('double-entry/trial-balance', trans('double-entry::general.trial_balance'), 5, $attr);
            }

            $sub->url('double-entry/settings', trans_choice('general.settings', 2), 6, $attr);
        }, 6, [
            'title' => trans('double-entry::general.double_entry'),
            'icon' => 'fa fa-balance-scale',
        ]);
    }
}
