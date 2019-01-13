<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Repositories\EmployeeRepository;

class EmployeeRegistrationRequest extends FormRequest
{
    public $accountId = null, $employeeRepo =null;

    public function __construct(EmployeeRepository $employeeRepo)
    {
        $this->employeeRepo = $employeeRepo;
    }

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
        if(!empty($this->id)) {
            $employee = $this->employeeRepo->getEmployee($this->id, ['account'], false);

            if(!empty($employee) && !empty($employee->id)) {
                $this->accountId = $employee->account_id;
            }
        }

        return [
            'name'              =>  [
                                        'required',
                                        'min:3',
                                        'max:100',
                                    ],
            'phone'             =>  [
                                        'required',
                                        'numeric',
                                        'digits_between:10,13',
                                        Rule::unique('accounts')->ignore($this->accountId),
                                    ],
            'address'           =>  [
                                        'nullable',
                                        'max:200',
                                    ],
            'wage_type'         =>  [
                                        'required',
                                        Rule::in(array_keys(config('constants.employeeWageTypes'))),
                                    ],
            'wage'              =>  [
                                        'required',
                                        'numeric',
                                        'min:0',
                                        'max:99999',
                                    ],
            'account_name'      =>  [
                                        'required',
                                        'max:100',
                                        Rule::unique('accounts')->ignore($this->accountId),
                                    ],
            'financial_status'  =>  [
                                        'required',
                                        Rule::in([0, 1, 2])
                                    ],
            'opening_balance'   =>  [
                                        'required',
                                        'numeric',
                                        'min:0',
                                        'max:999999'
                                    ]
        ];
    }
}
