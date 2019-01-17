<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExcavatorReadingRegistrationRequest extends FormRequest
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
            'excavator_id'          =>  [
                                            'required',
                                            'exists:excavators,id',
                                        ],
            'reading_date'          =>  [
                                            'required',
                                            'date_format:d-m-Y',
                                            'before_or_equal:today',
                                        ],
            'customer_account_id'   =>  [
                                            'required',
                                            'exists:accounts,id',
                                        ],
            'site_id'               =>  [
                                            'required',
                                            'exists:sites,id',
                                        ],
            'employee_id'           =>  [
                                            'required',
                                            'exists:employees,id',
                                        ],
            'description'           =>  [
                                            'nullable',
                                            'max:200',
                                        ],
            'bucket_hour'           =>  [
                                            'required',
                                            'numeric',
                                            'min:0',
                                            'max:99',
                                        ],
            'bucket_rate'           =>  [
                                            'required',
                                            'numeric',
                                            'min:0',
                                            'max:9999',
                                        ],
            'breaker_hour'          =>  [
                                            'required',
                                            'numeric',
                                            'min:0',
                                            'max:99',
                                        ],
            'breaker_rate'          =>  [
                                            'required',
                                            'numeric',
                                            'min:0',
                                            'max:9999',
                                        ],
            'total_rent'            => [
                                            'required',
                                            'numeric',
                                            'min:1',
                                            'max:99999',
                                        ],
        ];
    }
}
