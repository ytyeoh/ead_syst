<?php

namespace Modules\DoubleEntry\Http\Requests;

use App\Http\Requests\Request;

class Setting extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'accounts_receivable' => 'required|integer',
            'accounts_payable' => 'required|integer',
            'accounts_sales' => 'required|integer',
            'accounts_expenses' => 'required|integer',
        ];
    }
}
