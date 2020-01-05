<?php

namespace Modules\DoubleEntry\Http\Controllers;

use App\Http\Controllers\Controller;
use Date;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\DEClass;
use Modules\DoubleEntry\Models\Type;

class BalanceSheet extends Controller
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

        return view('double-entry::balance-sheet.' . $template, compact('classes', 'accounts', 'start_date', 'end_date'));
    }

    public function export()
    {
        \Excel::create(trans('double-entry::general.balance_sheet'), function ($excel) {
            $excel->sheet(trans('double-entry::general.balance_sheet'), function ($sheet) {
                $template = 'body';

                list($classes, $accounts) = $this->getData();

                $sheet->loadView('double-entry::balance-sheet.' . $template, compact('classes', 'accounts'));
            });
        })->download('xlsx');
    }

    protected function getData()
    {
        $accounts = [];

        $income = 0;

        $classes = DEClass::with('types', 'types.accounts')->get()->reject(function ($c) {
            return ($c->name == 'double-entry::classes.expenses');
        });

        foreach ($classes as $class) {
            $class->total = 0;

            foreach ($class->types as $type) {
                $type->total = 0;

                foreach ($type->accounts as $item) {
                    $item->total = abs($item->debit_total - $item->credit_total);

                    if ($class->name == 'double-entry::classes.income') {
                        $income += $item->total;
                        continue;
                    }

                    if ($item->code == '320') {
                        $item->total += $income;
                    }

                    $type->total += $item->total;
                    $class->total += $item->total;

                    $accounts[$type->id][] = $item;
                }
            }
        }

        $classes = $classes->reject(function ($c) {
            return ($c->name == 'double-entry::classes.income');
        })->all();

        return [$classes, $accounts];
    }
}
