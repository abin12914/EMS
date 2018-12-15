<?php

namespace App\Repositories;

use App\Models\EmployeeWage;
use Exception;
use App\Exceptions\AppCustomException;

class EmployeeWageRepository
{
    public $repositoryCode, $errorCode = 0, $loop = 0;

    public function __construct()
    {
        $this->repositoryCode = config('settings.repository_code.EmployeeWageRepository');
    }

    /**
     * Return employeeWages.
     */
    public function getEmployeeWages(
        $whereParams=[],
        $orWhereParams=[],
        $relationalParams=[],
        $orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],
        $withParams=[],
        $activeFlag=true
    ){
        $employeeWages = [];

        try {
            if(empty($withParams)) {
                $employeeWages = EmployeeWage::query();
            } else {
                $employeeWages = EmployeeWage::with($withParams);
            }

            if($activeFlag) {
                $employeeWages = $employeeWages->active(); //status == 1
            }

            foreach ((array)$whereParams as $param) {
                if(!empty($param['paramValue'])) {
                    $employeeWages = $employeeWages->where($param['paramName'], $param['paramOperator'], $param['paramValue']);
                }
            }

            $this->loop = 0;
            $employeeWages = $employeeWages->where(function ($query) use($employeeWages, $orWhereParams){
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
                    $employeeWages = $employeeWages->whereHas($relationalParam['relation'], function($qry) use($relationalParam) {
                        $qry->where($relationalParam['paramName'], $relationalParam['paramOperator'], $relationalParam['paramValue']);
                    });
                };
            }

            if(!empty($orderBy['num'])) {
                if($orderBy['num'] == 1) {
                    $employeeWages = $employeeWages->firstOrFail();
                } else {
                    $employeeWages = $employeeWages->orderBy($orderBy['by'], $orderBy['order'])->paginate($orderBy['num']);
                }
            } else {
                $employeeWages= $employeeWages->orderBy($orderBy['by'], $orderBy['order'])->get();
            }
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 1);
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $employeeWages;
    }

    /**
     * return employeeWage.
     */
    public function getEmployeeWage($id, $withParams=[], $activeFlag=true)
    {
        $employeeWage = [];

        try {
            if(empty($withParams)) {
                $employeeWage = EmployeeWage::query();
            } else {
                $employeeWage = EmployeeWage::with($withParams);
            }
            
            if($activeFlag) {
                $employeeWage = $employeeWage->active();
            }

            $employeeWage = $employeeWage->findOrFail($id);
        } catch (Exception $e) {
            if($e->getMessage() == "CustomError") {
                $this->errorCode = $e->getCode();
            } else {
                $this->errorCode = $this->repositoryCode + 3;
            }
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $employeeWage;
    }

    /**
     * Action for saving employeeWages.
     */
    public function saveEmployeeWage($inputArray, $id=null)
    {
        $saveFlag   = false;

        try {
            //find record with id or create new if none exist
            $employeeWage = EmployeeWage::findOrNew($id);

            foreach ($inputArray as $key => $value) {
                $employeeWage->$key = $value;
            }
            //employeeWage save
            $employeeWage->save();

            return [
                'flag'    => true,
                'employeeWage' => $employeeWage,
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

    public function deleteEmployeeWage($id, $forceFlag=false)
    {
        try {
            $employeeWage = $this->getEmployeeWage($id, [], false);

            //force delete or soft delete
            //related records will be deleted by deleting event handlers
            $forceFlag ? $employeeWage->forceDelete() : $employeeWage->delete();
            
            return [
                'flag'  => true,
                'force' => $forceFlag,
            ];
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ?  $e->getCode() : $this->repositoryCode + 5);
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }
        return [
            'flag'          => false,
            'errorCode'    => $this->repositoryCode + 6,
        ];
    }
}
