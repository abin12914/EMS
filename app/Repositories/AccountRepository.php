<?php

namespace App\Repositories;

use App\Models\Account;
use Exception;
use App\Exceptions\AppCustomException;

class AccountRepository
{
    public $repositoryCode, $errorCode = 0;

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

            foreach ($whereParams as $param) {
                $accounts = $accounts->when($param['paramValue'], function ($query) use($param) {
                    return $query->where($param['paramName'], $param['paramOperator'], $param['paramValue']);
                });
            }

            $keyCount = 0;
            $accounts = $accounts->where(function ($query) {
                foreach ($orWhereParams as $orParam) {
                    $accounts = $accounts->when($orParam['paramValue'], function ($query) use($orParam) {
                        if($keyCount == 0) {
                            return $query->where($orParam['paramName'], $orParam['paramOperator'], $orParam['paramValue']);
                        } else {
                            return $query->orWhere($orParam['paramName'], $orParam['paramOperator'], $orParam['paramValue']);
                        }
                        $keyCount ++;
                    });
                }
            });

            foreach ($relationalParams as $relationalParam) {
                $accounts = $accounts->when($relationalParam['paramValue'], function ($query) use($relationalParam) {
                    $query->whereHas($relationalParam['relation'], function($qry) use($relationalParam) {
                        return $qry->where($relationalParam['paramName'], $relationalParam['paramOperator'], $relationalParam['paramValue']);
                    });
                });
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
            //find first record or create new if none exist
            $account = Account::firstOrNew(['id' => $id]);

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
