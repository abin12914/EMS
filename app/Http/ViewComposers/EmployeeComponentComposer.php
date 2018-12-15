<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\EmployeeRepository;
use Exception;
//use App\Exceptions\AppCustomException;

class EmployeeComponentComposer
{
    protected $employees = [];//, $errorHead = null;

    /**
     * Create a new employees partial composer.
     *
     * @param  EmployeeRepository  $employees
     * @return void
     */
    public function __construct(EmployeeRepository $employeeRepo)
    {
        $errorCode          = 0;
        $this->errorHead    = config('settings.composer_code.EmployeeComponentComposer');

        try {
            //getEmployees($whereParams=[],$orWhereParams=[],$relationalParams=[],$orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],$aggregates=['key' => null, 'value' => null],$withParams=[],$activeFlag=true)
            $this->employees = $employeeRepo->getEmployees([], [],  [], $orderBy=['by' => 'id', 'order' => 'asc', 'num' => null], $aggregates=['key' => null, 'value' => null], $withParams=[], $activeFlag=true);
        } catch (Exception $e) {
            //$errorCode = (($e->getMessage() == "CustomError") ? $e->getCode() : 1);
            
            //throw new AppCustomException("CustomError", ($this->errorHead + $errorCode));
        }
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with(['employeesCombo' => $this->employees]);
    }
}