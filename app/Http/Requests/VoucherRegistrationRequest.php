<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Account;

class VoucherRegistrationRequest extends FormRequest
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
            'transaction_type'  =>  [
                                        'required',
                                        Rule::in([1, 2]),
                                    ],
            'account_id'        =>  [
                                        'required',
                                        'exists:accounts,id',
                                    ],
            'transaction_date'  =>  [
                                        'required',
                                        'date_format:d-m-Y',
                                    ],
            'description'       =>  [
                                        'required',
                                        'min:4',
                                        'max:200',
                                    ],
            'amount'            =>  [
                                        'required',
                                        'numeric',
                                        'min:10',
                                        'max:999999',
                                    ],
        ];
    }
}
