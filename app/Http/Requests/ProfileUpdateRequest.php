<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Auth;

class ProfileUpdateRequest extends FormRequest
{
    public $id=null;

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
        $this->id = Auth::id();
        return [
            'username'          =>  [
                                        'required',
                                        'min:4',
                                        'max:100',
                                        Rule::unique('users')->ignore($this->id),
                                    ],
            'name'              =>  [
                                        'required',
                                        'min:4',
                                        'max:100',
                                    ],
            'email'             =>  [
                                        'required',
                                        'min:5',
                                        'email',
                                        Rule::unique('users')->ignore($this->id),
                                    ],
            'currentPassword'   =>  [
                                        'required',
                                    ],
            'password'          =>  [
                                        'nullable',
                                        'min:6',
                                        'max:16',
                                        'confirmed',
                                    ],
        ];
    }
}
