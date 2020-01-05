@extends('layouts.admin')

@section('title', trans('general.title.new', ['type' => trans('double-entry::general.journal_entry')]))

@section('content')
<!-- Default box -->
<div class="box box-success">
    {!! Form::open(['url' => 'double-entry/journal-entry', 'files' => true, 'role' => 'form']) !!}

    <div class="box-body">
        {{ Form::textGroup('paid_at', trans('general.date'), 'calendar',['id' => 'paid_at', 'class' => 'form-control', 'required' => 'required', 'data-inputmask' => '\'alias\': \'yyyy-mm-dd\'', 'data-mask' => ''], Date::now()->toDateString()) }}

        {{ Form::textGroup('reference', trans('general.reference'), 'file-text-o', []) }}

        {{ Form::textareaGroup('description', trans('general.description'), null, ['rows' => '3', 'required' => 'required']) }}

        <div class="form-group col-md-12">
            {!! Form::label('items', trans_choice('general.items', 2), ['class' => 'control-label']) !!}
            <div class="table-responsive">
                <table class="table table-bordered" id="items">
                    <thead>
                    <tr style="background-color: #f9f9f9;">
                        @stack('actions_th_start')
                        <th width="5%"  class="text-center" required>{{ trans('general.actions') }}</th>
                        @stack('actions_th_end')
                        @stack('name_th_start')
                        <th width="20%" class="text-left required">{{ trans('double-entry::general.account') }}</th>
                        @stack('name_th_end')
                        @stack('quantity_th_start')
                        <th width="20%" class="text-right">{{ trans('double-entry::general.debit') }}</th>
                        @stack('quantity_th_end')
                        @stack('price_th_start')
                        <th width="20%" class="text-right">{{ trans('double-entry::general.credit') }}</th>
                        @stack('price_th_end')
                    </tr>
                    </thead>
                    <tbody>
                    @php $item_row = 0; @endphp
                    @if(old('item'))
                        @foreach(old('item') as $old_item)
                            @php $item = (object) $old_item; @endphp
                            @include('double-entry::journal-entry.item')
                            @php $item_row++; @endphp
                        @endforeach
                    @else
                        @include('double-entry::journal-entry.item')
                    @endif
                    @php $item_row++; @endphp
                    @stack('add_item_td_start')
                    <tr id="addItem">
                        <td class="text-center"><button type="button" id="button-add-item" data-toggle="tooltip" title="{{ trans('general.add') }}" class="btn btn-xs btn-primary" data-original-title="{{ trans('general.add') }}"><i class="fa fa-plus"></i></button></td>
                        <td class="text-right" colspan="3"></td>
                    </tr>
                    @stack('add_item_td_end')
                    @stack('sub_total_td_start')
                    <tr>
                        <td class="text-right" colspan="2"><strong>{{ trans('invoices.sub_total') }}</strong></td>
                        <td class="text-right"><span id="debit-sub-total">0</span></td>
                        <td class="text-right"><span id="credit-sub-total">0</span></td>
                    </tr>
                    @stack('sub_total_td_end')
                    @stack('grand_total_td_start')
                    <tr>
                        <td class="text-right" colspan="2"><strong>{{ trans('invoices.total') }}</strong></td>
                        <td class="text-right"><span id="debit-grand-total">0</span></td>
                        <td class="text-right"><span id="credit-grand-total">0</span></td>
                    </tr>
                    @stack('grand_total_td_end')
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        {{ Form::saveButtons('double-entry/journal-entry') }}
    </div>
    <!-- /.box-footer -->

    {{ Form::hidden('currency_code', $currency->code, ['id' => 'currency_code']) }}
    {!! Form::close() !!}
</div>
@endsection

@push('js')
<script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/locales/bootstrap-datepicker.' . language()->getShortCode() . '.js') }}"></script>
@endpush

@push('css')
<link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/datepicker3.css') }}">
@endpush

