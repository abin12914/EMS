<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExcavatorReadingFilterRequest extends FormRequest
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
            'from_date'     =>  [
                                    'nullable',
                                    'date_format:d-m-Y'
                                ],
            'to_date'       =>  [
                                    'nullable',
                                    'date_format:d-m-Y',
                                    'after_or_equal:from_date'
                                ],
            'excavator_id'  =>  [
                                    'nullable',
                                    'exists:excavators,id'
                                ],
            'account_id'    =>  [
                                    'nullable',
                                    'exists:accounts,id'
                                ],
            'site_id'       =>  [
                                    'nullable',
                                    'exists:sites,id'
                                ],
            'employee_id'   =>  [
                                    'nullable',
                                    'exists:employees,id'
                                ],
            'no_of_records' =>  [
                                    'nullable',
                                    'min:2',
                                    'max:100',
                                    'integer',
                                ],
            'page'          =>  [
                                    'nullable',
                                    'integer',
                                ],
        ];
    }
}
