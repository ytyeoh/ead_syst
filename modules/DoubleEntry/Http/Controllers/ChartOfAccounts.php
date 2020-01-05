<?php

namespace Modules\DoubleEntry\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Banking\Account as CoreAccount;
use App\Traits\Uploads;
use App\Utilities\Import;
use App\Utilities\ImportFile;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Models\DEClass;
use Modules\DoubleEntry\Models\Type;
use Modules\DoubleEntry\Http\Requests\Account as Request;

class ChartOfAccounts extends Controller
{
    use Uploads;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $classes = DEClass::with('accounts')->get();

        return view('double-entry::chart-of-accounts.index', compact('classes'));
    }

    /**
     * Show the form for viewing the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        return redirect()->route('chart-of-accounts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $types = [];

        $classes = DEClass::pluck('name', 'id')->map(function ($name) {
            return trans($name);
        })->toArray();

        $all_types = Type::all()->reject(function ($t) {
            return ($t->id == setting('double-entry.types_tax', 17));
        });

        foreach ($all_types as $type) {
            if (!isset($classes[$type->class_id])) {
                continue;
            }

            $types[$classes[$type->class_id]][$type->id] = trans($type->name);
        }

        ksort($types);

        return view('double-entry::chart-of-accounts.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $account = Account::create($request->all());

        if ($account->type_id == setting('double-entry.types_bank', 6)) {
            $bank = CoreAccount::create([
                'company_id' => $account->company_id,
                'name' => $account->name,
                'number' => $account->code,
                'currency_code' => setting('general.default_currency'),
                'opening_balance' => 0,
                'enabled' => $account->enabled,
                'bank_name' => 'chart-of-accounts',
            ]);

            AccountBank::create([
                'company_id' => $account->company_id,
                'account_id' => $account->id,
                'bank_id' => $bank->id,
            ]);
        }

        $message = trans('messages.success.added', ['type' => trans_choice('general.accounts', 1)]);

        flash($message)->success();

        return redirect()->route('chart-of-accounts.index');
    }

    /**
     * Duplicate the specified resource.
     *
     * @param  Account  $chart_of_account
     *
     * @return Response
     */
    public function duplicate(Account $chart_of_account)
    {
        $clone = $chart_of_account->duplicate();

        $message = trans('messages.success.duplicated', ['type' => trans_choice('general.accounts', 1)]);

        flash($message)->success();

        return redirect()->route('chart-of-accounts.edit', $clone->id);
    }

    /**
     * Import the specified resource.
     *
     * @param  ImportFile  $import
     *
     * @return Response
     */
    public function import(ImportFile $import)
    {
        if (!Import::createFromFile($import, 'Account', 'Modules\DoubleEntry')) {
            return redirect('common/import/double-entry/chart-of-accounts');
        }

        $message = trans('messages.success.imported', ['type' => trans_choice('general.accounts', 2)]);

        flash($message)->success();

        return redirect()->route('chart-of-accounts.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Account  $chart_of_account
     *
     * @return Response
     */
    public function edit(Account $chart_of_account)
    {
        $account = $chart_of_account;

        $account->name = trans($account->name);

        $types = [];

        $classes = DEClass::pluck('name', 'id')->map(function ($name) {
            return trans($name);
        })->toArray();

        if ($chart_of_account->type_id == setting('double-entry.types_tax', 17)) {
            $all_types = Type::all();
        } else {
            $all_types = Type::all()->reject(function ($t) {
                return ($t->id == setting('double-entry.types_tax', 17));
            });
        }

        foreach ($all_types as $type) {
            if (!isset($classes[$type->class_id])) {
                continue;
            }

            $types[$classes[$type->class_id]][$type->id] = trans($type->name);
        }

        ksort($types);

        return view('double-entry::chart-of-accounts.edit', compact('account', 'types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Account  $chart_of_account
     * @param  Request  $request
     *
     * @return Response
     */
    public function update(Account $chart_of_account, Request $request)
    {
        /*$relationships = $this->countRelationships($tax, [
            'items' => 'items',
            'invoice_items' => 'invoices',
            'bill_items' => 'bills',
        ]);*/

        $request['code'] = !empty($request['code']) ? $request['code'] : $chart_of_account->code;
        $request['type_id'] = !empty($request['type_id']) ? $request['type_id'] : $chart_of_account->type_id;

        if (empty($relationships) || $request['enabled']) {
            $lang = array_flip(trans('double-entry::accounts'));
            if (!empty($lang[$request['name']])) {
                $request['name'] = 'double-entry::accounts.' . $lang[$request['name']];
            }

            $bank_type = setting('double-entry.types_bank', 6);
            $tax_type = setting('double-entry.types_tax', 17);

            $is_new_bank = (($request['type_id'] == $bank_type) && ($chart_of_account->type_id != $bank_type)) ? true : false;

            $chart_of_account->update($request->all());

            if ($chart_of_account->type_id == $bank_type) {
                if ($is_new_bank) {
                    $bank = CoreAccount::create([
                        'company_id' => $chart_of_account->company_id,
                        'name' => trans($chart_of_account->name),
                        'number' => $chart_of_account->code,
                        'currency_code' => setting('general.default_currency'),
                        'opening_balance' => 0,
                        'enabled' => $chart_of_account->enabled,
                        'bank_name' => 'chart-of-accounts',
                    ]);

                    AccountBank::create([
                        'company_id' => $chart_of_account->company_id,
                        'account_id' => $chart_of_account->id,
                        'bank_id' => $bank->id,
                    ]);
                } else {
                    $core_account = $chart_of_account->bank->bank;

                    $core_account->update([
                        'company_id' => $chart_of_account->company_id,
                        'name' => trans($chart_of_account->name),
                        'number' => $core_account->number,
                        'currency_code' => $core_account->currency_code,
                        'opening_balance' => $core_account->opening_balance,
                        'enabled' => $chart_of_account->enabled,
                    ]);
                }
            }

            if ($chart_of_account->type_id == $tax_type) {
                $tax = $chart_of_account->tax->tax;

                $tax->update([
                    'company_id' => $chart_of_account->company_id,
                    'name' => $chart_of_account->name,
                    'rate' => $tax->rate,
                    'type' => $tax->type,
                    'enabled' => $chart_of_account->enabled,
                ]);
            }

            $message = trans('messages.success.updated', ['type' => trans_choice('general.accounts', 1)]);

            flash($message)->success();

            return redirect()->route('chart-of-accounts.index');
        } else {
            $message = trans('messages.warning.disabled', ['name' => $chart_of_account->name, 'text' => implode(', ', $relationships)]);

            flash($message)->warning();

            return redirect()->route('chart-of-accounts.edit', $chart_of_account->id);
        }
    }

    /**
     * Enable the specified resource.
     *
     * @param  Account  $chart_of_account
     *
     * @return Response
     */
    public function enable(Account $chart_of_account)
    {
        $chart_of_account->enabled = 1;
        $chart_of_account->save();

        if ($chart_of_account->type_id == setting('double-entry.types_bank', 6)) {
            $core_account = $chart_of_account->bank->bank;
            $core_account->enabled = 1;
            $core_account->save();
        }

        $message = trans('messages.success.enabled', ['type' => trans_choice('general.accounts', 1)]);

        flash($message)->success();

        return redirect()->route('chart-of-accounts.index');
    }

    /**
     * Disable the specified resource.
     *
     * @param  Account  $chart_of_account
     *
     * @return Response
     */
    public function disable(Account $chart_of_account)
    {
        $chart_of_account->enabled = 0;
        $chart_of_account->save();

        if ($chart_of_account->type_id == setting('double-entry.types_bank', 6)) {
            $core_account = $chart_of_account->bank->bank;
            $core_account->enabled = 0;
            $core_account->save();
        }

        $message = trans('messages.success.disabled', ['type' => trans_choice('general.accounts', 1)]);

        flash($message)->success();

        return redirect()->route('chart-of-accounts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Account  $chart_of_account
     *
     * @return Response
     */
    public function destroy(Account $chart_of_account)
    {
        $relationships = [];
        
        /*$relationships = $this->countRelationships($account, [
            'items' => 'items',
            'invoice_items' => 'invoices',
            'bill_items' => 'bills',
        ]);*/
        
        if ($chart_of_account->type_id == setting('double-entry.types_bank', 6)) {
            $relationships[] = strtolower(trans_choice('general.accounts', 1));
        }
        if ($chart_of_account->type_id == setting('double-entry.types_tax', 17)) {
            $relationships[] = strtolower(trans_choice('general.tax_rates', 1));
        }

        if (empty($relationships)) {
            $chart_of_account->delete();

            $message = trans('messages.success.deleted', ['type' => trans_choice('general.accounts', 1)]);

            flash($message)->success();
        } else {
            $message = trans('messages.warning.deleted', ['name' => $chart_of_account->name, 'text' => implode(', ', $relationships)]);

            flash($message)->warning();
        }

        return redirect()->route('chart-of-accounts.index');
    }

    /**
     * Export the specified resource.
     *
     * @return Response
     */
    public function export()
    {
        \Excel::create('accounts', function($excel) {
            $excel->sheet('accounts', function($sheet) {
                $sheet->fromModel(Account::all()->makeHidden([
                    'id', 'company_id', 'debit_total', 'credit_total', 'created_at', 'updated_at', 'deleted_at'
                ]));
            });
        })->download('xlsx');
    }
}
