@extends('layouts.admin')

@section('title', trans('double-entry::general.chart_of_accounts'))

@section('new_button')
@permission('create-double-entry-chart-of-accounts')
<span class="new-button"><a href="{{ url('double-entry/chart-of-accounts/create') }}" class="btn btn-success btn-sm"><span class="fa fa-plus"></span> &nbsp;{{ trans('general.add_new') }}</a></span>
<span><a href="{{ route('import.create', ['double-entry', 'chart-of-accounts']) }}" class="btn btn-default btn-sm"><span class="fa fa-download"></span> &nbsp;{{ trans('import.import') }}</a></span>
@endpermission
<span><a href="{{ route('chart-of-accounts.export', request()->input()) }}" class="btn btn-default btn-sm"><span class="fa fa-upload"></span> &nbsp;{{ trans('general.export') }}</a></span>
@endsection

@section('content')
@foreach($classes as $class)
<!-- Default box -->
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans($class->name) }}</h3>
    </div>
    <div class="box-body">
        <div class="table table-responsive">
            <table class="table table-striped table-hover" id="tbl-taxes">
                <thead>
                    <tr>
                        <th class="col-md-1">{{ trans('general.code') }}</th>
                        <th class="col-md-5">{{ trans('general.name') }}</th>
                        <th class="col-md-3">{{ trans_choice('general.types', 1) }}</th>
                        <th class="col-md-1 hidden-xs">{{ trans_choice('general.statuses', 1) }}</th>
                        <th class="col-md-2 text-center">{{ trans('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($class->accounts->sortBy('code') as $item)
                    <tr>
                        <td>{{ $item->code }}</td>
                        <td><a href="{{ route('chart-of-accounts.edit', $item->id) }}">{{ trans($item->name) }}</a></td>
                        <td>{{ trans($item->type->name) }}</td>
                        <td class="hidden-xs">
                            @if ($item->enabled)
                                <span class="label label-success">{{ trans('general.enabled') }}</span>
                            @else
                                <span class="label label-danger">{{ trans('general.disabled') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" data-toggle-position="left" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="{{ route('chart-of-accounts.edit', $item->id) }}">{{ trans('general.edit') }}</a></li>
                                    @if ($item->enabled)
                                        <li><a href="{{ route('chart-of-accounts.disable', $item->id) }}">{{ trans('general.disable') }}</a></li>
                                    @else
                                        <li><a href="{{ route('chart-of-accounts.enable', $item->id) }}">{{ trans('general.enable') }}</a></li>
                                    @endif
                                    @permission('create-double-entry-chart-of-accounts')
                                    <li class="divider"></li>
                                    <li><a href="{{ route('chart-of-accounts.duplicate', $item->id) }}">{{ trans('general.duplicate') }}</a></li>
                                    @endpermission
                                    @permission('delete-double-entry-chart-of-accounts')
                                    <li class="divider"></li>
                                    <li>{!! Form::deleteLink($item, 'double-entry/chart-of-accounts', 'accounts') !!}</li>
                                    @endpermission
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
</div>
@endforeach
<!-- /.box -->
@endsection
