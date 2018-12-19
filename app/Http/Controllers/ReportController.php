<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\TransactionRepository;
use App\Repositories\AccountRepository;
use App\Http\Requests\TransactionFilterRequest;
use Carbon\Carbon;
use DB;
use Exception;
use App\Exceptions\AppCustomException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReportController extends Controller
{
    protected $transactionRepo, $accountRepo;
    public $errorHead = null;

    public function __construct(TransactionRepository $transactionRepo, AccountRepository $accountRepo)
    {
        $this->transactionRepo      = $transactionRepo;
        $this->accountRepo          = $accountRepo;
        $this->errorHead            = config('settings.controller_code.ReportController');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function accountStatement(TransactionFilterRequest $request)
    {
        $obDebit         = 0;
        $obCredit        = 0;
        
        $noOfRecordsPerPage = $request->get('no_of_records') ?? config('settings.no_of_record_per_page');
        //if no account is selected use cash account;
        $accountId          = $request->get('account_id') ?? config('constants.accountConstants.Cash.id');

        $fromDate           = !empty($request->get('from_date')) ? Carbon::createFromFormat('d-m-Y', $request->get('from_date'))->format('Y-m-d') : null;
        $toDate             = !empty($request->get('to_date')) ? Carbon::createFromFormat('d-m-Y', $request->get('to_date'))->format('Y-m-d') : null;

        $relation           = $request->get('relation');
        $transactionType    = $request->get('transaction_type');

        try {

            //confirming account existency.
            $account = $this->accountRepo->getAccount($accountId, [], false);

            $whereParams = [
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

            $debitParam = [
                'debit_account_id'   =>  [
                    'paramName'      => 'debit_account_id',
                    'paramOperator'  => '=',
                    'paramValue'     => $accountId,
                ],
            ];

            $creditParam = [
                'credit_account_id'  =>  [
                    'paramName'      => 'credit_account_id',
                    'paramOperator'  => '=',
                    'paramValue'     => $accountId,
                ]
            ];

            $obParam = [
                'from_date' =>  [
                    'paramName'     => 'transaction_date',
                    'paramOperator' => '<',
                    'paramValue'    => $fromDate,
                ]
            ];

            $orWhereParams = array_merge($debitParam, $creditParam);

            $subTotalWhereParams = [];
            if($transactionType == 1) {
                //if user select debit transactions only then remove credit transaction checking (or condition)
                unset($orWhereParams['credit_account_id']);
            } elseif ($transactionType == 2) {
                //if user select credit transactions only then remove debit transaction checking (or condition)
                unset($orWhereParams['debit_account_id']);
            } //else both transactions are included with or condition

            //display data
            //getTransactions($whereParams=[],$orWhereParams=[],$relationalParams=[],$orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],$aggregates=['key' => null, 'value' => null],$withParams=[],$relation,$activeFlag=true)
            $transactions = $this->transactionRepo->getTransactions($whereParams, $orWhereParams, [], ['by' => 'id', 'order' => 'asc', 'num' => $noOfRecordsPerPage], [], [], $relation, true);

            //subtotal values
            $subTotalDebit  = $this->transactionRepo->getTransactions((array_merge($whereParams, $debitParam)), [], [], [], ['key' => 'sum', 'value' => 'amount'], [], $relation, true);
            $subTotalCredit = $this->transactionRepo->getTransactions((array_merge($whereParams, $creditParam)), [], [], [], ['key' => 'sum', 'value' => 'amount'], [], $relation, true);

            //outstanding values
            $outstandingDebit   = $this->transactionRepo->getTransactions($debitParam, [], [], [], ['key' => 'sum', 'value' => 'amount'], [], null, true);
            $outstandingCredit  = $this->transactionRepo->getTransactions($creditParam, [], [], [], ['key' => 'sum', 'value' => 'amount'], [], null, true);

            //old balance values
            if(!empty($fromDate)) {
                $obDebit = $this->transactionRepo->getTransactions((array_merge($obParam, $debitParam)), [], [], [], ['key' => 'sum', 'value' => 'amount'], [], null, true);
                $obCredit = $this->transactionRepo->getTransactions((array_merge($obParam, $creditParam)), [], [], [], ['key' => 'sum', 'value' => 'amount'], [], null, true);
            }

        } catch(Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 1);

            throw new AppCustomException("CustomError", $errorCode);
            
        }

        //params passing for auto selection
        $params['from_date']['paramValue']        = $request->get('from_date');
        $params['to_date']['paramValue']          = $request->get('to_date');
        $params['relation']['paramValue']         = $relation;
        $params['account_id']['paramValue']       = $accountId;
        $params['transaction_type']['paramValue'] = $transactionType;

        return view('reports.account-statement', [
            'transactions'          => $transactions,
            'params'                => $params,
            'relations'             => config('constants.transactionRelations'),
            'noOfRecords'           => $noOfRecordsPerPage,
            'account'               => $account,
            'outstandingDebit'      => $outstandingDebit,
            'outstandingCredit'     => $outstandingCredit,
            'subTotalDebit'         => $subTotalDebit,
            'subTotalCredit'        => $subTotalCredit,
            'obDebit'               => $obDebit,
            'obCredit'              => $obCredit
        ]);
    }

    public function creditList(Request $request, AccountRepository $accountRepo)
    {
        $creditAmount       = [];
        $debitAmount        = [];
        $accounts           = [];
        $totalCredit        = 0;
        $totalDebit         = 0;

        $accountRelation    = $request->get('relation_type');

        if(!empty($accountRelation)) {
            $debitRelationalParams = [
                'relation_type'   =>  [
                    'relation'      => 'debitAccount',
                    'paramName'     => 'relation',
                    'paramOperator' => '=',
                    'paramValue'    => $accountRelation,
                ]
            ];

            $creditRelationalParams = [
                'relation_type'   =>  [
                    'relation'      => 'creditAccount',
                    'paramName'     => 'relation',
                    'paramOperator' => '=',
                    'paramValue'    => $accountRelation,
                ]
            ];

            /*$debitTransactions = $this->transactionRepo->groupTransactions([], [], $debitRelationalParams, [], [], 'debit_account_id', true)->select(\DB::raw('debit_account_id as id'), \DB::raw('sum(amount) as total'))->get();
            $creditTransactions = $this->transactionRepo->groupTransactions([], [], $creditRelationalParams, [], [], 'credit_account_id', true)->select(\DB::raw('credit_account_id as id'), \DB::raw('sum(amount) as total'))->get();
            dd($debitTransactions);*/
        }

        return view('reports.credit-list',[
                'accounts'          => $accounts,
                'creditAmount'      => $creditAmount,
                'debitAmount'       => $debitAmount,
                'relation'          => $accountRelation,
                'relationTypes'     => config('constants.accountRelationTypes'),
                'totalCreditAmount' => $totalCredit,
                'totalDebitAmount'  => $totalDebit
            ]);
    }
}
