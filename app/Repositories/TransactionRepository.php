<?php

namespace App\Repositories;

use App\Models\Transaction;
use Auth;
use Exception;
use App\Exceptions\AppCustomException;

class TransactionRepository
{
    public $repositoryCode, $errorCode = 0, $transactionRelations=[], $loop = 0;

    public function __construct()
    {
        $this->repositoryCode       = config('settings.repository_code.TransactionRepository');
        $this->transactionRelations = config('constants.transactionRelations');
    }
    /**
     * Return transactions.
     */
    public function getTransactions(
        $whereParams=[],
        $orWhereParams=[],
        $relationalParams=[],
        $orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],
        $withParams=[],
        $relation,
        $activeFlag=true
    ){
        $transactions = [];

        try {
            if(empty($withParams)) {
                $transactions = Transaction::active();
            } else {
                $transactions = Transaction::with($withParams);
            }

            if($activeFlag) {
                $transactions = $transactions->active(); //status == 1
            }

            foreach ((array)$whereParams as $param) {
                if(!empty($param['paramValue'])) {
                    $transactions = $transactions->where($param['paramName'], $param['paramOperator'], $param['paramValue']);
                }
            }

            $this->loop = 0;
            $transactions = $transactions->where(function ($query) use($transactions, $orWhereParams) {
                foreach ((array)$orWhereParams as $orParam) {
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
                    $transactions = $transactions->whereHas($relationalParam['relation'], function($qry) use($relationalParam) {
                        $qry->where($relationalParam['paramName'], $relationalParam['paramOperator'], $relationalParam['paramValue']);
                    });
                }
            }

            //has relation checking
            if(!empty($relation)) {
                $transactions = $transactions->has($this->transactionRelations[$relation]['relationName']);
            }

            if(!empty($orderBy['num'])) {
                if($orderBy['num'] == 1) {
                    $transactions = $transactions->firstOrFail();
                } else {
                    $transactions = $transactions->orderBy($orderBy['by'], $orderBy['order'])->paginate($orderBy['num']);
                }
            } else {
                $transactions= $transactions->orderBy($orderBy['by'], $orderBy['order'])->get();
            }
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 1);

            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $transactions;
    }

    /**
     * return account.
     */
    public function getTransaction($id, $withParams=[], $activeFlag=true)
    {
        $transaction = [];

        try {
            if(empty($withParams)) {
                $transaction = Transaction::query();
            } else {
                $transaction = Transaction::with($withParams);
            }
            
            if($activeFlag) {
                $transaction = $transaction->active();
            }

            $transaction = $transaction->findOrFail($id);
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 4);
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $transaction;
    }

    /**
     * Action for saving transaction.
     */
    public function saveTransaction($inputArray, $id=null)
    {
        try {
            //find first record or create new if none exist
            $transaction = Transaction::findOrNew($id);

            foreach ($inputArray as $key => $value) {
                $transaction->$key = $value;
            }
            //transaction save
            $transaction->save();

            return [
                'flag'        => true,
                'transaction' => $transaction,
            ];
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 3);

            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return [
            'flag'      => false,
            'errorCode' => $repositoryCode + 4,
        ];
    }

    public function deleteTransaction($id, $forceFlag=false)
    {
        try {
            $transaction = $this->getTransaction($id, [], false);

            //force delete or soft delete
            //related records will be deleted by deleting event handlers
            $forceFlag ? $transaction->forceDelete() : $transaction->delete();
            
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
