<?php

namespace Modules\DoubleEntry\Http\Controllers;

use App\Http\Controllers\Controller;
use Date;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\DEClass;
use Modules\DoubleEntry\Models\Type;

class TrialBalance extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        list($classes, $accounts) = $this->getData();

        // Check if it's a print or normal request
        if (request('print')) {
            $template = 'print';
        } else {
            $template = 'index';
        }

        $start_date = request('start_date', Date::now()->startOfYear()->format('Y-m-d'));
        $end_date = request('end_date', Date::now()->endOfYear()->format('Y-m-d'));

        return view('double-entry::trial-balance.' . $template, compact('classes', 'accounts', 'start_date', 'end_date'));
    }

    public function export()
    {
        \Excel::create(trans('double-entry::general.trial_balance'), function ($excel) {
            $excel->sheet(trans('double-entry::general.trial_balance'), function ($sheet) {
                $template = 'body';

                list($classes, $accounts) = $this->getData();

                $sheet->loadView('double-entry::trial-balance.' . $template, compact('classes', 'accounts'));
            });
        })->download('xlsx');
    }

    protected function getData()
    {
        $classes = DEClass::with('accounts')->get();

        $accounts = [];

        foreach ($classes as $class) {
            $class->debit_total = $class->credit_total = 0;

            foreach ($class->accounts as $item) {
                $i = new \stdClass();
                $i->name = $item->name;

                $total = $item->debit_total - $item->credit_total;

                if ($total < 0) {
                    $i->debit_total = 0;
                    $i->credit_total = abs($total);
                } else {
                    $i->debit_total = abs($total);
                    $i->credit_total = 0;
                }

                $class->debit_total += $i->debit_total;
                $class->credit_total += $i->credit_total;

                $accounts[$class->id][] = $i;
            }
        }

        return [$classes, $accounts];
    }
}
