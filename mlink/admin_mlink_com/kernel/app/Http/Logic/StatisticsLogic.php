<?php
namespace App\Http\Logic;

use App\Http\Entity\Entity;
use App\Model\CategoryStd;
use App\Model\StatisAffiliateBr;

class StatisticsLogic extends BasicLogic
{
    /**
     * @return mixed
     */
    public function getCategory()
    {
        $model = new CategoryStd();
        return $model::query()->orderBy('Name','ASC')->lists('Name','ID');

    }


    public function getSiteDateStatistics(Entity $entity)
    {
        $startDate = $entity->get('startDate');
        $endDate = $entity->get('endDate');
        $publisherType = $entity->get('publisherType');
        
        $model = new StatisAffiliateBr();
        $model::query()->where('createddate','b')->groupBy('createddate');
    }
    
}