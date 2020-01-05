@extends('layouts.admin')

@section('title', trans_choice('general.settings', 2))

@section('content')
    <div class="row">
        {!! Form::open(['url' => 'double-entry/settings', 'files' => true, 'role' => 'form']) !!}

        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('double-entry::general.default_type', ['type' => trans('double-entry::general.chart_of_accounts')]) }}</h3>
                </div>
                <div class="box-body">
                    {{ Form::selectGroup('accounts_receivable', trans('double-entry::general.accounts.receivable'), 'book', $accounts, old('accounts_receivable', setting('double-entry.accounts_receivable'))) }}

                    {{ Form::selectGroup('accounts_payable', trans('double-entry::general.accounts.payable'), 'book', $accounts, old('accounts_payable', setting('double-entry.accounts_payable'))) }}

                    {{ Form::selectGroup('accounts_sales', trans('double-entry::general.accounts.sales'), 'book', $accounts, old('accounts_sales', setting('double-entry.accounts_sales'))) }}

                    {{ Form::selectGroup('accounts_expenses', trans('double-entry::general.accounts.expenses'), 'book', $accounts, old('accounts_expenses', setting('double-entry.accounts_expenses'))) }}
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('double-entry::general.default_type', ['type' => trans_choice('general.types', 2)]) }}</h3>
                </div>
                <div class="box-body">
                    {{ Form::selectGroup('types_bank', trans('double-entry::general.bank_cash'), 'bars', $types, old('types_bank', setting('double-entry.types_bank', 6))) }}

                    {{ Form::selectGroup('types_tax', trans_choice('general.taxes', 1), 'bars', $types, old('types_tax', setting('double-entry.types_tax', 17))) }}
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-footer">
                    <div class="col-md-12">
                        <div class="form-group no-margin">
                            {!! Form::button('<span class="fa fa-save"></span> &nbsp;' . trans('general.save'), ['type' => 'submit', 'class' => 'btn btn-success  button-submit', 'data-loading-text' => trans('general.loading')]) !!}
                            <a href="{{ url('/') }}" class="btn btn-default"><span class="fa fa-times-circle"></span> &nbsp;{{ trans('general.cancel') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
@endsection

@push('scripts')
<script type="text/javascript">

    $(document).ready(function(){
        $("#accounts_receivable").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
        });

        $("#accounts_payable").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
        });

        $("#accounts_sales").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
        });

        $("#accounts_expenses").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
        });

        $("#types_bank").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.types', 1)]) }}"
        });

        $("#types_tax").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.types', 1)]) }}"
        });
    });
</script>
@endpush
