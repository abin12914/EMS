<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ExcavatorReadingRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\AccountRepository;
use App\Http\Requests\ExcavatorReadingRegistrationRequest;
use App\Http\Requests\ExcavatorReadingFilterRequest;
use Carbon\Carbon;
use Auth;
use DB;
use Exception;
use App\Exceptions\AppCustomException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExcavatorReadingController extends Controller
{
    protected $excavatorReadingRepo;
    public $errorHead = null;

    public function __construct(ExcavatorReadingRepository $excavatorReadingRepo)
    {
        $this->excavatorReadingRepo = $excavatorReadingRepo;
        $this->errorHead            = config('settings.controller_code.ExcavatorReadingController');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ExcavatorReadingFilterRequest $request)
    {
        $noOfRecordsPerPage = $request->get('no_of_records') ?? config('settings.no_of_record_per_page');
        //date format conversion
        $fromDate    = empty($request->get('from_date')) ?: Carbon::createFromFormat('d-m-Y', $request->get('from_date'))->format('Y-m-d');
        $toDate      = empty($request->get('to_date')) ?: Carbon::createFromFormat('d-m-Y', $request->get('to_date'))->format('Y-m-d');

        $whereParams = [
            'from_date' => [
                'paramName'     => 'reading_date',
                'paramOperator' => '>=',
                'paramValue'    => $fromDate,
            ],
            'to_date' => [
                'paramName'     => 'reading_date',
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
            'employee_id' => [
                'paramName'     => 'operator_id',
                'paramOperator' => '=',
                'paramValue'    => $request->get('employee_id'),
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
        
        //getExcavatorReadings($whereParams=[],$orWhereParams=[],$relationalParams=[],$orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],$aggregates=['key' => null, 'value' => null],$withParams=[],$activeFlag=true)
        return view('excavator-readings.list', [
            'excavatorReadings' => $this->excavatorReadingRepo->getExcavatorReadings($whereParams, [], $relationalParams, ['by' => 'id', 'order' => 'asc', 'num' => $noOfRecordsPerPage], ['excavator', 'transaction.debitAccount', 'site', 'operator.account'], [], true),
            'totalExcavatorReading' => $this->excavatorReadingRepo->getExcavatorReadings($whereParams, [], $relationalParams, [], ['key' => 'sum', 'value' => 'total_rent'], [], true),
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
        return view('excavator-readings.register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        ExcavatorReadingRegistrationRequest $request,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo,
        $id=null
    ) {
        $errorCode        = 0;
        $excavatorReading = null;

        $excavatorRentAccountId = config('constants.accountConstants.ExcavatorRent.id');
        $transactionDate        = Carbon::createFromFormat('d-m-Y', $request->get('reading_date'))->format('Y-m-d');
        $bucketRate             = $request->get('bucket_rate');
        $bucketHour             = $request->get('bucket_hour');
        $breakerRate            = $request->get('breaker_rate');
        $breakerHour            = $request->get('breaker_hour');
        $particulars            = ('[Bucket : '. $bucketHour. 'x'. $bucketRate. '='. ($bucketHour * $bucketRate). ' + Breaker : '. $breakerHour. 'x'. $breakerRate. '='. ($breakerHour * $breakerRate). ']');

        //wrappin db transactions
        DB::beginTransaction();
        try {
            $user = Auth::user();

            //confirming excavatorRent account exist-ency.
            $excavatorRentAccount = $accountRepo->getAccount($excavatorRentAccountId, [], false);
            if(!empty($id)) {
                $excavatorReading     = $this->excavatorReadingRepo->getExcavatorReading($id, [], false);
            }

            //save excavatorReading transaction to table
            $transactionResponse   = $transactionRepo->saveTransaction([
                'debit_account_id'  => $request->get('customer_account_id'), // debit the customer
                'credit_account_id' => $excavatorRentAccountId, // credit the excavatorRent Account
                'amount'            => $request->get('total_rent'),
                'transaction_date'  => $transactionDate,
                'particulars'       => $request->get('description'). $particulars,
                'status'            => 1,
                'company_id'        => $user->company_id,
            ], (!empty($excavatorReading) ? $excavatorReading->transaction_id : null));

            if(!$transactionResponse['flag']) {
                throw new AppCustomException("CustomError", $transactionResponse['errorCode']);
            }

            //save to excavatorReading table
            $excavatorReadingResponse = $this->excavatorReadingRepo->saveExcavatorReading([
                'reading_date'   => $transactionDate,
                'excavator_id'   => $request->get('excavator_id'),
                'transaction_id' => $transactionResponse['transaction']->id,
                'site_id'        => $request->get('site_id'),
                'operator_id'    => $request->get('employee_id'),
                'description'    => $request->get('description'),
                'bucket_hour'    => $request->get('bucket_hour'),
                'bucket_rate'    => $request->get('bucket_rate'),
                'breaker_hour'   => $request->get('breaker_hour'),
                'breaker_rate'   => $request->get('breaker_rate'),
                'total_rent'     => $request->get('total_rent'),
                'status'         => 1,
                'created_by'     => $user->id,
                'company_id'     => $user->company_id,
            ], $id);

            if(!$excavatorReadingResponse['flag']) {
                throw new AppCustomException("CustomError", $excavatorReadingResponse['errorCode']);
            }

            DB::commit();

            if(!empty($id)) {
                return [
                    'flag'             => true,
                    'excavatorReading' => $excavatorReadingResponse['excavatorReading']
                ];
            }

            return redirect(route('excavator-reading.index'))->with("message","ExcavatorReading details saved successfully. Reference Number : ". $excavatorReadingResponse['excavatorReading']->id)->with("alert-class", "success");
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
        return redirect()->back()->with("message","Failed to save the excavatorReading details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
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
        $excavatorReading    = [];

        try {
            $excavatorReading = $this->excavatorReadingRepo->getExcavatorReading($id, [], false);
        } catch (\Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 2);
            
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("ExcavatorReading", $errorCode);
        }

        return view('excavator-readings.details', [
            'excavatorReading' => $excavatorReading,
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
        $excavatorReading    = [];

        try {
            $excavatorReading = $this->excavatorReadingRepo->getExcavatorReading($id, [], false);
        } catch (\Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 3);
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("ExcavatorReading", $errorCode);
        }

        return view('excavator-readings.edit', [
            'excavatorReading' => $excavatorReading,
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
        ExcavatorReadingRegistrationRequest $request,
        TransactionRepository $transactionRepo,
        AccountRepository $accountRepo,
        $id
    ) {
        $updateResponse = $this->store($request, $transactionRepo, $accountRepo, $id);

        if($updateResponse['flag']) {
            return redirect(route('excavator-reading.index'))->with("message","ExcavatorReadings details updated successfully. Updated Record Number : ". $updateResponse['excavatorReading']->id)->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to update the excavatorReadings details. Error Code : ". $this->errorHead. "/". $updateResponse['errorCode'])->with("alert-class", "error");
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
            $deleteResponse = $this->excavatorReadingRepo->deleteExcavatorReading($id, false);
            
            if(!$deleteResponse['flag']) {
                throw new AppCustomException("CustomError", $deleteResponse['errorCode']);
            }
            
            DB::commit();
            return redirect(route('excavatorReading.index'))->with("message","ExcavatorReading details deleted successfully.")->with("alert-class", "success");
        } catch (Exception $e) {
            //roll back in case of exceptions
            DB::rollback();

            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 4);
        }
        
        return redirect()->back()->with("message","Failed to delete the excavatorReading details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
    }

    public function getLastReadingDetail(Request $request)
    {
        $lastExcavatorReading = [];
        $whereParams = [
            'excavator_id' => [
                'paramName'     => 'excavator_id',
                'paramOperator' => '=',
                'paramValue'    => $request->get('excavator_id'),
            ],
        ];

        try {
            //getExcavatorReadings($whereParams=[],$orWhereParams=[],$relationalParams=[],$orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],$aggregates=['key' => null, 'value' => null],$withParams=[],$activeFlag=true)
            $lastExcavatorReading = $this->excavatorReadingRepo->getExcavatorReadings($whereParams, [], [], ['by' => 'id', 'order' => 'desc', 'num' => 1], ['key' => null, 'value' => null], ['transaction'], true);
        } catch (\Exception $e) {
            return [
                'flag' => false,
            ];
        }

        return [
            'flag'        => true,
            'lastReading' => $lastExcavatorReading,
        ];
    }
}
