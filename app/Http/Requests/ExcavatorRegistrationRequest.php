<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExcavatorRegistrationRequest extends FormRequest
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
            'name'          =>  [
                                    'required',
                                    'string',
                                    'max:100',
                                    'min:3',
                                    Rule::unique('excavators')->ignore($this->excavator),
                                ],
            'description'   =>  [
                                    'nullable',
                                    'max:200',
                                ],
            'maker'         =>  [
                                    'required',
                                    'max:100',
                                    'min:2',
                                ],
            'capacity'      =>  [
                                    'required',
                                    'numeric',
                                    'min:1',
                                    'max:9999',
                                ],
            'bucket_rate'   =>  [
                                    'required',
                                    'numeric',
                                    'min:0',
                                    'max:9999',
                                ],
            'breaker_rate'  =>  [
                                    'required',
                                    'numeric',
                                    'min:0',
                                    'max:9999',
                                ]
        ];
    }
}
