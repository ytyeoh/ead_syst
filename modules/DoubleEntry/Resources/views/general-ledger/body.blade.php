<div class="table table-responsive">
    <table class="table margin-top">
        <thead>
            <tr>
                <th class="col-sm-3">&nbsp;</th>
                <th class="col-sm-3">{{ strtoupper(trans('general.description')) }}</th>
                <th class="col-sm-2 text-right">{{ strtoupper(trans('double-entry::general.debit')) }}</th>
                <th class="col-sm-2 text-right">{{ strtoupper(trans('double-entry::general.credit')) }}</th>
                <th class="col-sm-2 text-right">{{ strtoupper(trans('general.balance')) }}</th>
            </tr>
        </thead>
    </table>
</div>

@foreach($accounts as $account)
    @if (!empty($account->debit_total) || !empty($account->credit_total))
    @php
        $closing_balance = $account->opening_balance;
    @endphp
    <div class="box box-default">
        <div class="box-header with-border">
            {{ trans($account->name) }} ({{ trans($account->type->name) }})
        </div>
        <div class="box-body">
            <div class="table table-responsive">
                <table class="table table-striped table-hover" id="tbl-ledgers">
                    <thead>
                        <tr>
                            <th class="col-sm-10" colspan="3">{{ trans('accounts.opening_balance') }}</th>
                            <th class="col-sm-2 text-right">@money($account->opening_balance, setting('general.default_currency'), true)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($account->transactions as $ledger)
                        @php
                            $closing_balance += $ledger->debit - $ledger->credit;
                        @endphp
                        <tr>
                            <td class="col-sm-3">{{ Date::parse($ledger->issued_at)->format($date_format) }}</td>
                            <td class="col-sm-3">{{ $ledger->description }}</th>
                            <td class="col-sm-2 text-right">@if (!empty($ledger->debit)) @money((double) $ledger->debit, setting('general.default_currency'), true) @endif</td>
                            <td class="col-sm-2 text-right">@if (!empty($ledger->credit)) @money((double) $ledger->credit, setting('general.default_currency'), true) @endif</td>
                            <td class="col-sm-2 text-right">@money((double) abs($closing_balance), setting('general.default_currency'), true)</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="col-sm-10" colspan="3">{{ trans('double-entry::general.balance_change') }}</th>
                            <th class="col-sm-2 text-right">@money(abs($closing_balance - $account->opening_balance), setting('general.default_currency'), true)</th>
                        </tr>
                    </tfoot>
                    <tfoot>
                        <tr>
                            <th class="col-sm-3">{{ trans('double-entry::general.totals_balance') }}</th>
                            <th class="col-sm-3">&nbsp;</th>
                            <th class="col-sm-2 text-right">@money($account->debit_total, setting('general.default_currency'), true)</th>
                            <th class="col-sm-2 text-right">@money($account->credit_total, setting('general.default_currency'), true)</th>
                            <th class="col-sm-2 text-right">@money(abs($closing_balance), setting('general.default_currency'), true)</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @if (!request()->has('print') && !Request::routeIs('general-ledger.export'))
        <div class="box-footer">
            @include('partials.admin.pagination', ['items' => $account->transactions, 'type' => 'transactions'])
        </div>
        <!-- /.box-footer -->
        @endif
    </div>
    @endif
@endforeach
<!-- /.box-body -->
