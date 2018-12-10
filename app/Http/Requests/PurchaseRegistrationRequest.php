<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Account;
use App\Models\Product;

class PurchaseRegistrationRequest extends FormRequest
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
            'purchase_date'             =>  [
                                                'required',
                                                'date_format:d-m-Y',
                                            ],
            'supplier_account_id'       =>  [
                                                'required',
                                                Rule::in(array_merge(['-1'], Account::pluck('id')->toArray())),
                                            ],
            'supplier_name'             =>  [
                                                'required',
                                                'string',
                                                'min:3',
                                                'max:100',
                                            ],
            'supplier_phone'            =>  [
                                                'required_if:supplier_account_id,-1',
                                                'nullable',
                                                'string',
                                                'min:10',
                                                'max:13',
                                            ],
            'description'               =>  [
                                                'nullable',
                                                'string',
                                                'min:3',
                                                'max:200',
                                            ],
            'product_id'                =>  [
                                                'required',
                                                'array',
                                            ],
            'product_id.*'              =>  [
                                                'nullable',
                                                Rule::in(Product::pluck('id')->toArray()),
                                                'distinct'
                                            ],
            'gross_quantity.*'          =>  [
                                                'nullable',
                                                'numeric',
                                                'min:0.1',
                                                'max:9999',
                                            ],
            'product_number.*'          =>  [
                                                'nullable',
                                                'numeric',
                                                'min:0.1',
                                                'max:9999',
                                            ],
            'unit_wastage.*'            =>  [
                                                'nullable',
                                                'numeric',
                                                'min:0.1',
                                                'max:9999',
                                            ],
            'total_wastage.*'           =>  [
                                                'nullable',
                                                'numeric',
                                                'min:0.1',
                                                'max:9999',
                                            ],
            'net_quantity'         =>  [    
                                                'required',
                                                'array',
                                            ],
            'net_quantity.*'       =>  [    
                                                'required',
                                                'numeric',
                                                'min:1',
                                                'max:99999'
                                            ],
            'purchase_rate'             =>  [
                                                'required',
                                                'array',
                                            ],
            'purchase_rate.*'           =>  [
                                                'required',
                                                'numeric',
                                                'min:0.1',
                                                'max:99999'
                                            ],
            'sub_bill'                  =>  [
                                                'required',
                                                'array',
                                            ],
            'sub_bill.*'                =>  [
                                                'required',
                                                'numeric',
                                                'min:0.1',
                                                'max:99999'
                                            ],
            'total_amount'              =>  [
                                                'required',
                                                'numeric',
                                                'max:99999',
                                                'min:1'
                                            ],
            'discount'                  =>  [
                                                'required',
                                                'numeric',
                                                'max:9999',
                                                'min:0'
                                            ],
            'total_bill'                =>  [
                                                'required',
                                                'numeric',
                                                'max:99999',
                                                'min:1'
                                            ],
            'cash_paid'                 =>  [
                                                'required',
                                                'min:0',
                                                'max:999999',
                                            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->checkCalculations()) {
                $validator->errors()->add('calculations', 'Something went wrong with the calculations! Please try again after reloading the page');
            }
        });
    }

    public function checkCalculations() {
        $totalAmount        = 0;
        $productEmptyCount  = 0;

        foreach ($this->request->get("product_id") as $index => $productId) {
            if(empty($productId)) {
                $productEmptyCount ++;
                continue;
            }

            if(empty($this->request->get('net_quantity')[$index]) || empty($this->request->get('purchase_rate')[$index]) || empty($this->request->get('sub_bill')[$index])) {
                return false;
            }

            //weighment deduction
            if(!empty($this->request->get('gross_quantity')[$index]) && !empty($this->request->get('product_number')[$index]) && !empty($this->request->get('unit_wastage')[$index]) && !empty($this->request->get('total_wastage')[$index])) {
                    $totalWastage = $this->request->get('product_number')[$index] * $this->request->get('unit_wastage')[$index];
                    if(($this->request->get('gross_quantity')[$index] - $totalWastage) != $this->request->get('net_quantity')[$index]) {
                        return false;
                    }
            } elseif(!empty($this->request->get('gross_quantity')[$index]) || !empty($this->request->get('product_number')[$index]) || !empty($this->request->get('unit_wastage')[$index]) || !empty($this->request->get('total_wastage')[$index])) {
                return false;
            }
            
            $subTotal = $this->request->get('net_quantity')[$index] * $this->request->get('purchase_rate')[$index];
            
            if($subTotal != $this->request->get('sub_bill')[$index]) {
                return false;
            }
            $totalAmount = $totalAmount + $subTotal;
        }

        if($productEmptyCount > 1) {
            return false;
        }

        $billTotal   = $this->request->get("total_amount");
        $discount    = $this->request->get("discount");
        $totalBill   = $this->request->get("total_bill");
        $oldBalance  = $this->request->get("old_balance");
        $billPlusOb  = $this->request->get("bill_plus_ob_amount");
        $cashPaid    = $this->request->get("cash_paid");
        $outStanding = $this->request->get('outstanding_amount');

        if(($billTotal != $totalAmount) || (($billTotal - $discount) != $totalBill) || (($oldBalance - $totalBill) != $billPlusOb) || (($billPlusOb + $cashPaid) != $outStanding)) {
            return false;
        }
        
        return true;
    }
}
