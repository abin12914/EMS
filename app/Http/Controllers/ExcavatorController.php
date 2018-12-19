<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ExcavatorRepository;
use App\Repositories\AccountRepository;
use App\Http\Requests\ExcavatorRegistrationRequest;
use App\Http\Requests\ExcavatorFilterRequest;
use Carbon\Carbon;
use Auth;
use DB;
use Exception;
use App\Exceptions\AppCustomException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExcavatorController extends Controller
{
    protected $excavatorRepo;
    public $errorHead = null;

    public function __construct(ExcavatorRepository $excavatorRepo)
    {
        $this->excavatorRepo = $excavatorRepo;
        $this->errorHead     = config('settings.controller_code.ExcavatorController');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ExcavatorFilterRequest $request)
    {
        $noOfRecordsPerPage = $request->get('no_of_records') ?? config('settings.no_of_record_per_page');
        //getExcavators($whereParams=[],$orWhereParams=[],$relationalParams=[],$orderBy=['by' => 'id', 'order' => 'asc', 'num' => null], $withParams=[],$activeFlag=true)
        return view('excavators.list', [
            'excavators'  => $this->excavatorRepo->getExcavators([], [], [], ['by' => 'id', 'order' => 'asc', 'num' => $noOfRecordsPerPage], [], [], true),
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
        return view('excavators.register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        ExcavatorRegistrationRequest $request,
        $id=null
    ) {
        $errorCode          = 0;

        //wrappin db transactions
        DB::beginTransaction();
        try {
            $user = Auth::user();

            //save excavator to table
            $excavatorResponse   = $excavatorRepo->saveExcavator([
                'name'         => $request->get('name'),
                'description'  => $request->get('description'),
                'maker'        => $request->get('maker'),,
                'capacity'     => $request->get('capacity'),,
                'bucket_rate'  => $request->get('bucket_rate'),
                'breaker_rate' => $request->get('breaker_rate'),,
                'status'       => 1,     
                'created_by'   => $user->id,
                'company_id'   => $user->company_id,
            ], $id);

            if(!$excavatorResponse['flag']) {
                throw new AppCustomException("CustomError", $excavatorResponse['errorCode']);
            }

            if(!empty($id)) {
                return [
                    'flag'    => true,
                    'excavator' => $excavatorResponse['excavator']
                ];
            }

            return redirect(route('excavator.index'))->with("message","Excavator details saved successfully. Reference Number : ". $excavatorResponse['excavator']->id)->with("alert-class", "success");
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
        return redirect()->back()->with("message","Failed to save the excavator details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
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
        $excavator  = [];

        try {
            $excavator = $this->excavatorRepo->getExcavator($id, [], false);
        } catch (\Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 2);
            
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("Excavator", $errorCode);
        }

        return view('excavators.details', [
            'excavator' => $excavator,
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
        $excavator  = [];

        try {
            $excavator = $this->excavatorRepo->getExcavator($id, [], false);
        } catch (\Exception $e) {
            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 3);
            //throwing methodnotfound exception when no model is fetched
            throw new ModelNotFoundException("Excavator", $errorCode);
        }

        return view('excavators.edit', [
            'excavator' => $excavator,
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
        ExcavatorRegistrationRequest $request,
        $id
    ) {
        $updateResponse = $this->store($request, $id);

        if($updateResponse['flag']) {
            return redirect(route('excavators.show', $updateResponse['excavator']->id))->with("message","Excavators details updated successfully. Updated Record Number : ". $updateResponse['excavator']->id)->with("alert-class", "success");
        }
        
        return redirect()->back()->with("message","Failed to update the excavator details. Error Code : ". $this->errorHead. "/". $updateResponse['errorCode'])->with("alert-class", "error");
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
            $deleteResponse = $this->excavatorRepo->deleteExcavator($id, false);
            
            if(!$deleteResponse['flag']) {
                throw new AppCustomException("CustomError", $deleteResponse['errorCode']);
            }
            
            DB::commit();
            return redirect(route('excavator.index'))->with("message","Excavator details deleted successfully.")->with("alert-class", "success");
        } catch (Exception $e) {
            //roll back in case of exceptions
            DB::rollback();

            $errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 4);
        }
        
        return redirect()->back()->with("message","Failed to delete the excavator details. Error Code : ". $this->errorHead. "/". $errorCode)->with("alert-class", "error");
    }
}
