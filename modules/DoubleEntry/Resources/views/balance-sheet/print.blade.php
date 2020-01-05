@extends('layouts.print')

@section('title', trans('double-entry::general.balance_sheet'))

@section('content')
    <div class="box-header">
        <h2>{{ trans('double-entry::general.balance_sheet') }}</h2>
        <div class="text-muted">
            {{ setting('general.company_name') }}
            <br/>
            {{ Date::parse($start_date)->format($date_format) }} - {{ Date::parse($end_date)->format($date_format) }}
        </div>
    </div>
    @include('double-entry::balance-sheet.body')
@endsection