<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExcavatorRentRegistrationRequest extends FormRequest
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
            'excavator_id'  =>  [
                                    'required',
                                    'exists:excavators,id',
                                ],
            'account_id'    =>  [
                                    'required',
                                    'exists:accounts,id',
                                ],
            'site_id'       =>  [
                                    'required',
                                    'exists:sites,id',
                                ],
            'from_date'     =>  [
                                    'required',
                                    'date_format:d-m-Y',
                                    'before:tomorrow',
                                    'before_or_equal:to_date'
                                ],
            'to_date'       =>  [
                                    'nullable',
                                    'date_format:d-m-Y',
                                    'before:tomorrow',
                                    'after_or_equal:from_date'
                                ],
            'description'   =>  [
                                    'nullable',
                                    'max:200',
                                ],
            'total_rent'    =>  [
                                    'required',
                                    'numeric',
                                    'min:10',
                                    'max:999999',
                                ],
        ];
    }
}
