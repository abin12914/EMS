<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\SaleRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\AccountRepository;
use App\Repositories\VoucherRepository;
use App\Http\Requests\SaleRegistrationRequest;
use App\Http\Requests\SaleFilterRequest;
use Carbon\Carbon;
use DB;
use Exception;
use App\Exceptions\AppCustomException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SaleController extends Controller
{
    protected $saleRepo;
    public $errorHead = null, $noOfRecordsPerPage = null;

    public function __construct(SaleRepository $saleRepo)
    {
        $this->saleRepo             = $saleRepo;
        $this->noOfRecordsPerPage   = config('settings.no_of_record_per_page');
        $this->errorHead            = config('settings.controller_code.SaleController');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SaleFilterRequest $request)
    {
        $fromDate       = !empty($request->get('from_date')) ? Carbon::createFromFormat('d-m-Y', $request->get('from_date'))->format('Y-m-d') : "";
        $toDate         = !empty($request->get('to_date')) ? Carbon::createFromFormat('d-m-Y', $request->get('to_date'))->format('Y-m-d') : "";
        $noOfRecords    = !empty($request->get('no_of_records')) ? $request->get('no_of_records') : $this->noOfRecordsPerPage;

        $params = [
            'from_date'    =>  [
                'paramName'     => 'date',
                'paramOperator' => '>=',
                'paramValue'    => $fromDate,
            ],
            'to_date'   =>  [
                'paramName'     => 'date',
                'paramOperator' => '<=',
                'paramValue'    => $toDate,
            ],
        ];

        $relationalParams = [
            'customer_account_id'   =>  [
                'relation'      => 'transaction',
                'paramName'     => 'debit_account_id',
                'paramValue'    => $request->get('customer_account_id'),
            ]
        ];

        $sales          = $this->saleRepo->getSales($params, $relationalParams, $noOfRecords);
        $totalAmount    = $this->saleRepo->getSales($params, $relationalParams, null)->sum('total_amount');

        //params passing for auto selection
        $params['from_date']['paramValue'] = $request->get('from_date');
        $params['to_date']['paramValue'] = $request->get('to_date');
        $params = array_merge($params, $relationalParams);

        return view('sales.list', [
            'saleRecords'   => $sales,
            'totalAmount'   => $totalAmount,
            'params'        => $params,
            'noOfRecords'   => $noOfRecords,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('sales.register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        SaleRegistrationRequest $request,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo,
        VoucherRepository $voucherRepo,
        $id=null
    ) {
        $saveFlag            = false;
        $errorCode           = 0;
        $sale                = null;
        $saleTransaction     = null;
        $voucher             = null;
        $voucherTransaction  = null;
        $voucherResponse     = null;

        //configured values
        $saleAccountId      = config('constants.accountConstants.Sale.id');
        $accountRelations   = config('constants.accountRelationTypes');
        $cashAccountId      = config('constants.accountConstants.Cash.id');

        $transactionDate    = Carbon::createFromFormat('d-m-Y', $request->get('sale_date'))->format('Y-m-d');
        $customerAccountId  = $request->get('customer_account_id');
        $products           = $request->get('product_id');
        $totalBill          = $request->get('total_bill');
        $customerName       = $request->get('customer_name');
        $customerPhone      = $request->get('customer_phone');
        $cashReceived       = $request->get('cash_received');

        //wrappin db transactions
        DB::beginTransaction();
        try {
            foreach ($products as $index => $productId) {
                if(!empty($request->get('net_quantity')[$index]) && !empty($request->get('sale_rate')[$index])) {
                    $productArray[$productId] = [
                        'net_quantity'      => $request->get('net_quantity')[$index],
                        'rate'              => $request->get('sale_rate')[$index],
                        'gross_quantity'    => $request->get('gross_quantity')[$index] ?: null,
                        'product_number'    => $request->get('product_number')[$index] ?: null,
                        'unit_wastage'      => $request->get('unit_wastage')[$index] ?: null,
                        'total_wastage'     => $request->get('total_wastage')[$index] ?: null,
                    ];
                }
            }

            //confirming sale account existency.
            $saleAccount = $accountRepo->getAccount($saleAccountId);

            //if editing
            if(!empty($id)) {
                $sale               = $this->saleRepo->getSale($id);
                $saleTransaction    = $transactionRepo->getTransaction($sale->transaction_id);
                if(!empty($sale->voucher_id)) {
                    $voucher            = $this->voucherRepo->getVoucher($sale->voucher_id);
                    $voucherTransaction = $transactionRepo->getTransaction($voucher->transaction_id);
                }
            }

            if($customerAccountId == -1) {
                //checking for exist-ency of the account
                $accounts = $accountRepo->getAccounts(['phone' => $customerPhone],null,null,false);

                if(empty($accounts) || count($accounts) == 0)
                {
                    //save quick customer account to table
                    $accountResponse = $accountRepo->saveAccount([
                        'account_name'      => $customerName. "-". $customerPhone,
                        'description'       => ("New account of". $customerName),
                        'relation'          => array_search('Customer', $accountRelations), //customer key=2
                        'financial_status'  => 0,
                        'opening_balance'   => 0,
                        'name'              => $customerName,
                        'phone'             => $customerPhone,
                        'address'           => '',
                        'image'             => null,
                        'status'            => 1,
                    ]);

                    if(!$accountResponse['flag']) {
                        throw new AppCustomException("CustomError", $accountResponse['errorCode']);
                    }
                    $customerAccountId  = $accountResponse['id'];
                    $particulars        = ("Sale to ". $customerName. "-". $customerPhone);
                    $voucherParticulars = "Cash received with the sale bill of Rs.". $totalBill. "[". $customerName . " -> Cash Acount]";
                } else {
                    $customerAccount    = $accounts->first();
                    $customerAccountId  = $customerAccount->id;
                    $particulars        = ("Sale to ". $customerAccount->account_name. "-". $customerAccount->phone);
                    $voucherParticulars = "Cash received with the sale bill of Rs.". $totalBill. "[". $customerAccount->account_name . " -> Cash Acount]";
                }
            } else {
                //accessing debit account
                $customerAccount    = $accountRepo->getAccount($customerAccountId, false);
                $particulars        = ("Sale to ". $customerAccount->account_name);
                $voucherParticulars = "Cash received with the sale bill of Rs.". $totalBill. "[". $customerAccount->account_name . " -> Cash Acount]";
            }

            //save voucher transaction if cash received from customer
            if(!empty($cashReceived) && $cashReceived >= 1) {
                $voucherTransactionResponse = $transactionRepo->saveTransaction([
                    'debit_account_id'  => $cashAccountId, // debit the cash account
                    'credit_account_id' => $customerAccountId, // credit customer account
                    'amount'            => $cashReceived ,
                    'transaction_date'  => $transactionDate,
                    'particulars'       => $voucherParticulars,
                ], $voucherTransaction);

                $voucherResponse = $voucherRepo->saveVoucher([
                    'transaction_id' => $voucherTransactionResponse['id'],
                    'date'           => $transactionDate,
                    'voucher_type'   => 1, //cash payment
                    'amount'         => $cashReceived,
                ], $voucher);

            }

            //save sale transaction to table
            $transactionResponse = $transactionRepo->saveTransaction([
                'debit_account_id'  => $customerAccountId, // debit the customer
                'credit_account_id' => $saleAccountId, // credit the sale account
                'amount'            => $totalBill ,
                'transaction_date'  => $transactionDate,
                'particulars'       => ($request->get('description'). "(". $particulars. ")"),
            ], $saleTransaction);

            if(!$transactionResponse['flag']) {
                throw new AppCustomException("CustomError", $transactionResponse['errorCode']);
            }

            //save to sale table
            $saleResponse = $this->saleRepo->saveSale([
                'transaction_id'    => $transactionResponse['id'],
                'date'              => $transactionDate,
                'voucher_id'        => (!empty($voucherResponse['id']) ? $voucherResponse['id'] : null),
                'customer_name'     => $customerName,
                'customer_phone'    => $customerPhone,
                'description'       => $request->get('description'),
                'discount'          => $request->get('discount'),
                'total_amount'      => $totalBill,
                'productsArray'     => $productArray,
            ], $sale);

            if(!$saleResponse['flag']) {
                throw new AppCustomException("CustomError", $saleResponse['errorCode']);
            }

            DB::commit();
            $saveFlag = true;
        } catch (Exception $e) {
            //roll back in case of exceptions
            DB::rollback();

            if($e->getMessage() == "CustomError") {
                $errorCode = $e->getCode();
            } else {
                $errorCode = 1;
            }
        }

        if($saveFlag) {
            return redirect(route('sale.invoice', $saleResponse['id']))->with("message","Sale details saved successfully. Reference Number : ". $transactionResponse['id'])->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to save the sale details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
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
        $sale       = [];

        try {
            $sale = $this->saleRepo->getSale($id);
        } catch (\Exception $e) {
            if($e->getMessage() == "CustomError") {
                $errorCode = $e->getCode();
            } else {
                $errorCode = 2;
            }
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("Sale", $errorCode);
        }

        return view('sales.details', [
            'sale' => $sale,
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
        return redirect()->back()->with("message","Edit disabled!")->with("alert-class", "error");

        $errorCode  = 0;
        $sale       = [];

        try {
            $sale = $this->saleRepo->getSale($id);
        } catch (\Exception $e) {
            if($e->getMessage() == "CustomError") {
                $errorCode = $e->getCode();
            } else {
                $errorCode = 3;
            }
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("Sale", $errorCode);
        }

        return view('sales.edit', [
            'sale' => $sale,
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
        SaleRegistrationRequest $request,
        $id,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo
    ) {
        $updateResponse = $this->store($request, $transactionRepo, $accountRepo, $id);

        if($updateResponse['flag']) {
            return redirect(route('sale.index', $updateResponse['id']))->with("message","Sale details updated successfully. Updated Record Number : ". $updateResponse['id'])->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to update the sale details. Error Code : ". $this->errorHead. "/". $updateResponse['errorCode'])->with("alert-class", "error");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return redirect()->back()->with("message","Deletion disabled!")->with("alert-class", "error");

        $deleteFlag = false;
        $errorCode  = 0;

        //wrapping db transactions
        DB::beginTransaction();
        try {
            $deleteResponse = $this->saleRepo->deleteSale($id);
            
            if(!$deleteResponse['flag']) {
                throw new AppCustomException("CustomError", $deleteResponse['errorCode']);
            }
            
            DB::commit();
            $deleteFlag = true;
        } catch (Exception $e) {
            //roll back in case of exceptions
            DB::rollback();

            if($e->getMessage() == "CustomError") {
                $errorCode = $e->getCode();
            } else {
                $errorCode = 5;
            }
        }

        if($deleteFlag) {
            return redirect(route('sale.index'))->with("message","Sale details deleted successfully.")->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to delete the sale details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
    }

    /**
     * Show the invoice for print.
     *
     * @return \Illuminate\Http\Response
     */
    public function invoice($id, TransactionRepository $transactionRepo)
    {
        $errorCode  = 0;
        $sale       = [];
        $oldBalance = 0;

        try {
            $sale = $this->saleRepo->getSale($id);
            if(!empty($sale->payment)) {
                $oldBal = $transactionRepo->getOldBalance($sale->transaction->debit_account_id, null, $sale->payment->transaction_id);
            } else {
                $oldBal = $transactionRepo->getOldBalance($sale->transaction->debit_account_id, null, $sale->transaction_id);
            }

            $oldBalance = $oldBal['debit'] - $oldBal['credit'];
        } catch (\Exception $e) {
            if($e->getMessage() == "CustomError") {
                $errorCode = $e->getCode();
            } else {
                $errorCode = 7;
            }
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("Sale", $errorCode);
        }

        return view('sales.invoice', [
            'sale' => $sale,
            'oldBalance'    => $oldBalance
        ]);
    }
}
