<?php

namespace App\Repositories;

use App\Models\ExcavatorRent;
use Exception;
use App\Exceptions\AppCustomException;

class ExcavatorRentRepository extends Repository
{
    public $repositoryCode, $errorCode = 0;

    public function __construct()
    {
        $this->repositoryCode = config('settings.repository_code.ExcavatorRentRepository');
    }

    /**
     * Return excavatorRents.
     */
    public function getExcavatorRents(
        $whereParams=[],
        $orWhereParams=[],
        $relationalParams=[],
        $orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],
        $aggregates=['key' => null, 'value' => null],
        $withParams=[],
        $activeFlag=true
    ){
        $excavatorRents = [];

        try {
            $excavatorRents = empty($withParams) ? ExcavatorRent::query() : ExcavatorRent::with($withParams);

            $excavatorRents = $activeFlag ? $excavatorRents->active() : $excavatorRents;

            $excavatorRents = parent::whereFilter($excavatorRents, $whereParams);

            $excavatorRents = parent::orWhereFilter($excavatorRents, $orWhereParams);

            $excavatorRents = parent::relationalFilter($excavatorRents, $relationalParams);

            return (!empty($aggregates['key']) ? parent::aggregatesSwitch($excavatorRents, $aggregates) : parent::getFilter($excavatorRents, $orderBy));
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 1);

            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $excavatorRents;
    }

    /**
     * return excavatorRent.
     */
    public function getExcavatorRent($id, $withParams=[], $activeFlag=true)
    {
        $excavatorRent = [];

        try {
            if(empty($withParams)) {
                $excavatorRent = ExcavatorRent::query();
            } else {
                $excavatorRent = ExcavatorRent::with($withParams);
            }
            
            if($activeFlag) {
                $excavatorRent = $excavatorRent->active();
            }

            $excavatorRent = $excavatorRent->findOrFail($id);
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 2);

            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $excavatorRent;
    }

    /**
     * Action for excavatorRent save.
     */
    public function saveExcavatorRent($inputArray=[], $id=null)
    {
        try {
            //find record with id or create new if none exist
            $excavatorRent = ExcavatorRent::findOrNew($id);

            foreach ($inputArray as $key => $value) {
                $excavatorRent->$key = $value;
            }
            //excavatorRent save
            $excavatorRent->save();

            return [
                'flag'    => true,
                'excavatorRent' => $excavatorRent,
            ];
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 3);
dd($e);
            throw new AppCustomException("CustomError", $this->errorCode);
        }
        return [
            'flag'      => false,
            'errorCode' => $this->repositoryCode + 3,
        ];
    }

    public function deleteExcavatorRent($id, $forceFlag=false)
    {
        try {
            //get excavatorRent
            $excavatorRent = $this->getExcavatorRent($id, [], false);

            //force delete or soft delete
            //related models will be deleted by deleting event handlers
            $forceFlag ? $excavatorRent->forceDelete() : $excavatorRent->delete();
            
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
