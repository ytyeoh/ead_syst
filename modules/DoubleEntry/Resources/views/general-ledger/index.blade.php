@extends('layouts.admin')

@section('title', trans('double-entry::general.general_ledger'))

@section('new_button')
    <span class="new-button"><a href="{{ route('general-ledger.index') }}?print=1&start_date={{ $start_date }}&end_date={{ $end_date }}&de_account_id={{ request('de_account_id') }}" target="_blank" class="btn btn-default btn-sm"><span class="fa fa-print"></span> &nbsp;{{ trans('general.print') }}</a></span>
    <span><a href="{{ route('general-ledger.export') }}?start_date={{ $start_date }}&end_date={{ $end_date }}&de_account_id={{ request('de_account_id') }}" class="btn btn-default btn-sm"><span class="fa fa-file-excel-o"></span> &nbsp;{{ trans('general.export') }}</a></span>
@endsection

@section('content')<!-- Default box -->
    <div class="box box-success">
        <div class="box-header with-border">
            @include('double-entry::partials.filters.date', ['url' => route('general-ledger.index')])
        </div>
    </div>

    @include('double-entry::general-ledger.body')
    <!-- /.box -->
@endsection
