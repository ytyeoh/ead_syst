<tr id="item-row-{{ $item_row }}">
    @stack('actions_td_start')
    <td class="text-center" style="vertical-align: middle;">
        @stack('actions_button_start')
        <button type="button" onclick="$(this).tooltip('destroy'); $('#item-row-{{ $item_row }}').remove(); totalItem();" data-toggle="tooltip" title="{{ trans('general.delete') }}" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
        @stack('actions_button_end')
    </td>
    @stack('actions_td_end')
    @stack('account_id_td_start')
    <td{!! $errors->has('item.' . $item_row . '.account_id') ? ' class="has-error"' : ''  !!}>
        @stack('account_id_input_start')
        {!! Form::select('item[' . $item_row . '][account_id]', $accounts, empty($item->account_id) ? null : $item->account_id, ['id'=> 'item-account-id-'. $item_row, 'class' => 'form-control account-select2 input-account', 'placeholder' => trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)])]) !!}
        {!! $errors->first('item.' . $item_row . '.account_id', '<p class="help-block">:message</p>') !!}
        @stack('account_id_input_end')
    </td>
    @stack('account_td_end')
    @stack('debit_td_start')
    <td{!! $errors->has('item.' . $item_row . '.debit') ? ' class="has-error"' : '' !!}>
        @stack('debit_input_start')
        <input value="{{ empty($item->debit) ? '' : $item->debit }}" class="form-control text-right input-price" required="required" name="item[{{ $item_row }}][debit]" type="text" id="item-debit-{{ $item_row }}">
        {!! $errors->first('item.' . $item_row . '.debit', '<p class="help-block">:message</p>') !!}
        @stack('debit_input_end')
    </td>
    @stack('debit_td_end')
    @stack('credit_td_start')
    <td{!! $errors->has('item.' . $item_row . '.credit') ? ' class="has-error"' : '' !!}>
        @stack('credit_input_start')
        <input value="{{ empty($item->credit) ? '' : $item->credit }}" class="form-control text-right input-price" required="required" name="item[{{ $item_row }}][credit]" type="text" id="item-credit-{{ $item_row }}">
        {!! $errors->first('item.' . $item_row . '.credit', '<p class="help-block">:message</p>') !!}
        @stack('credit_input_end')
    </td>
    @stack('credit_td_end')
</tr>
