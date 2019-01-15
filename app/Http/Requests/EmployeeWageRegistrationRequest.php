<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeWageRegistrationRequest extends FormRequest
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
            'employee_id'   =>  [
                                    'required',
                                    'exists:employees,id',
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
                                    'required',
                                    'min:4',
                                    'max:200',
                                ],
            'wage_amount'   =>  [
                                    'required',
                                    'numeric',
                                    'min:10',
                                    'max:99999',
                                ],
        ];
    }
}
