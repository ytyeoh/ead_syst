<?php

namespace Modules\DoubleEntry\Http\Controllers;

use App\Http\Controllers\Controller;
use Date;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\Type;
use Modules\DoubleEntry\Models\DEClass;
use Modules\DoubleEntry\Models\Ledger;

class GeneralLedger extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        list($accounts) = $this->getData();

        // Check if it's a print or normal request
        if (request('print')) {
            $template = 'print';
        } else {
            $template = 'index';
        }

        $types = Type::pluck('name', 'id')->map(function ($name) {
            return trans($name);
        })->toArray();

        $de_accounts = [];
        Account::with(['type'])->orderBy('code')->get()->each(function ($account) use($types, &$de_accounts) {
            $de_accounts[$types[$account->type_id]][$account->id] = trans($account->name);
        });
        ksort($de_accounts);

        $start_date = request('start_date', Date::now()->startOfYear()->format('Y-m-d'));
        $end_date = request('end_date', Date::now()->endOfYear()->format('Y-m-d'));

        return view('double-entry::general-ledger.' . $template, compact('accounts', 'de_accounts', 'start_date', 'end_date'));
    }

    public function export()
    {
        \Excel::create(trans('double-entry::general.general_ledger'), function ($excel) {
            $excel->sheet(trans('double-entry::general.general_ledger'), function ($sheet) {
                $template = 'body';

                list($accounts) = $this->getData();

                $sheet->loadView('double-entry::general-ledger.' . $template, compact('accounts'));
            });
        })->download('xlsx');
    }

    protected function getData()
    {
        $limit = request('limit', setting('general.list_limit', '25'));
    
        $model = Account::with(['type', 'ledgers']);

        if (request()->has('de_account_id')) {
            $model->where('id', (int) request('de_account_id'));
        }

        $accounts = $model->orderBy('code')->get()->each(function ($account) use($limit) {
            $account->transactions = $account->ledgers()->orderBy('issued_at')->paginate($limit);
        });

        return [$accounts];
    }
}
