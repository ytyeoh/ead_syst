@php
    $date_types = [
        'today' => trans('double-entry::filters.date_types.today'),
        'this_week' => trans('double-entry::filters.date_types.this_week'),
        'this_month' => trans('double-entry::filters.date_types.this_month'),
        'this_quarter' => trans('double-entry::filters.date_types.this_quarter'),
        'this_year' => trans('double-entry::filters.date_types.this_year'),
        'year_to_date' => trans('double-entry::filters.date_types.year_to_date')
    ];
@endphp

{!! Form::open(['url' => $url, 'role' => 'form', 'method' => 'GET']) !!}
    <div id="items" class="pull-left box-filter">
        {!! Form::select('date_types', $date_types, request('date_types', 'this_year'), ['id' => 'filter-date-types', 'class' => 'form-control input-filter input-sm', 'placeholder' => trans('general.form.select.field', ['field' => trans('double-entry::filters.date_type')])]) !!}
        {!! Form::text('start_date', request('start_date', $start_date), ['id' => 'filter-start-date', 'class' => 'form-control input-filter input-sm', 'placeholder' => trans('double-entry::filters.start_date'), 'autocomplete' => 'off']) !!}
        {!! Form::text('end_date', request('end_date', $end_date), ['id' => 'filter-end-date', 'class' => 'form-control input-filter input-sm', 'placeholder' => trans('double-entry::filters.end_date'), 'autocomplete' => 'off']) !!}

        @if(isset($de_accounts))
        {!! Form::select('de_account_id', $de_accounts, request('de_account_id'), ['id' => 'filter-de-account-id', 'class' => 'form-control input-filter input-sm', 'placeholder' => trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)])]) !!}
        @endif

        {!! Form::button('<span class="fa fa-filter"></span> &nbsp;' . trans('general.filter'), ['type' => 'submit', 'class' => 'btn btn-sm btn-default btn-filter']) !!}
    </div>
{!! Form::close() !!}

@push('stylesheet')
    <style type="text/css">
        #items span#select2-filter-date-types-container,
        #items span#select2-filter-de-account-id-container {
            font-size: 12px;
        }

        #items .select2-container--default .select2-selection--single, .select2-selection .select2-selection--single {
            border: 1px solid #d2d6de;
            border-radius: 0;
            padding: 4px 0px;
            height: 30px;
        }
    </style>
@endpush

@push('scripts')
    <script type="text/javascript">
        var date = new Date();

        $(document).ready(function(){
            $('#filter-date-types').select2({
                placeholder: {
                    id: '-1', // the value of the option
                    text: "{{ trans('general.form.select.field', ['field' => trans('double-entry::filters.date_type')]) }}"
                }
            });

            $('#filter-de-account-id').select2({
                placeholder: {
                    id: '-1', // the value of the option
                    text: "{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
                }
            });

            //Date picker
            $('#filter-start-date').datepicker({
                format: 'yyyy-mm-dd',
                todayBtn: 'linked',
                weekStart: 1,
                autoclose: true,
                language: '{{ language()->getShortCode() }}'
            });

            $('#filter-end-date').datepicker({
                format: 'yyyy-mm-dd',
                todayBtn: 'linked',
                weekStart: 1,
                autoclose: true,
                language: '{{ language()->getShortCode() }}'
            });
        });

        $(document).on('change', '#filter-date-types', function (e) {
            $.ajax({
                url: '{{ route("double-entry.filter") }}',
                type: 'GET',
                dataType: 'JSON',
                data: {date_type: $(this).val()},
                success: function(json) {
                    $('#filter-start-date').val(json['start_date']);
                    $('#filter-start-date').datepicker("refresh");

                    $('#filter-end-date').val(json['end_date']);
                    $('#filter-end-date').datepicker("refresh");
                }
            });
        });
    </script>
@endpush

@push('js')
    <script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
    @if (language()->getShortCode() != 'en')
    <script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/locales/bootstrap-datepicker.' . language()->getShortCode() . '.js') }}"></script>
    @endif
@endpush

@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/datepicker3.css') }}">
@endpush