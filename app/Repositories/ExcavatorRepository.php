<?php

namespace App\Repositories;

use App\Models\Excavator;
use Exception;
use App\Exceptions\AppCustomException;

class ExcavatorRepository extends Repository
{
    public $repositoryCode, $errorCode = 0;

    public function __construct()
    {
        $this->repositoryCode = config('settings.repository_code.ExcavatorRepository');
    }

    /**
     * Return excavators.
     */
    public function getExcavators(
        $whereParams=[],
        $orWhereParams=[],
        $relationalParams=[],
        $orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],
        $aggregates=['key' => null, 'value' => null],
        $withParams=[],
        $activeFlag=true
    ){
        $excavators = [];

        try {
            $excavators = empty($withParams) ? Excavator::query() : Excavator::with($withParams);

            $excavators = $activeFlag ? $excavators->active() : $excavators;

            $excavators = parent::whereFilter($excavators, $whereParams);

            $excavators = parent::orWhereFilter($excavators, $orWhereParams);

            $excavators = parent::relationalFilter($excavators, $relationalParams);

            return (!empty($aggregates['key']) ? parent::aggregatesSwitch($excavators, $aggregates): parent::getFilter($excavators, $orderBy));
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 1);
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $excavators;
    }

    /**
     * return excavator.
     */
    public function getExcavator($id, $withParams=[], $activeFlag=true)
    {
        $excavator = [];

        try {
            if(empty($withParams)) {
                $excavator = Excavator::query();
            } else {
                $excavator = Excavator::with($withParams);
            }
            
            if($activeFlag) {
                $excavator = $excavator->active();
            }

            $excavator = $excavator->findOrFail($id);
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 2);

            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $excavator;
    }

    /**
     * Action for saving excavators.
     */
    public function saveExcavator($inputArray=[], $id=null)
    {
        try {
            //find record with id or create new if none exist
            $excavator = Excavator::findOrNew($id);

            foreach ($inputArray as $key => $value) {
                $excavator->$key = $value;
            }
            //excavator save
            $excavator->save();

            return [
                'flag'    => true,
                'excavator' => $excavator,
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

    public function deleteExcavator($id, $forceFlag=false)
    {
        try {
            $excavator = $this->getExcavator($id, [], false);

            //force delete or soft delete
            //related records will be deleted by deleting event handlers
            $forceFlag ? $excavator->forceDelete() : $excavator->delete();
            
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
