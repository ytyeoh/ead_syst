@extends('layouts.admin')

@section('title', trans('double-entry::general.journal_entry'))

@section('new_button')
    <span class="new-button"><a href="{{ url('double-entry/journal-entry/create') }}" class="btn btn-success btn-sm"><span class="fa fa-plus"></span> &nbsp;Add New</a></span>
@endsection

@section('content')
    <!-- Default box -->
    <div class="box box-success">
        <div class="box-header with-border">
            @include('double-entry::partials.filters.date', ['url' => route('journal-entry.index')])
        </div>

        <div class="box-body">
            <div class="table table-responsive">
                <table class="table table-striped table-hover" id="tbl-taxes">
                    <thead>
                        <tr>
                            <th class="col-md-2">{{ trans('general.date') }}</th>
                            <th class="col-md-2 text-right amount-space">{{ trans('general.amount') }}</th>
                            <th class="col-md-4">{{ trans('general.description') }}</th>
                            <th class="col-md-2">{{ trans('general.reference') }}</th>
                            <th class="col-md-2 text-center">{{ trans('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($journals as $item)
                        <tr>
                            <td><a href="{{ url('double-entry/journal-entry/' . $item->id . '/edit') }}">{{ Date::parse($item->paid_at)->format($date_format) }}</a></td>
                            <td class="text-right amount-space">@money($item->amount, setting('general.default_currency'), true)</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->reference }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" data-toggle-position="left" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="{{ url('double-entry/journal-entry/' . $item->id . '/edit') }}">{{ trans('general.edit') }}</a></li>
                                        <li>{!! Form::deleteLink($item, 'double-entry/journal-entry') !!}</li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!-- /.box-body -->

        <div class="box-footer">
            @include('double-entry::partials.pagination', ['items' => $journals, 'type' => trans('double-entry::general.journal_entry')])
        </div>
        <!-- /.box-footer -->
    </div>
    <!-- /.box -->
@endsection
