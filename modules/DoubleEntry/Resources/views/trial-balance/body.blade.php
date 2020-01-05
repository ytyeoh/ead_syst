@php
    $debits = 0;
    $credits = 0;
@endphp

<div class="box-body">
    <div class="table table-responsive">
        <table class="table" style="margin-top: 10px">
            <thead>
                <tr>
                    <th class="col-sm-6">{{ trans_choice('general.accounts', 1) }}</th>
                    <th class="col-sm-3 text-right">{{ trans('double-entry::general.debit') }}</th>
                    <th class="col-sm-3 text-right">{{ trans('double-entry::general.credit') }}</th>
                </tr>
            </thead>
        </table>
        @foreach($classes as $class)
        @if (!empty($class->debit_total) || !empty($class->credit_total))
        <table class="table">
            <thead>
                <tr>
                    <th class="col-sm-12" colspan="3">{{ trans($class->name) }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($accounts[$class->id] as $item)
                @php
                    $debits += $item->debit_total;
                    $credits += $item->credit_total;
                @endphp

                @if (!empty($item->debit_total) || !empty($item->credit_total))
                <tr>
                    <td class="col-sm-6" style="padding-left: 25px;">{{ trans($item->name) }}</td>
                    <td class="col-sm-3 text-right">@money($item->debit_total, setting('general.default_currency'), true)</td>
                    <td class="col-sm-3 text-right">@money($item->credit_total, setting('general.default_currency'), true)</td>
                </tr>
                @endif
            @endforeach
            </tbody>
        </table>
        @endif
        @endforeach
        <table class="table" style="margin-top: 30px">
            <tbody>
                <tr>
                    <th class="col-sm-6">{{ trans_choice('general.totals', 2) }}</th>
                    <th class="col-sm-3 text-right">@money($debits, setting('general.default_currency'), true)</th>
                    <th class="col-sm-3 text-right">@money($credits, setting('general.default_currency'), true)</th>
                </tr>
            </tbody>
        </table>
    </div>
</div>
