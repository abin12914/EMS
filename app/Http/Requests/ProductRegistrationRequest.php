<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRegistrationRequest extends FormRequest
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
            'product_name'      =>  [
                                        'required',
                                        'max:100',
                                        Rule::unique('products', 'name')->ignore($this->product),
                                    ],
            'uom_code'          =>  [
                                        'required',
                                        'max:15',
                                        'alpha',
                                    ],
            'description'       =>  [
                                        'required',
                                        'max:200',
                                    ],
            'malayalam_name'    =>  [
                                        'nullable',
                                        'string',
                                        'min:2',
                                        'max:100',
                                        Rule::unique('products', 'malayalam_name')->ignore($this->product),
                                    ],
            'product_code'      =>  [
                                        'required',
                                        'numeric',
                                        'min:1',
                                        'max:9999',
                                        Rule::unique('products', 'product_code')->ignore($this->product),
                                    ],
            'weighment_wastage' =>  [
                                        'nullable',
                                        'numeric'
                                    ],
        ];
    }
}
