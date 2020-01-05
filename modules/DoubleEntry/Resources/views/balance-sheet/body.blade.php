<div class="box-body">
    <div class="table table-responsive">
        @foreach($classes as $class)
        @if (!empty($class->total))
        <table class="table" style="margin-top: 10px">
            <thead>
            <tr>
                <th class="col-sm-12" colspan="2">{{ strtoupper(trans($class->name)) }}</th>
            </tr>
            </thead>
        </table>
        @foreach($class->types as $type)
        @if (!empty($type->total))
        <table class="table">
            <thead>
                <tr>
                    <th class="col-sm-12" colspan="2" style="padding-left: 25px;">{{ trans($type->name) }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($accounts[$type->id] as $item)
                @if (!empty($item->total))
                <tr>
                    <td class="col-sm-9" style="padding-left: 50px;">{{ trans($item->name) }}</td>
                    <td class="col-sm-3 text-right">@money($item->total, setting('general.default_currency'), true)</td>
                </tr>
                @endif
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="col-sm-9" style="padding-left: 25px;">{{ trans('double-entry::general.total_type', ['type' => trans($type->name)]) }}</th>
                    <th class="col-sm-3 text-right">@money($type->total, setting('general.default_currency'), true)</th>
                </tr>
            </tfoot>
        </table>
        @endif
        @endforeach
        <table class="table" style="margin-top: 10px">
            <tfoot>
            <tr>
                <th class="col-sm-9">{{ strtoupper(trans('double-entry::general.total_type', ['type' => trans($class->name)])) }}</th>
                <th class="col-sm-3 text-right">@money($class->total, setting('general.default_currency'), true)</th>
            </tr>
            </tfoot>
        </table>
        @endif
        @endforeach
    </div>
</div>
