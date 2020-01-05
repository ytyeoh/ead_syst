@extends('layouts.admin')

@section('title', trans('double-entry::general.trial_balance'))

@section('new_button')
    <span class="new-button"><a href="{{ route('trial-balance.index') }}?print=1&start_date={{ $start_date }}&end_date={{ $end_date }}" target="_blank" class="btn btn-default btn-sm"><span class="fa fa-print"></span> &nbsp;{{ trans('general.print') }}</a></span>
    <span><a href="{{ route('trial-balance.export') }}?start_date={{ $start_date }}&end_date={{ $end_date }}" class="btn btn-default btn-sm"><span class="fa fa-file-excel-o"></span> &nbsp;{{ trans('general.export') }}</a></span>
@endsection

@section('content')
    <!-- Default box -->
    <div class="box box-success">
        <div class="box-header with-border">
            @include('double-entry::partials.filters.date', ['url' => route('trial-balance.index')])
        </div>

        @include('double-entry::trial-balance.body')
    </div>
    <!-- /.box -->
@endsection