@push('scripts')
<script type="text/javascript">
    var item_row = '{{ $item_row }}';

    function totalItem() {
        $('.box-footer .btn-success').prop('disabled', true);

        $.ajax({
            url: '{{ url("double-entry/journal-entry/totalItem") }}',
            type: 'POST',
            dataType: 'JSON',
            data: $('#items input[type=\'text\'], #items input[type=\'number\'], #items input[type=\'hidden\'], #items textarea, #items select, #currency_code'),
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(data) {
                if (data) {
                    $('#debit-sub-total').html(data.debit_sub_total);
                    $('#credit-sub-total').html(data.credit_sub_total);

                    $('#debit-grand-total').html(data.debit_grand_total);
                    $('#credit-grand-total').html(data.credit_grand_total);

                    if (data.debit_grand_total_raw == data.credit_grand_total_raw) {
                        $('.box-footer .btn-success').prop('disabled', false);

                        $('#debit-grand-total').parent().css('background-color', '#d0e9c6');
                        $('#credit-grand-total').parent().css('background-color', '#d0e9c6');
                    } else if (data.debit_grand_total_raw > data.credit_grand_total_raw) {
                        $('#debit-grand-total').parent().css('background-color', '#d0e9c6');
                        $('#credit-grand-total').parent().css('background-color', '#f2dede');
                    } else if (data.debit_grand_total_raw < data.credit_grand_total_raw) {
                        $('#debit-grand-total').parent().css('background-color', '#f2dede');
                        $('#credit-grand-total').parent().css('background-color', '#d0e9c6');
                    }
                }
            }
        });
    }

    $(document).on('click', '#button-add-item', function (e) {
        $.ajax({
            url: '{{ url("double-entry/journal-entry/addItem") }}',
            type: 'GET',
            dataType: 'JSON',
            data: {item_row: item_row},
            success: function(json) {
                if (json['success']) {
                    $('#items tbody #addItem').before(json['html']);

                    $('[data-toggle="tooltip"]').tooltip('hide');

                    var currency = json['data']['currency'];

                    $("#item-debit-" + item_row).maskMoney({
                        thousands : currency.thousands_separator,
                        decimal : currency.decimal_mark,
                        precision : currency.precision,
                        allowZero : true,
                        prefix : (currency.symbol_first) ? currency.symbol : '',
                        suffix : (currency.symbol_first) ? '' : currency.symbol
                    });

                    $("#item-debit-" + item_row).trigger('focusout');

                    $("#item-credit-" + item_row).maskMoney({
                        thousands : currency.thousands_separator,
                        decimal : currency.decimal_mark,
                        precision : currency.precision,
                        allowZero : true,
                        prefix : (currency.symbol_first) ? currency.symbol : '',
                        suffix : (currency.symbol_first) ? '' : currency.symbol
                    });

                    $("#item-credit-" + item_row).trigger('focusout');

                    $(".input-account").select2({
                        placeholder: {
                            id: '-1', // the value of the option
                            text: "{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
                        }
                    });

                    item_row++;
                }
            }
        });
    });

    $(document).on('change', '.input-price', function(){
        id = $(this).attr('id');

        ids = id.split('-');

        amount = $(this).maskMoney('unmasked')[0];

        $(this).parent().removeClass('has-error');
        $(this).parent().find('.help-block').remove();

        if (ids[1] == 'debit') {
            $('#item-credit-' + ids[2]).prop('disabled', true);

            $('#item-credit-' + ids[2]).parent().removeClass('has-error');
            $('#item-credit-' + ids[2]).parent().find('.help-block').remove();
        } else {
            $('#item-debit-' + ids[2]).prop('disabled', true);

            $('#item-debit-' + ids[2]).parent().removeClass('has-error');
            $('#item-debit-' + ids[2]).parent().find('.help-block').remove();
        }

        if (amount == 0) {
            $('#item-debit-' + ids[2]).prop('disabled', false);
            $('#item-credit-' + ids[2]).prop('disabled', false);
        }
    });

    var focus = false;

    $(document).on('focusin', '#items .input-price', function(){
        focus = true;
    });

    $(document).on('blur', '#items .input-price', function(){
        if (focus) {
            totalItem();

            focus = false;
        }
    });

    $(document).ready(function(){
        $('.box-footer .btn-success').prop('disabled', true);

        @if(old('item'))
        $('.input-price').each(function(){
            id = $(this).attr('id');

            ids = id.split('-');

            amount_1 = $(this).maskMoney('unmasked')[0];

            if (ids[1] == 'debit') {
                amount_2 = $('#item-credit-' + ids[2]).maskMoney('unmasked')[0];
            } else {
                amount_2 = $('#item-debit-' + ids[2]).maskMoney('unmasked')[0];
            }

            if (amount_1 == 0 && amount_2 > 0) {
                $(this).prop('disabled', true);
            }

            if (amount_1 > 0 && amount_2 == 0) {
                if (ids[1] == 'debit') {
                    amount_2 = $('#item-credit-' + ids[2]).prop('disabled', true);
                } else {
                    amount_2 = $('#item-debit-' + ids[2]).prop('disabled', true);
                }
            }
        });
        @endif

        $(".input-price").maskMoney({
            thousands : '{{ $currency->thousands_separator }}',
            decimal : '{{ $currency->decimal_mark }}',
            precision : {{ $currency->precision }},
            allowZero : true,
            @if($currency->symbol_first)
            prefix : '{{ $currency->symbol }}'
            @else
            suffix : '{{ $currency->symbol }}'
            @endif
        });

        $('.input-price').trigger('focusout');

        $(".input-account").select2({
            placeholder: {
                id: '-1', // the value of the option
                text: "{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
            }
        });

        //Date picker
        $('#paid_at').datepicker({
            format: 'yyyy-mm-dd',
            todayBtn: 'linked',
            autoclose: true,
            language: '{{ language()->getShortCode() }}'
        });

        $("#debit_account_id").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
        });

        $("#credit_account_id").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
        });
    });
</script>
@endpush
