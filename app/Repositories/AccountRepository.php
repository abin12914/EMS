<?php

namespace App\Repositories;

use App\Models\Account;
use Exception;
use App\Exceptions\AppCustomException;

class AccountRepository
{
    public $repositoryCode, $errorCode = 0, $loop = 0;

    public function __construct()
    {
        $this->repositoryCode = config('settings.repository_code.AccountRepository');
    }

    /**
     * Return accounts.
     */
    public function getAccounts(
        $whereParams=[],
        $orWhereParams=[],
        $relationalParams=[],
        $orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],
        $aggregates=['key' => null, 'value' => null],
        $withParams=[],
        $activeFlag=true
    ){
        $accounts = [];

        try {
            if(empty($withParams)) {
                $accounts = Account::query();
            } else {
                $accounts = Account::with($withParams);
            }

            if($activeFlag) {
                $accounts = $accounts->active(); //status == 1
            }

            foreach ((array)$whereParams as $param) {
                if(!empty($param['paramValue'])) {
                    $accounts = $accounts->where($param['paramName'], $param['paramOperator'], $param['paramValue']);
                }
            }

            $this->loop = 0;
            $accounts = $accounts->where(function ($query) use($accounts, $orWhereParams){
                foreach((array)$orWhereParams as $orParam) {
                    if(!empty($orParam['paramValue'])) {
                        if($this->loop == 0) {
                            $this->loop ++;
                            $query->where($orParam['paramName'], $orParam['paramOperator'], $orParam['paramValue']);
                        } else {
                            $query->orWhere($orParam['paramName'], $orParam['paramOperator'], $orParam['paramValue']);
                        }
                    }
                }
            });

            foreach ((array)$relationalParams as $relationalParam) {
                if(!empty($relationalParam['paramValue'])) {
                    $accounts = $accounts->whereHas($relationalParam['relation'], function($qry) use($relationalParam) {
                        $qry->where($relationalParam['paramName'], $relationalParam['paramOperator'], $relationalParam['paramValue']);
                    });
                };
            }

            //if asking aggregates ? return result.
            if(!empty($aggregates['key'])) {
                return $accounts->$aggregates['key']($aggregates['value']);
            }

            if(!empty($orderBy['num'])) {
                if($orderBy['num'] == 1) {
                    $accounts = $accounts->firstOrFail();
                } else {
                    $accounts = $accounts->orderBy($orderBy['by'], $orderBy['order'])->paginate($orderBy['num']);
                }
            } else {
                $accounts= $accounts->orderBy($orderBy['by'], $orderBy['order'])->get();
            }
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 1);
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $accounts;
    }

    /**
     * return account.
     */
    public function getAccount($id, $withParams=[], $activeFlag=true)
    {
        $account = [];

        try {
            if(empty($withParams)) {
                $account = Account::query();
            } else {
                $account = Account::with($withParams);
            }
            
            if($activeFlag) {
                $account = $account->active();
            }

            $account = $account->findOrFail($id);
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 2);

            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $account;
    }

    /**
     * Action for saving accounts.
     */
    public function saveAccount($inputArray=[], $id=null)
    {
        try {
            //find record with id or create new if none exist
            $account = Account::findOrNew($id);

            foreach ($inputArray as $key => $value) {
                $account->$key = $value;
            }
            //account save
            $account->save();

            return [
                'flag'    => true,
                'account' => $account,
            ];
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 3);

            throw new AppCustomException("CustomError", $this->errorCode);
        }
        return [
            'flag'      => false,
            'errorCode' => $this->repositoryCode + 4,
        ];
    }

    public function deleteAccount($id, $forceFlag=false)
    {
        try {
            $account = $this->getAccount($id, [], false);

            //force delete or soft delete
            //related records will be deleted by deleting event handlers
            $forceFlag ? $account->forceDelete() : $account->delete();
            
            return [
                'flag'  => true,
                'force' => $forceFlag,
            ];
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ?  $e->getCode() : $this->repositoryCode + 5);
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return [
            'flag'      => false,
            'errorCode' => $this->repositoryCode + 6,
        ];
    }
}
