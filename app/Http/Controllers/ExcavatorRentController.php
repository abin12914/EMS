<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ExcavatorRentRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\AccountRepository;
use App\Http\Requests\ExcavatorRentRegistrationRequest;
use App\Http\Requests\ExcavatorRentFilterRequest;
use Carbon\Carbon;
use Auth;
use DB;
use Exception;
use App\Exceptions\AppCustomException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExcavatorRentController extends Controller
{
    protected $excavatorRentRepo;
    public $errorHead = null;

    public function __construct(ExcavatorRentRepository $excavatorRentRepo)
    {
        $this->excavatorRentRepo = $excavatorRentRepo;
        $this->errorHead            = config('settings.controller_code.ExcavatorRentController');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ExcavatorRentFilterRequest $request)
    {
        $noOfRecordsPerPage = $request->get('no_of_records') ?? config('settings.no_of_record_per_page');
        //date format conversion
        $fromDate    = !empty($request->get('from_date')) ? Carbon::createFromFormat('d-m-Y', $request->get('from_date'))->format('Y-m-d') : null;
        $toDate      = !empty($request->get('to_date')) ? Carbon::createFromFormat('d-m-Y', $request->get('to_date'))->format('Y-m-d') : null;

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
            'excavator_id' => [
                'paramName'     => 'excavator_id',
                'paramOperator' => '=',
                'paramValue'    => $request->get('excavator_id'),
            ],
            'site_id' => [
                'paramName'     => 'site_id',
                'paramOperator' => '=',
                'paramValue'    => $request->get('site_id'),
            ],
        ];

        $relationalParams = [
            'account_id' => [
                'relation'      => 'transaction',
                'paramName'     => 'debit_account_id',
                'paramOperator' => '=',
                'paramValue'    => $request->get('account_id'),
            ]
        ];
        //params passing for auto selection
        $whereParams['from_date']['paramValue'] = $request->get('from_date');
        $whereParams['to_date']['paramValue']   = $request->get('to_date');
        
        //getExcavatorRents($whereParams=[],$orWhereParams=[],$relationalParams=[],$orderBy=['by' => 'id', 'order' => 'asc', 'num' => null], $withParams=[],$activeFlag=true)
        return view('excavator-rent.list', [
            'excavatorRents' => $this->excavatorRentRepo->getExcavatorRents($whereParams, [], $relationalParams, ['by' => 'id', 'order' => 'asc', 'num' => $noOfRecordsPerPage], [], ['excavator', 'transaction.debitAccount', 'site'], true),
            'totalExcavatorRent' => $this->excavatorRentRepo->getExcavatorRents($whereParams, [], $relationalParams, [], ['key' => 'sum', 'value' => 'rent'], [], true),
            'params'       => array_merge($whereParams, $relationalParams),
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
        return view('excavator-rent.register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        ExcavatorRentRegistrationRequest $request,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo,
        $id=null
    ) {
        $errorCode     = 0;
        $excavatorRent = null;

        $excavatorRentAccountId = config('constants.accountConstants.ExcavatorRent.id');
        
        $fromDate    = Carbon::createFromFormat('d-m-Y', $request->get('from_date'))->format('Y-m-d');
        $toDate      = Carbon::createFromFormat('d-m-Y', $request->get('to_date'))->format('Y-m-d');
        $particulars = ("Excavator rent generated.");

        //wrappin db transactions
        DB::beginTransaction();
        try {
            $user = Auth::user();

            //confirming excavatorRent account exist-ency.
            $excavatorRentAccount = $accountRepo->getAccount($excavatorRentAccountId, [], false);
            if(!empty($id)) {
                $excavatorRent = $this->excavatorRentRepo->getExcavatorRent($id, [], false);
            }

            //save excavatorRent transaction to table
            $transactionResponse   = $transactionRepo->saveTransaction([
                'debit_account_id'  => $request->get('account_id'), // credit the supplier
                'credit_account_id' => $excavatorRentAccountId, // debit the excavatorRent Account
                'amount'            => $request->get('total_rent'),
                'transaction_date'  => $toDate,
                'particulars'       => $request->get('description'). $particulars,
                'status'            => 1,
                'company_id'        => $user->company_id,
            ], (!empty($excavatorRent) ? $excavatorRent->transaction_id : null));

            if(!$transactionResponse['flag']) {
                throw new AppCustomException("CustomError", $transactionResponse['errorCode']);
            }

            //save to excavatorRent table
            $excavatorRentResponse = $this->excavatorRentRepo->saveExcavatorRent([
                'excavator_id'   => $request->get('excavator_id'),
                'transaction_id' => $transactionResponse['transaction']->id,
                'site_id'        => $request->get('site_id'),
                'from_date'      => $fromDate,
                'to_date'        => $toDate,
                'description'    => $request->get('description'),
                'rent'           => $request->get('total_rent'),
                'status'         => 1,
                'created_by'     => $user->id,
                'company_id'     => $user->company_id,
            ], $id);

            if(!$excavatorRentResponse['flag']) {
                throw new AppCustomException("CustomError", $excavatorRentResponse['errorCode']);
            }

            DB::commit();

            if(!empty($id)) {
                return [
                    'flag'             => true,
                    'excavatorRent' => $excavatorRentResponse['excavatorRent']
                ];
            }

            return redirect(route('excavator-rent.index'))->with("message","ExcavatorRent details saved successfully. Reference Number : ". $excavatorRentResponse['excavatorRent']->id)->with("alert-class", "success");
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
        return redirect()->back()->with("message","Failed to save the excavatorRent details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
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
        $excavatorRent    = [];

        try {
            $excavatorRent = $this->excavatorRentRepo->getExcavatorRent($id, [], false);
        } catch (\Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 2);
            
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("ExcavatorRent", $errorCode);
        }

        return view('excavator-rent.details', [
            'excavatorRent' => $excavatorRent,
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
        $excavatorRent    = [];

        try {
            $excavatorRent = $this->excavatorRentRepo->getExcavatorRent($id, [], false);
        } catch (\Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 3);
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("ExcavatorRent", $errorCode);
        }

        return view('excavator-rent.edit', [
            'excavatorRent' => $excavatorRent,
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
        ExcavatorRentRegistrationRequest $request,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo,
        $id
    ) {
        $updateResponse = $this->store($request, $transactionRepo, $accountRepo, $id);

        if($updateResponse['flag']) {
            return redirect(route('excavator-rent.index'))->with("message","ExcavatorRents details updated successfully. Updated Record Number : ". $updateResponse['excavatorRent']->id)->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to update the excavatorRents details. Error Code : ". $this->errorHead. "/". $updateResponse['errorCode'])->with("alert-class", "error");
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
            $deleteResponse = $this->excavatorRentRepo->deleteExcavatorRent($id, false);
            
            if(!$deleteResponse['flag']) {
                throw new AppCustomException("CustomError", $deleteResponse['errorCode']);
            }
            
            DB::commit();
            return redirect(route('excavatorRent.index'))->with("message","ExcavatorRent details deleted successfully.")->with("alert-class", "success");
        } catch (Exception $e) {
            //roll back in case of exceptions
            DB::rollback();

            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 4);
        }
        
        return redirect()->back()->with("message","Failed to delete the excavatorRent details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
    }

    /*public function getNextRentDate(Request $request)
    {
        $errorCode    = 0;
        $excavatorRent = [];
        $nextRentDate = '';

        $whereParams = [
            'excavator_id' => [
                'paramName'     => 'excavator_id',
                'paramOperator' => '=',
                'paramValue'    => $request->get('excavator_id'),
            ],
        ];

        try {
            //getExcavatorRents($whereParams=[],$orWhereParams=[],$relationalParams=[],$orderBy=['by' => 'id', 'order' => 'asc', 'num' => null], $aggregates=['key' => null, 'value' => null], $withParams=[],$activeFlag=true)
            $excavatorRent = $this->excavatorRentRepo->getExcavatorRents($whereParams, [], [], ['by' => 'id', 'order' => 'desc', 'num' => 1], [], [], true);

            if(!empty($excavatorRent) && !empty($excavatorRent->id)) {
                $excavatorLastRentDate = $excavatorRent->to_date;
                $nextRentDate = new \DateTime($excavatorLastRentDate);
                $nextRentDate->modify('+1 day');
                $nextRentDate = $nextRentDate->format('m-d-Y');
            }
        } catch (\Exception $e) {
            return [
                'flag'         => false,
            ];
        }

        return [
            'flag'         => true,
            'nextRentDate' => $nextRentDate,
        ];
    }*/
}
