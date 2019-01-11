<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\VoucherRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\AccountRepository;
use App\Http\Requests\VoucherRegistrationRequest;
use App\Http\Requests\VoucherFilterRequest;
use \Carbon\Carbon;
use Auth;
use DB;
use Exception;
use App\Exceptions\AppCustomException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VoucherController extends Controller
{
    protected $voucherRepo;
    public $errorHead = null;

    public function __construct(VoucherRepository $voucherRepo)
    {
        $this->voucherRepo  = $voucherRepo;
        $this->errorHead    = config('settings.controller_code.VoucherController');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(VoucherFilterRequest $request)
    {
        $noOfRecordsPerPage = $request->get('no_of_records') ?? config('settings.no_of_record_per_page');

        $fromDate = !empty($request->get('from_date')) ? Carbon::createFromFormat('d-m-Y', $request->get('from_date'))->format('Y-m-d') : "";
        $toDate   = !empty($request->get('to_date')) ? Carbon::createFromFormat('d-m-Y', $request->get('to_date'))->format('Y-m-d') : "";
        
        $dateWhere = [
            'from_date' =>  [
                'paramName'     => 'transaction_date',
                'paramOperator' => '>=',
                'paramValue'    => $fromDate,
            ],
            'to_date'   =>  [
                'paramName'     => 'transaction_date',
                'paramOperator' => '<=',
                'paramValue'    => $toDate,
            ],
        ];

        $voucherTypeWhere = [
            'transaction_type'  =>  [
                'paramName'     => 'transaction_type',
                'paramOperator' => '=',
                'paramValue'    => $request->get('transaction_type'),
            ]
        ];

        $debitVoucherTypeWhere = [
            'transaction_type'  =>  [
                'paramName'     => 'transaction_type',
                'paramOperator' => '=',
                'paramValue'    => 1,
            ]
        ];

        $creditVoucherTypeWhere = [
            'transaction_type'  =>  [
                'paramName'     => 'transaction_type',
                'paramOperator' => '=',
                'paramValue'    => 2,
            ]
        ];
        
        $whereParams = $dateWhere;
        if(!empty($request->get('transaction_type')) && count($request->get('transaction_type')) <= 1) {
            $whereParams = array_merge($whereParams, $voucherTypeWhere);
        }

        $relationalOrParams = [
            'voucher_account_id'    =>  [
                'relation' => 'transaction',
                'params'   => [
                    'debit_account_id' => [
                        'paramName'     => 'debit_account_id',
                        'paramOperator' => '=',
                        'paramValue'    => $request->get('voucher_account_id'),
                    ],
                    'credit_account_id' => [
                        'paramName'     => 'credit_account_id',
                        'paramOperator' => '=',
                        'paramValue'    => $request->get('voucher_account_id'),
                    ],
                ]
            ]
        ];

        //getVouchers($whereParams=[],$orWhereParams=[],$relationalOrParams=[],$orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],$aggregates=['key' => null, 'value' => null],$withParams=[],$activeFlag=true)
        $vouchers = $this->voucherRepo->getVouchers($whereParams, [], $relationalOrParams, ['by' => 'id', 'order' => 'asc', 'num' => $noOfRecordsPerPage], [], [], true);
        $totalDebitAmount = $this->voucherRepo->getVouchers((array_merge($dateWhere, $debitVoucherTypeWhere)), [], $relationalOrParams, ['by' => 'id', 'order' => 'asc', 'num' => $noOfRecordsPerPage], ['key' => 'sum', 'value' => 'amount'], [], true);
        $totalCreditAmount = $this->voucherRepo->getVouchers((array_merge($dateWhere, $creditVoucherTypeWhere)), [], $relationalOrParams, ['by' => 'id', 'order' => 'asc', 'num' => $noOfRecordsPerPage], ['key' => 'sum', 'value' => 'amount'], [], true);

        //params passing for auto selection
        $params['from_date']['paramValue']          = $request->get('from_date');
        $params['to_date']['paramValue']            = $request->get('to_date');
        $params['transaction_type']['paramValue']   = $request->get('transaction_type');
        $params['voucher_account_id']['paramValue'] = $request->get('voucher_account_id');

        return view('vouchers.list', [
            'vouchers'          => $vouchers,
            'params'            => $params,
            'noOfRecords'       => $noOfRecordsPerPage,
            'totalDebitAmount'  => $totalDebitAmount,
            'totalCreditAmount' => $totalCreditAmount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('vouchers.register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        VoucherRegistrationRequest $request,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo,
        $id=null
    ) {
        $errorCode          = 0;
        $voucher            = null;

        $cashAccountId      = config('constants.accountConstants.Cash.id');
        $transactionDate    = Carbon::createFromFormat('d-m-Y', $request->get('transaction_date'))->format('Y-m-d');
        $voucherType        = $request->get('transaction_type');
        $voucherTitle       = $voucherType == 1 ? "Reciept" : "Payemnt";
        $accountId          = $request->get('voucher_account_id');
        $description        = $request->get('description');
        $amount             = $request->get('amount');

        //wrappin db transactions
        DB::beginTransaction();
        try {
            $user = Auth::user();

            if(!empty($id)) {
                $voucher = $this->voucherRepo->getVoucher($id);
            }
            //confirming account exist-ency.
            $cashAccount = $accountRepo->getAccount($cashAccountId, [], false);
            $account     = $accountRepo->getAccount($accountId, [], false);

            if($voucherType == 1) {
                //Receipt : Debit cash account - Credit giver account
                $debitAccountId     = $cashAccountId;
                $creditAccountId    = $accountId;
                $particulars        = $description. "[Cash received from ". $account->account_name. "]";
            } else {
                //Payment : Debit receiver account - Credit cash account
                $debitAccountId     = $accountId;
                $creditAccountId    = $cashAccountId;
                $particulars        = $description. "[Cash paid to ". $account->account_name. "]";
            }

            //save voucher transaction to table
            $transactionResponse   = $transactionRepo->saveTransaction([
                'debit_account_id'  => $debitAccountId,
                'credit_account_id' => $creditAccountId,
                'amount'            => $amount ,
                'transaction_date'  => $transactionDate,
                'particulars'       => $voucherTitle. '-'. $particulars,
                'status'            => 1,
                'company_id'        => $user->company_id,
            ], (!empty($voucher) ? $voucher->transaction_id : null));

            if(!$transactionResponse['flag']) {
                throw new AppCustomException("CustomError", $transactionResponse['errorCode']);
            }

            //save to voucher table
            $voucherResponse = $this->voucherRepo->saveVoucher([
                'transaction_id'    => $transactionResponse['transaction']->id,
                'transaction_date'  => $transactionDate,
                'transaction_type'  => $voucherType,
                'amount'            => $amount,
                'status'            => 1,
                'created_by'        => $user->id,
                'company_id'        => $user->company_id,
            ], $id);

            if(!$voucherResponse['flag']) {
                throw new AppCustomException("CustomError", $voucherResponse['errorCode']);
            }

            DB::commit();
            if(!empty($id)) {
                return [
                    'flag'  => true,
                    'id'    => $voucherResponse['voucher']
                ];
            }
            return redirect(route('voucher.show', $voucherResponse['voucher']->id))->with("message", $voucherTitle. " details saved successfully. Reference Number : ". $transactionResponse['transaction']->id)->with("alert-class", "success");
        } catch (Exception $e) {
            //roll back in case of exceptions
            DB::rollback();
dd($e);
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 1);
        }
        if(!empty($id)) {
            return [
                'flag'      => false,
                'errorCode' => $errorCode
            ];
        }
        return redirect()->back()->with("message","Failed to save the ". $voucherTitle. " details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $voucher    = [];

        try {
            $voucher = $this->voucherRepo->getVoucher($id, [], true);
        } catch (\Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 2);
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("Voucher", $errorCode);
        }

        return view('vouchers.details', [
            'voucher' => $voucher,
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
        $voucher   = [];

        try {
            $voucher = $this->voucherRepo->getVoucher($id, [], true);
        } catch (\Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 3);

            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("Voucher", $errorCode);
        }

        return view('vouchers.edit', [
            'voucher' => $voucher,
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
        VoucherRegistrationRequest $request,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo,
        $id
    ) {
        $updateResponse = $this->store($request, $transactionRepo, $accountRepo, $id);

        if($updateResponse['flag']) {
            return redirect(route('voucher.show', $updateResponse['voucher']->id))->with("message","Voucher details updated successfully. Updated Record Number : ". $updateResponse['voucher']->id)->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to update the voucher details. Error Code : ". $this->errorHead. "/". $updateResponse['errorCode'])->with("alert-class", "error");
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
            $deleteResponse = $this->voucherRepo->deleteVoucher($id, false);
            
            if(!$deleteResponse['flag']) {
                throw new AppCustomException("CustomError", $deleteResponse['errorCode']);
            }
            
            DB::commit();
            return redirect(route('voucher.index'))->with("message","Voucher details deleted successfully.")->with("alert-class", "success");
        } catch (Exception $e) {
            //roll back in case of exceptions
            DB::rollback();

            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 4);
        }
        
        return redirect()->back()->with("message","Failed to delete the voucher details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
    }
}
