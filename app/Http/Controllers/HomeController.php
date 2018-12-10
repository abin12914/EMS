<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\PurchaseRepository;
use App\Repositories\SaleRepository;
use App\Repositories\AccountRepository;
use App\Repositories\UserRepository;
use App\Http\Requests\ProfileUpdateRequest;
use Auth;
use Hash;
use DB;
use Exception;
use App\Exceptions\AppCustomException;

class HomeController extends Controller
{
    public $errorHead = null;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('index');
        $this->errorHead = config('settings.controller_code.HomeController');
    }

    /**
     * Show the application welcome page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Show the user dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard(
        PurchaseRepository $purchaseRepo,
        SaleRepository $saleRepo,
        AccountRepository $accountRepo
    ){
        return view('users.dashboard');
    }

    /**
     * Return view for user profile
     */
    public function profileView()
    {
        return view('users.profile');
    }

    /**
     * action for user profile update
     */
    public function profileUpdate(ProfileUpdateRequest $request, UserRepository $userRepo)
    {
        $errorCode = 0;
        $response  = [];

        $inputArray['username']         = $request->get('username');
        $inputArray['name']             = $request->get('name');
        $inputArray['email']            = $request->get('email');
        $inputArray['password']         = Hash::make($request->get('password'));
        $inputArray['currentPassword']  = $request->get('currentPassword');

        if(!Hash::check($inputArray['currentPassword'], Auth::User()->password)) {
            return redirect()->back()->with("message", "Authentication Failed! Invalid password.")->with("alert-class", "error");
        }

        //wrappin db transactions
        DB::beginTransaction();
        try {
            $user = Auth::User();
            $response = $userRepo->updateProfile($inputArray, $user);

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
            return redirect(route('dashboard'))->with("message", "Profile Successfully Updated!")->with("alert-class", "success");
        }
        return redirect()->back()->with("message", "Profile Update failed! Error Code : ". $response['errorCode'])->with("alert-class", "error");
    }
}
