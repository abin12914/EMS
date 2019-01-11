<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\ExcavatorRepository;
use Exception;

class ExcavatorComponentComposer
{
    protected $excavators = [];

    /**
     * Create a new excavators partial composer.
     *
     * @param  ExcavatorRepository  $excavators
     * @return void
     */
    public function __construct(ExcavatorRepository $excavatorRepo)
    {
        try {
            //getExcavators($whereParams=[],$orWhereParams=[],$relationalParams=[],$orderBy=['by' => 'id', 'order' => 'asc', 'num' => null],$aggregates=['key' => null, 'value' => null],$withParams=[],$activeFlag=true)
            $this->excavators = $excavatorRepo->getExcavators([], [],  [], $orderBy=['by' => 'id', 'order' => 'asc', 'num' => null], $aggregates=['key' => null, 'value' => null], $withParams=[], $activeFlag=true);
        } catch (Exception $e) {
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
        $view->with(['excavatorsCombo' => $this->excavators]);
    }
}