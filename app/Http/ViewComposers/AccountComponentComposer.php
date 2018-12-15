<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\AccountRepository;
use Exception;
//use App\Exceptions\AppCustomException;

class AccountComponentComposer
{
    protected $accounts = [];//, $cashAccount, $errorHead = null;

    /**
     * Create a new account partial composer.
     *
     * @param  AccountRepository  $account
     * @return void
     */
    public function __construct(AccountRepository $accountRepo)
    {
        //$errorCode = 0;
        //$this->errorHead = config('settings.composer_code.AccountComponentComposer');
        $orWhereParams = [
            [
                'paramName'     => 'type',
                'paramOperator' => '=',
                'paramValue'    => 1,
            ],
            [
                'paramName'     => 'type',
                'paramOperator' => '=',
                'paramValue'    => 3,
            ]
        ];
        
        try {
            //getAccounts($whereParams=[],$orWhereParams=[],$relationalParams=[],$orderBy=['by' => 'id', 'order' => 'asc', 'num' => null], $withParams=[],$activeFlag=true)
            $this->accounts = $accountRepo->getAccounts([], $orWhereParams, [], ['by' => 'id', 'order' => 'asc', 'num' => null], $aggregates=['key' => null, 'value' => null], [], true);
            
        } catch (Exception $e) {
            //$errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 1);
            
            //throw new AppCustomException("CustomError", ($this->errorHead + $errorCode));
        }
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with(['accountsCombo' => $this->accounts]);
    }
}