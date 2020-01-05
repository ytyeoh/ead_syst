<?php

namespace Modules\DoubleEntry\Listeners\Updates;

use App\Events\UpdateFinished;
use App\Listeners\Updates\Listener;
use App\Models\Income\Revenue;
use App\Models\Expense\Payment;
use Modules\DoubleEntry\Models\Ledger;

class Version1026 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '1.0.26';

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(UpdateFinished $event)
    {
        // Check if should listen
        if (!$this->check($event)) {
            return;
        }

        Ledger::where('ledgerable_type', 'Modules\DoubleEntry\Models\Journal')->where('company_id', '<>', '0')->each(function($ledger) {
            Revenue::where('reference', 'journal-entry:' . $ledger->ledgerable_id)->where('company_id', '<>', '0')->each(function($revenue) use($ledger) {
                $revenue->reference = 'journal-entry-ledger:' . $ledger->id;
                $revenue->save();
            });

            Payment::where('reference', 'journal-entry:' . $ledger->ledgerable_id)->where('company_id', '<>', '0')->each(function($payment) use($ledger) {
                $payment->reference = 'journal-entry-ledger:' . $ledger->id;
                $payment->save();
            });
        });
    }
}
