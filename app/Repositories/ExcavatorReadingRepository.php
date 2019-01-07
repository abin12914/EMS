<?php

namespace App\Repositories;

use App\Models\ExcavatorReading;
use Exception;
use App\Exceptions\AppCustomException;

class ExcavatorReadingRepository extends Repository
{
    public $repositoryCode, $errorCode = 0;

    public function __construct()
    {
        $this->repositoryCode = config('settings.repository_code.ExcavatorReadingRepository');
    }

    /**
     * Return excavatorReadings.
     */
    public function getExcavatorReadings(
        $whereParams=[],
        $orWhereParams=[],
        $relationalParams=[],
        $orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],
        $aggregates=['key' => null, 'value' => null],
        $withParams=[],
        $activeFlag=true
    ){
        $excavatorReadings = [];

        try {
            $excavatorReadings = empty($withParams) ? ExcavatorReading::query() : ExcavatorReading::with($withParams);

            $excavatorReadings = $activeFlag ? $excavatorReadings->active() : $excavatorReadings;

            $excavatorReadings = parent::whereFilter($excavatorReadings, $whereParams);

            $excavatorReadings = parent::orWhereFilter($excavatorReadings, $orWhereParams);

            $excavatorReadings = parent::relationalFilter($excavatorReadings, $relationalParams);

            return (!empty($aggregates['key']) ? parent::aggregatesSwitch($excavatorReadings, $aggregates) : parent::getFilter($excavatorReadings, $orderBy));
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 1);
dd($e);
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $excavatorReadings;
    }

    /**
     * return excavatorReading.
     */
    public function getExcavatorReading($id, $withParams=[], $activeFlag=true)
    {
        $excavatorReading = [];

        try {
            if(empty($withParams)) {
                $excavatorReading = ExcavatorReading::query();
            } else {
                $excavatorReading = ExcavatorReading::with($withParams);
            }
            
            if($activeFlag) {
                $excavatorReading = $excavatorReading->active();
            }

            $excavatorReading = $excavatorReading->findOrFail($id);
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 2);

            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $excavatorReading;
    }

    /**
     * Action for excavatorReading save.
     */
    public function saveExcavatorReading($inputArray=[], $id=null)
    {
        try {
            //find record with id or create new if none exist
            $excavatorReading = ExcavatorReading::findOrNew($id);

            foreach ($inputArray as $key => $value) {
                $excavatorReading->$key = $value;
            }
            //excavatorReading save
            $excavatorReading->save();

            return [
                'flag'    => true,
                'excavatorReading' => $excavatorReading,
            ];
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 3);

            throw new AppCustomException("CustomError", $this->errorCode);
        }
        return [
            'flag'      => false,
            'errorCode' => $this->repositoryCode + 3,
        ];
    }

    public function deleteExcavatorReading($id, $forceFlag=false)
    {
        try {
            //get excavatorReading
            $excavatorReading = $this->getExcavatorReading($id, [], false);

            //force delete or soft delete
            //related models will be deleted by deleting event handlers
            $forceFlag ? $excavatorReading->forceDelete() : $excavatorReading->delete();
            
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
