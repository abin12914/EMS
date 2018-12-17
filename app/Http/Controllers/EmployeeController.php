<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\EmployeeRepository;
use App\Repositories\AccountRepository;
use App\Repositories\TransactionRepository;
use App\Http\Requests\EmployeeRegistrationRequest;
use App\Http\Requests\EmployeeFilterRequest;
use \Carbon\Carbon;
use Auth;
use DB;
use Exception;
use App\Exceptions\AppCustomException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmployeeController extends Controller
{
    protected $employeeRepo;
    public $errorHead = null;

    public function __construct(EmployeeRepository $employeeRepo)
    {
        $this->employeeRepo         = $employeeRepo;
        $this->errorHead            = config('settings.controller_code.EmployeeController');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(EmployeeFilterRequest $request)
    {
        $noOfRecordsPerPage = $request->get('no_of_records') ?? config('settings.no_of_record_per_page');

        $whereParams = [
            'wage_type' => [
                'paramName'     => 'wage_type',
                'paramOperator' => '=',
                'paramValue'    => $request->get('wage_type'),
            ],
            'employee_id' => [
                'paramName'     => 'id',
                'paramOperator' => '=',
                'paramValue'    => $request->get('employee_id'),
            ]
        ];
        
        return view('employees.list', [
                'employees'         => $this->employeeRepo->getEmployees($whereParams, [], [], ['by' => 'id', 'order' => 'asc', 'num' => $noOfRecordsPerPage], $aggregates=['key' => null, 'value' => null], [], true),
                'wageTypes'   => config('constants.employeeWageTypes'),
                'params'      => $whereParams,
                'noOfRecords' => $noOfRecordsPerPage,
            ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('employees.register', [
                'wageTypes' => config('constants.employeeWageTypes'),
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        EmployeeRegistrationRequest $request,
        AccountRepository $accountRepo,
        TransactionRepository $transactionRepo,
        $id=null
    ) {
        $errorCode            = 0;
        $employee             = null;
        $employeeAccount      = null;
        $openingTransactionId = null;

        $openingBalanceAccountId    = config('constants.accountConstants.AccountOpeningBalance.id');
        $accountRelations           = config('constants.accountRelationTypes');

        $financialStatus    = $request->get('financial_status');
        $openingBalance     = $request->get('opening_balance');
        $name               = $request->get('name');

        //wrappin db transactions
        DB::beginTransaction();
        try {
            $user = Auth::user();
            //confirming opening balance existency.
            $openingBalanceAccount = $accountRepo->getAccount($openingBalanceAccountId, [], false);

            if(!empty($id)) {
                $employee = $this->employeeRepo->getEmployee($id, ['account'], false);

                if($employee->account->financial_status == 2){
                    $searchTransaction = [
                        ['paramName' => 'debit_account_id', 'paramOperator' => '=', 'paramValue' => $employee->account_id],
                        ['paramName' => 'credit_account_id', 'paramOperator' => '=', 'paramValue' => $openingBalanceAccountId],
                    ];
                } else {
                    $searchTransaction = [
                        ['paramName' => 'debit_account_id', 'paramOperator' => '=', 'paramValue' => $openingBalanceAccountId],
                        ['paramName' => 'credit_account_id', 'paramOperator' => '=', 'paramValue' => $employee->account_id],
                    ];
                }

                $openingTransactionId = $transactionRepo->getTransactions($searchTransaction, [], [], ['by' => 'id', 'order' => 'asc', 'num' => 1], [], [], null, false )->id;
            }

            //save to account table
            $accountResponse = $accountRepo->saveAccount([
                'account_name'      => $request->get('account_name'),
                'description'       => $request->get('description'),
                'type'              => array_search('Personal', (config('constants.accountTypes'))),
                'relation'          => array_search('Employees', $accountRelations), //employee //key=1
                'financial_status'  => $financialStatus,
                'opening_balance'   => $openingBalance,
                'name'              => $name,
                'phone'             => $request->get('phone'),
                'address'           => $request->get('address'),
                'status'            => 1,
                'created_by'        => $user->id,
                'company_id'        => $user->company_id,
            ], (!empty($employee) ? $employee->account_id : null));

            if(!$accountResponse['flag']) {
                throw new AppCustomException("CustomError", $accountResponse['errorCode']);
            }

            //opening balance transaction details
            if($financialStatus == 1) { //incoming [account holder gives cash to company] [Creditor]
                $debitAccountId     = $openingBalanceAccountId; //cash flow into the opening balance account
                $creditAccountId    = $accountResponse['account']->id; //newly created account id [flow out from new account]
                $particulars        = "Opening balance of ". $name . " - Debit [Creditor]";
            } else if($financialStatus == 2){ //outgoing [company gives cash to account holder] [Debitor]
                $debitAccountId     = $accountResponse['account']->id; //newly created account id [flow into new account]
                $creditAccountId    = $openingBalanceAccountId; //flow out from the opening balance account
                $particulars        = "Opening balance of ". $name . " - Credit [Debitor]";
            } else {
                $debitAccountId     = $openingBalanceAccountId;
                $creditAccountId    = $accountResponse['account']->id; //newly created account id
                $particulars        = "Opening balance of ". $name . " - None";
                $openingBalance     = 0;
            }

            //save to transaction table
            $transactionResponse = $transactionRepo->saveTransaction([
                'debit_account_id'  => $debitAccountId,
                'credit_account_id' => $creditAccountId,
                'amount'            => $openingBalance,
                'transaction_date'  => Carbon::now()->format('Y-m-d'),
                'particulars'       => $particulars,
                'status'            => 1,
                'company_id'        => $user->company_id,
            ], $openingTransactionId);

            if(!$transactionResponse['flag']) {
                throw new AppCustomException("CustomError", $transactionResponse['errorCode']);
            }

            $employeeResponse = $this->employeeRepo->saveEmployee([
                'account_id' => $accountResponse['account']->id, //newly created account id
                'wage_type'  => $request->get('wage_type'),
                'wage'       => $request->get('wage'),
                'status'     => 1,
                'created_by' => $user->id,
                'company_id' => $user->company_id,
            ], $id);

            if(!$employeeResponse['flag']) {
                throw new AppCustomException("CustomError", $employeeResponse['errorCode']);
            }

            DB::commit();
            if(!empty($id)) {
                return [
                    'flag'     => true,
                    'employee' => $employeeResponse['employee']
                ];
            }

            return redirect(route('employee.show', $employeeResponse['employee']->id))->with("message","Employee details saved successfully. Reference Number : ". $employeeResponse['employee']->id)->with("alert-class", "success");
        } catch (Exception $e) {
            //roll back in case of exceptions
            DB::rollback();

            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 1);
        }
        if(!empty($id)) {
            return [
                'flag'          => false,
                'errorCode'    => $errorCode
            ];
        }
        
        return redirect()->back()->with("message","Failed to save the employee details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $errorCode  = 0;
        $employee   = [];

        try {
            $employee = $this->employeeRepo->getEmployee($id, [], false);
        } catch (Exception $e) {
       $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 2);
        //throwing methodnotfound exception when no model is fetched
        throw new ModelNotFoundException("Employee", $errorCode);
    }
        return view('employees.details', [
                'employee'  => $employee,
                'wageTypes' => config('constants.employeeWageTypes'),
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $errorCode  = 0;
        $employee   = [];

        try {
            $employee = $this->employeeRepo->getEmployee($id);
        } catch (\Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 3);
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("Employee", $errorCode);
        }

        return view('employees.edit', [
            'employee'  => $employee,
            'wageTypes' => config('constants.employeeWageTypes'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(
        EmployeeRegistrationRequest $request,
        AccountRepository $accountRepo,
        TransactionRepository $transactionRepo,
        $id
    ) {
        $updateResponse = $this->store($request, $accountRepo, $transactionRepo, $id);

        if($updateResponse['flag']) {
            return redirect(route('employee.show', $updateResponse['employee']->id))->with("message","Employee details updated successfully. Updated Record Number : ". $updateResponse['employee']->id)->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to update the employee details. Error Code : ". $this->errorHead. "/". $updateResponse['errorCode'])->with("alert-class", "error");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return redirect()->back()->with("message", "Deletion restricted.")->with("alert-class", "error");
    }
}
