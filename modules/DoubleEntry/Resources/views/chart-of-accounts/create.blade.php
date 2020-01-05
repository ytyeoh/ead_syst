@extends('layouts.admin')

@section('title', trans('general.title.new', ['type' => trans_choice('general.accounts', 1)]))

@section('content')
      <!-- Default box -->
      <div class="box box-success">
        {!! Form::open(['url' => 'double-entry/chart-of-accounts', 'role' => 'form']) !!}

        <div class="box-body">
            {{ Form::textGroup('name', trans('general.name'), 'id-card-o') }}

            {{ Form::numberGroup('code', trans('general.code'), 'code') }}

            {{ Form::selectGroup('type_id', trans_choice('general.types', 1), 'bars', $types) }}

            {{ Form::textareaGroup('description', trans('general.description')) }}

            {{ Form::radioGroup('enabled', trans('general.enabled')) }}
        </div>
        <!-- /.box-body -->

        <div class="box-footer">
            {{ Form::saveButtons('double-entry/chart-of-accounts') }}
        </div>
        <!-- /.box-footer -->

        {!! Form::close() !!}
      </div>
@endsection

@push('scripts')
<script type="text/javascript">
    var text_yes = '{{ trans('general.yes') }}';
    var text_no = '{{ trans('general.no') }}';

    $(document).ready(function(){
        $("#type_id").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.types', 1)]) }}"
        });
    });
</script>
@endpush
