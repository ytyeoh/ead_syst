@stack('de_account_id_td_start')
<td>
    {!! Form::select('item[' . $item_row . '][de_account_id]', $de_accounts, (!empty($item) && !empty($item->id)) ? $item_accounts[$item->id] : null, ['id'=> 'item-de-account-'. $item_row, 'class' => 'form-control de-account-select2', 'placeholder' => trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)])]) !!}
</td>
@stack('de_account_id_td_end')