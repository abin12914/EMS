<?php

namespace App\Repositories;

use App\Models\Voucher;
use Exception;
use App\Exceptions\AppCustomException;

class VoucherRepository extends Repository
{
    public $repositoryCode, $errorCode = 0;

    public function __construct()
    {
        $this->repositoryCode = config('settings.repository_code.VoucherRepository');
    }

    /**
     * Return trucks.
     */
    public function getVouchers(
        $whereParams=[],
        $orWhereParams=[],
        $relationalOrParams=[],
        $orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],
        $aggregates=['key' => null, 'value' => null],
        $withParams=[],
        $activeFlag=true
    ){
        $vouchers = [];

        try {
            $vouchers = empty($withParams) ? Voucher::query() : Voucher::with($withParams);

            $vouchers = $activeFlag ? $vouchers->active() : $vouchers;

            $vouchers = parent::whereFilter($vouchers, $whereParams);

            $vouchers = parent::orWhereFilter($vouchers, $orWhereParams);

            $vouchers = parent::relationalOrFilter($vouchers, $relationalOrParams);

            return (!empty($aggregates['key']) ? parent::aggregatesSwitch($vouchers, $aggregates) : parent::getFilter($vouchers, $orderBy));
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 1);
dd($e);
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $vouchers;
    }

    /**
     * Save voucher.
     */
    public function saveVoucher($inputArray=[], $id=null)
    {
        try {
            //find record with id or create new if none exist
            $voucher = Voucher::findOrNew($id);

            foreach ($inputArray as $key => $value) {
                $voucher->$key = $value;
            }
            //voucher save
            $voucher->save();

            return [
                'flag'    => true,
                'voucher' => $voucher,
            ];
        } catch (Exception $e) {
            $this->errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : $this->repositoryCode + 2);
dd($e);
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return [
            'flag'      => false,
            'errorCode' => $this->repositoryCode + 3,
        ];
    }

    /**
     * Return trucks.
     */
    public function getVoucher($id)
    {
        $voucher = [];

        try {
            $voucher = Voucher::active()->findOrFail($id);
        } catch (Exception $e) {
            if($e->getMessage() == "CustomError") {
                $this->errorCode = $e->getCode();
            } else {
                $this->errorCode = $this->repositoryCode + 4;
            }
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        return $voucher;
    }

    /**
     * delete voucher.
     */
    public function deleteVoucher($id, $forceFlag=false)
    {   
        $deleteFlag = false;

        try {
            //get voucher
            $voucher = $this->getVoucher($id);

            //force delete or soft delete
            //related models will be deleted by deleting event handlers
            if($forceFlag) {
                $voucher->forceDelete();
            } else {
                $voucher->delete();
            }
            
            $deleteFlag = true;
        } catch (Exception $e) {
            if($e->getMessage() == "CustomError") {
                $this->errorCode = $e->getCode();
            } else {
                $this->errorCode = $this->repositoryCode + 5;
            }
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        if($deleteFlag) {
            return [
                'flag'  => true,
                'force' => $forceFlag,
            ];
        }

        return [
            'flag'          => false,
            'errorCode'    => $this->repositoryCode + 6,
        ];
    }
}
