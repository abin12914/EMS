<?php

namespace App\Repositories;

use App\Models\User;
use Exception;
use App\Exceptions\AppCustomException;

class UserRepository
{
    public $repositoryCode, $errorCode = 0;

    public function __construct()
    {
        $this->repositoryCode = config('settings.repository_code.UserRepository');
    }

    /**
     * Action for updating user profile
     */
    public function updateProfile($inputArray=[], $user=[])
    {
        $saveFlag = false;

        try {
            $user->username = $inputArray['username'];
            $user->name     = $inputArray['name'];
            $user->email    = $inputArray['email'];
            
            if(!empty($inputArray['password'])) {
                $user->password = $inputArray['password'];
            }

            $user->save();
            $saveFlag = true;
        } catch(Exception $e) {
            if($e->getMessage() == "CustomError") {
                $this->errorCode = $e->getCode();
            } else {
                $this->errorCode = $this->repositoryCode + 1;
            }
            
            throw new AppCustomException("CustomError", $this->errorCode);
        }

        if($saveFlag) {
            return [
                'flag'  => true,
            ];
        }
        return [
            'flag'      => false,
            'errorCode' => $this->repositoryCode,
        ];
    }
}
