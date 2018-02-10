<?php
namespace App\Http\Logic;
use App\Model\WfAfflilate;

/**
 * Class CategoryLogic
 * @package App\Http\Logic
 */
class AfflilateLogic extends BasicLogic
{
    public function getAfflilates()
    {
        $model = new WfAfflilate();
        return $model::query()->where('isactive','YES')->orderBy('Name','ASC')->lists('Name','ID');
    }
    
}