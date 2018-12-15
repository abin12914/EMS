<?php

namespace App\Repositories;

use App\Models\Employee;
use Exception;
use App\Exceptions\AppCustomException;

class EmployeeRepository
{
    public $repositoryCode, $errorCode = 0, $loop = 0;

    public function __construct()
    {
        $this->repositoryCode = config('settings.repository_code.EmployeeRepository');
    }

    /**
     * Return employees.
     */
    public function getEmployees(
        $whereParams=[],
        $orWhereParams=[],
        $relationalParams=[],
        $orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],
        $aggregates=['key' => null, 'value' => null],
        $withParams=[],
        $activeFlag=true
    ){
        $employees = [];

        try {
            if(empty($withParams)) {
                $employees = Employee::query();
            } else {
                $employees = Employee::with($withParams);
            }

            if($activeFlag) {
                $employees = $employees->active(); //status == 1
            }

            foreach ((array)$whereParams as $param) {
                if(!empty($param['paramValue'])) {
                    $employees = $employees->where($param['paramName'], $param['paramOperator'], $param['paramValue']);
                }
            }

            $this->loop = 0;
            $employees = $employees->where(function ($query) use($employees, $orWhereParams){
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
                    $employees = $employees->whereHas($relationalParam['relation'], function($qry) use($relationalParam) {
                        $qry->where($relationalParam['paramName'], $relationalParam['paramOperator'], $relationalParam['paramValue']);
                    });
                };
            }

            //if asking aggregates ? return result.
            if(!empty($aggregates['key'])) {
                return $employees->$aggregates['key']($aggregates['value']);
            }
            
            if(!empty($orderBy['num'])) {
                if($orderBy['num'] == 1) {
                    $employees = $employees->firstOrFail();
                } else {
                    $employees = $employees->orderBy($orderBy['by'], $orderBy['order'])->paginate($orderBy['num']);
                }
            } else {
                $employees= $employees->orderBy($orderBy['by'], $orderBy['order'])->get();
            }
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 1);
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $employees;
    }

    /**
     * return employee.
     */
    public function getEmployee($id, $withParams=[], $activeFlag=true)
    {
        $employee = [];

        try {
            if(empty($withParams)) {
                $employee = Employee::query();
            } else {
                $employee = Employee::with($withParams);
            }
            
            if($activeFlag) {
                $employee = $employee->active();
            }

            $employee = $employee->findOrFail($id);
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 2);
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $employee;
    }

    /**
     * Action for saving employees.
     */
    public function saveEmployee($inputArray, $id=null)
    {
        try {
            //find record with id or create new if none exist
            $employee = Employee::findOrNew($id);

            foreach ($inputArray as $key => $value) {
                $employee->$key = $value;
            }
            //employee save
            $employee->save();

            return [
                'flag'     => true,
                'employee' => $employee,
            ];
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 3);
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }
        return [
            'flag'      => false,
            'errorCode' => $repositoryCode + 3,
        ];
    }

    public function deleteEmployee($id, $forceFlag=false)
    {
        $deleteFlag = false;

        try {
            //get employee record
            $employee = $this->getEmployee($id, [], false);

            //force delete or soft delete
            //related records will be deleted by deleting event handlers
            $forceFlag ? $employee->forceDelete() : $employee->delete();

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
            'error_code'    => $this->repositoryCode + 6,
        ];
    }
}
