<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRegistrationRequest extends FormRequest
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
            'transaction_date'  =>  [
                                        'required',
                                        'date_format:d-m-Y',
                                    ],
            'account_id'        =>  [
                                        'required',
                                        'exists:accounts,id',
                                    ],
            'excavator_id'      =>  [
                                        'required',
                                        'exists:excavators,id',
                                    ],
            'service_id'        =>  [
                                        'required',
                                        'exists:services,id',
                                    ],
            'description'       =>  [
                                        'required',
                                        'min:5',
                                        'max:200',
                                    ],
            'bill_amount'       =>  [
                                        'required',
                                        'numeric',
                                        'min:1',
                                        'max:999999',
                                    ],
        ];
    }
}
