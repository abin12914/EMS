<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\PurchaseRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\AccountRepository;
use App\Repositories\VoucherRepository;
use App\Http\Requests\PurchaseRegistrationRequest;
use App\Http\Requests\PurchaseFilterRequest;
use Carbon\Carbon;
use DB;
use Exception;
use App\Exceptions\AppCustomException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PurchaseController extends Controller
{
    protected $purchaseRepo;
    public $errorHead = null, $noOfRecordsPerPage = null;

    public function __construct(PurchaseRepository $purchaseRepo)
    {
        $this->purchaseRepo         = $purchaseRepo;
        $this->noOfRecordsPerPage   = config('settings.no_of_record_per_page');
        $this->errorHead            = config('settings.controller_code.PurchaseController');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PurchaseFilterRequest $request)
    {
        $fromDate       = !empty($request->get('from_date')) ? Carbon::createFromFormat('d-m-Y', $request->get('from_date'))->format('Y-m-d') : "";
        $toDate         = !empty($request->get('to_date')) ? Carbon::createFromFormat('d-m-Y', $request->get('to_date'))->format('Y-m-d') : "";
        $noOfRecords    = !empty($request->get('no_of_records')) ? $request->get('no_of_records') : $this->noOfRecordsPerPage;

        $params = [
            'from_date'     =>  [
                'paramName'     => 'date',
                'paramOperator' => '>=',
                'paramValue'    => $fromDate,
            ],
            'to_date'       =>  [
                'paramName'     => 'date',
                'paramOperator' => '<=',
                'paramValue'    => $toDate,
            ],
        ];

        $relationalParams = [
            'supplier_account_id'   =>  [
                'relation'      => 'transaction',
                'paramName'     => 'credit_account_id',
                'paramValue'    => $request->get('supplier_account_id'),
            ]
        ];

        $purchases      = $this->purchaseRepo->getPurchases($params, $relationalParams, $noOfRecords);
        $totalAmount    = $this->purchaseRepo->getPurchases($params, $relationalParams, null)->sum('total_amount');

        //params passing for auto selection
        $params['from_date']['paramValue'] = $request->get('from_date');
        $params['to_date']['paramValue'] = $request->get('to_date');
        $params = array_merge($params, $relationalParams);

        return view('purchases.list', [
            'purchaseRecords'   => $purchases,
            'totalAmount'       => $totalAmount,
            'params'            => $params,
            'noOfRecords'       => $noOfRecords,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('purchases.register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        PurchaseRegistrationRequest $request,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo,
        VoucherRepository $voucherRepo,
        $id=null
    ) {
        $saveFlag            = false;
        $errorCode           = 0;
        $purchase            = null;
        $purchaseTransaction = null;
        $voucher             = null;
        $voucherTransaction  = null;
        $voucherResponse     = null;

        //configured values
        $purchaseAccountId  = config('constants.accountConstants.Purchase.id');
        $accountRelations   = config('constants.accountRelationTypes');
        $cashAccountId      = config('constants.accountConstants.Cash.id');

        $transactionDate    = Carbon::createFromFormat('d-m-Y', $request->get('purchase_date'))->format('Y-m-d');
        $supplierAccountId  = $request->get('supplier_account_id');
        $products           = $request->get('product_id');
        $totalBill          = $request->get('total_bill');
        $supplierName       = $request->get('supplier_name');
        $supplierPhone      = $request->get('supplier_phone');
        $cashPaid           = $request->get('cash_paid');

        //wrappin db transactions
        DB::beginTransaction();
        try {
            foreach ($products as $index => $productId) {
                if(!empty($request->get('net_quantity')[$index]) && !empty($request->get('purchase_rate')[$index])) {
                    $productArray[$productId] = [
                        'net_quantity'      => $request->get('net_quantity')[$index],
                        'rate'              => $request->get('purchase_rate')[$index],
                        'gross_quantity'    => $request->get('gross_quantity')[$index] ?: null,
                        'product_number'    => $request->get('product_number')[$index] ?: null,
                        'unit_wastage'      => $request->get('unit_wastage')[$index] ?: null,
                        'total_wastage'     => $request->get('total_wastage')[$index] ?: null,
                    ];
                }
            }

            //confirming purchase account existency.
            $purchaseAccount = $accountRepo->getAccount($purchaseAccountId);

            //if editing
            if(!empty($id)) {
                $purchase               = $this->purchaseRepo->getPurchase($id);
                $purchaseTransaction    = $transactionRepo->getTransaction($purchase->transaction_id);
                if(!empty($purchase->voucher_id)) {
                    $voucher            = $this->voucherRepo->getVoucher($purchase->voucher_id);
                    $voucherTransaction = $transactionRepo->getTransaction($voucher->transaction_id);
                }
            }

            if($supplierAccountId == -1) {
                //checking for exist-ency of the account
                $accounts = $accountRepo->getAccounts(['phone' => $supplierPhone],null,null,false);

                if(empty($accounts) || count($accounts) == 0)
                {
                    //save quick supplier account to table
                    $accountResponse = $accountRepo->saveAccount([
                        'account_name'      => $supplierName. "-". $supplierPhone,
                        'description'       => ("New account of". $supplierName),
                        'relation'          => array_search('Supplier', $accountRelations), //supplier key=2
                        'financial_status'  => 0,
                        'opening_balance'   => 0,
                        'name'              => $supplierName,
                        'phone'             => $supplierPhone,
                        'address'           => '',
                        'image'             => null,
                        'status'            => 1,
                    ]);

                    if(!$accountResponse['flag']) {
                        throw new AppCustomException("CustomError", $accountResponse['errorCode']);
                    }
                    $supplierAccountId  = $accountResponse['id'];
                    $particulars        = ("Purchase from ". $supplierName. "-". $supplierPhone);
                    $voucherParticulars = "Cash paid with the purchase bill of Rs.". $totalBill. "[ Cash Acount -> ". $supplierName . "]";
                } else {
                    $supplierAccount    = $accounts->first();
                    $supplierAccountId  = $supplierAccount->id;
                    $particulars        = ("Purchase from ". $supplierAccount->account_name. "-". $supplierAccount->phone);
                    $voucherParticulars = "Cash paid with the purchase bill of Rs.". $totalBill. "[ Cash Acount -> ". $supplierAccount->account_name . "]";
                }
            } else {
                //accessing debit account
                $supplierAccount    = $accountRepo->getAccount($supplierAccountId, false);
                $particulars        = ("Purchase from ". $supplierAccount->account_name);
                $voucherParticulars = "Cash paid with the purchase bill of Rs.". $totalBill. "[ Cash Acount -> ". $supplierAccount->account_name . "]";
            }

            //save voucher transaction if cash paid to supplier
            if(!empty($cashPaid) && $cashPaid >= 1) {
                $voucherTransactionResponse = $transactionRepo->saveTransaction([
                    'debit_account_id'  => $supplierAccountId, // debit the supplier account
                    'credit_account_id' => $cashAccountId, // credit cash account
                    'amount'            => $cashPaid ,
                    'transaction_date'  => $transactionDate,
                    'particulars'       => ("Cash paid with the purchase bill of Rs.". $totalBill),
                ], $voucherTransaction);

                $voucherResponse = $voucherRepo->saveVoucher([
                    'transaction_id' => $voucherTransactionResponse['id'],
                    'date'           => $transactionDate,
                    'voucher_type'   => 2, //cash payment
                    'amount'         => $cashPaid,
                ], $voucher);

            }

            //save purchase transaction to table
            $transactionResponse = $transactionRepo->saveTransaction([
                'debit_account_id'  => $purchaseAccountId, // debit the purchase account
                'credit_account_id' => $supplierAccountId, // credit the supplier
                'amount'            => $totalBill ,
                'transaction_date'  => $transactionDate,
                'particulars'       => ($request->get('description'). "(". $particulars. ")"),
            ], $purchaseTransaction);

            if(!$transactionResponse['flag']) {
                throw new AppCustomException("CustomError", $transactionResponse['errorCode']);
            }

            //save to purchase table
            $purchaseResponse = $this->purchaseRepo->savePurchase([
                'transaction_id'    => $transactionResponse['id'],
                'date'              => $transactionDate,
                'voucher_id'        => (!empty($voucherResponse['id']) ? $voucherResponse['id'] : null),
                'supplier_name'     => $supplierName,
                'supplier_phone'    => $supplierPhone,
                'description'       => $request->get('description'),
                'discount'          => $request->get('discount'),
                'total_amount'      => $totalBill,
                'productsArray'     => $productArray,
            ], $purchase);

            if(!$purchaseResponse['flag']) {
                throw new AppCustomException("CustomError", $purchaseResponse['errorCode']);
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
            return redirect(route('purchase.invoice', $purchaseResponse['id']))->with("message","Purchase details saved successfully. Reference Number : ". $transactionResponse['id'])->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to save the purchase details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
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
        $purchase   = [];

        try {
            $purchase = $this->purchaseRepo->getPurchase($id);
        } catch (\Exception $e) {
            if($e->getMessage() == "CustomError") {
                $errorCode = $e->getCode();
            } else {
                $errorCode = 2;
            }
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("Purchase", $errorCode);
        }

        return view('purchases.details', [
            'purchase' => $purchase,
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
        //return redirect()->back()->with("message","Edit disabled!")->with("alert-class", "error");

        $errorCode  = 0;
        $purchase   = [];

        try {
            $purchase = $this->purchaseRepo->getPurchase($id);
        } catch (\Exception $e) {
            if($e->getMessage() == "CustomError") {
                $errorCode = $e->getCode();
            } else {
                $errorCode = 3;
            }
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("Purchase", $errorCode);
        }

        return view('purchases.edit', [
            'purchase' => $purchase,
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
        PurchaseRegistrationRequest $request,
        $id,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo
    ) {
        $updateResponse = $this->store($request, $transactionRepo, $accountRepo, $id);

        if($updateResponse['flag']) {
            return redirect(route('purchase.index', $updateResponse['id']))->with("message","Purchase details updated successfully. Updated Record Number : ". $updateResponse['id'])->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to update the purchase details. Error Code : ". $this->errorHead. "/". $updateResponse['errorCode'])->with("alert-class", "error");
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
            $deleteResponse = $this->purchaseRepo->deletePurchase($id);
            
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
                $errorCode = 4;
            }
        }

        if($deleteFlag) {
            return redirect(route('purchase.index'))->with("message","Purchase details deleted successfully.")->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to delete the purchase details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
    }

    /**
     * Show the invoice for print.
     *
     * @return \Illuminate\Http\Response
     */
    public function invoice($id, TransactionRepository $transactionRepo)
    {
        $errorCode  = 0;
        $purchase   = [];
        $oldBalance = 0;

        try {
            $purchase = $this->purchaseRepo->getPurchase($id);
            if(!empty($purchase->payment)) {
                $oldBal = $transactionRepo->getOldBalance($purchase->transaction->credit_account_id, null, $purchase->payment->transaction_id);
            } else {
                $oldBal = $transactionRepo->getOldBalance($purchase->transaction->credit_account_id, null, $purchase->transaction_id);
            }

            $oldBalance = $oldBal['debit'] - $oldBal['credit'];
        } catch (\Exception $e) {
            if($e->getMessage() == "CustomError") {
                $errorCode = $e->getCode();
            } else {
                $errorCode = 5;
            }
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("Purchase", $errorCode);
        }

        return view('purchases.invoice', [
            'purchase'      => $purchase,
            'oldBalance'    => $oldBalance
        ]);
    }
}
