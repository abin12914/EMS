<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\EmployeeWageRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\AccountRepository;
use App\Http\Requests\EmployeeWageRegistrationRequest;
use App\Http\Requests\EmployeeWageFilterRequest;
use Carbon\Carbon;
use Auth;
use DB;
use Exception;
use App\Exceptions\AppCustomException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmployeeWageController extends Controller
{
    protected $employeeWageRepo;
    public $errorHead = null;

    public function __construct(EmployeeWageRepository $employeeWageRepo)
    {
        $this->employeeWageRepo = $employeeWageRepo;
        $this->errorHead        = config('settings.controller_code.EmployeeWageController');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(EmployeeWageFilterRequest $request)
    {
        $noOfRecordsPerPage = $request->get('no_of_records') ?? config('settings.no_of_record_per_page');
        //date format conversion
        $fromDate    = empty($request->get('from_date')) ?: Carbon::createFromFormat('d-m-Y', $request->get('from_date'))->format('Y-m-d');
        $toDate      = empty($request->get('to_date')) ?: Carbon::createFromFormat('d-m-Y', $request->get('to_date'))->format('Y-m-d');

        $whereParams = [
            'from_date' => [
                'paramName'     => 'from_date',
                'paramOperator' => '>=',
                'paramValue'    => $fromDate,
            ],
            'to_date' => [
                'paramName'     => 'to_date',
                'paramOperator' => '<=',
                'paramValue'    => $toDate,
            ],
            'employee_id' => [
                'paramName'     => 'employee_id',
                'paramOperator' => '=',
                'paramValue'    => $request->get('employee_id'),
            ],
        ];

        //params passing for auto selection
        $whereParams['from_date']['paramValue'] = $request->get('from_date');
        $whereParams['to_date']['paramValue']   = $request->get('to_date');
        
        //getEmployeeWages($whereParams=[],$orWhereParams=[],$relationalParams=[],$orderBy=['by' => 'id', 'order' => 'asc', 'num' => null], $withParams=[],$activeFlag=true)
        return view('employee-wages.list', [
            'employeeWages'     => $this->employeeWageRepo->getEmployeeWages($whereParams, [], [], ['by' => 'id', 'order' => 'asc', 'num' => $noOfRecordsPerPage], [], [], true),
            'totalEmployeeWage' => $this->employeeWageRepo->getEmployeeWages($whereParams, [], [], [], ['key' => 'sum', 'value' => 'wage'], [], true),
            'params'       => $whereParams,
            'noOfRecords'  => $noOfRecordsPerPage,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('employee-wages.register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        EmployeeWageRegistrationRequest $request,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo,
        $id=null
    ) {
        $errorCode = 0;
        $employeeWageTransactionId = null;

        $employeeWageAccountId = config('constants.accountConstants.EmployeeWage.id');
        //date format conversion
        $fromDate    = empty($request->get('from_date')) ?: Carbon::createFromFormat('d-m-Y', $request->get('from_date'))->format('Y-m-d');
        $toDate      = empty($request->get('to_date')) ? $fromDate : Carbon::createFromFormat('d-m-Y', $request->get('to_date'))->format('Y-m-d');

        //wrappin db transactions
        DB::beginTransaction();
        try {
            $user = Auth::user();

            //confirming employeeWage account exist-ency.
            $employeeWageAccount = $accountRepo->getAccount($employeeWageAccountId, [], false);
            if (!empty($id)) {
                $employeeWageTransactionId = $this->employeeWageRepo->getEmployeeWage($id, [], false)->id;
            }

            //save employeeWage transaction to table
            $transactionResponse   = $transactionRepo->saveTransaction([
                'debit_account_id'  => $employeeWageAccountId, // debit the employeeWage account
                'credit_account_id' => $request->get('employee_id'), // credit the employee
                'amount'            => $request->get('wage_amount'),
                'transaction_date'  => $toDate,
                'particulars'       => $request->get('description')."[Employee Wage]",
                'status'            => 1,
                'company_id'        => $user->company_id,
            ], $employeeWageTransactionId);

            if(!$transactionResponse['flag']) {
                throw new AppCustomException("CustomError", $transactionResponse['errorCode']);
            }

            //save to employeeWage table
            $employeeWageResponse = $this->employeeWageRepo->saveEmployeeWage([
                'employee_id'    => $request->get('employee_id'),
                'transaction_id' => $transactionResponse['transaction']->id,
                'wage_type'      => ($fromDate->diffInDays($toDate) > 8) ? 1 : 2, //1 => salary 2 => wage
                'from_date'      => $fromDate,
                'to_date'        => $toDate,
                'wage'           => $request->get('wage_amount'),
                'description'    => $request->get('description'),
                'status'         => 1,
                'created_by'     => $user->id,
                'company_id'     => $user->company_id,
            ], $id);

            if(!$employeeWageResponse['flag']) {
                throw new AppCustomException("CustomError", $employeeWageResponse['errorCode']);
            }
            if(!empty($id)) {
                return [
                    'flag'         => true,
                    'employeeWage' => $employeeWageResponse['employeeWage']
                ];
            }

            return redirect(route('employeeWage.index'))->with("message","EmployeeWage details saved successfully. Reference Number : ". $transactionResponse['transaction']->id)->with("alert-class", "success");
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
        return redirect()->back()->with("message","Failed to save the employeeWage details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
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
        $employeeWage    = [];

        try {
            $employeeWage = $this->employeeWageRepo->getEmployeeWage($id, [], false);
        } catch (\Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 2);
            
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("EmployeeWage", $errorCode);
        }

        return view('employee-wages.details', [
            'employeeWage' => $employeeWage,
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
        $employeeWage    = [];

        try {
            $employeeWage = $this->employeeWageRepo->getEmployeeWage($id, [], false);
        } catch (\Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 3);
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("EmployeeWage", $errorCode);
        }

        return view('employee-wages.edit', [
            'employeeWage' => $employeeWage,
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
        EmployeeWageRegistrationRequest $request,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo,
        $id
    ) {
        $updateResponse = $this->store($request, $transactionRepo, $accountRepo, $id);

        if($updateResponse['flag']) {
            return redirect(route('employeeWages.show', $updateResponse['employeeWage']->id))->with("message","EmployeeWages details updated successfully. Updated Record Number : ". $updateResponse['employeeWage']->id)->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to update the employeeWages details. Error Code : ". $this->errorHead. "/". $updateResponse['errorCode'])->with("alert-class", "error");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $errorCode  = 0;

        //wrapping db transactions
        DB::beginTransaction();
        try {
            $deleteResponse = $this->employeeWageRepo->deleteEmployeeWage($id, false);
            
            if(!$deleteResponse['flag']) {
                throw new AppCustomException("CustomError", $deleteResponse['errorCode']);
            }
            
            DB::commit();
            return redirect(route('employeeWage.index'))->with("message","EmployeeWage details deleted successfully.")->with("alert-class", "success");
        } catch (Exception $e) {
            //roll back in case of exceptions
            DB::rollback();

            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 4);
        }
        
        return redirect()->back()->with("message","Failed to delete the employeeWage details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
    }
}
