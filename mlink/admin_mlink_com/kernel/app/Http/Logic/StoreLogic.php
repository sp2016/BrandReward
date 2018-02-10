<?php
namespace App\Http\Logic;

use App\Http\Entity\Store\StoreEntity;
use App\Model\StaticsDomainBr;
use App\Model\Store;
use App\Model\StoreProgramHistory;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class StoreLogic
 * @package App\Http\Logic
 */
class StoreLogic extends BasicLogic
{
    private function prefixQuery($query,$entity)
    {
        if ($entity instanceof StoreEntity && $query instanceof Builder) {
            if (!$entity->isEmpty('name'))
            {
                $name = $entity->name;
                $query->where(
                    function ($query) use ($name) {
                        $query->orWhere('Name','LIKE', '%' . $name . '%');
                        $query->orWhere('Domains','LIKE', '%' . $name . '%');
                        $query->orWhere('NameOptimized','LIKE', '%' . $name . '%');
                        $query->orderBy('LENGTH(b.`Name`)','ASC');
                        $query->orderBy('LENGTH(b.`NameOptimized`)','ASC');
                        $query->orderBy('LENGTH(b.`Domains`)','ASC');
                    }
                );
            }

            if (!$entity->isEmpty('categories'))
            {
                $categories = $entity->categories;
                $query->where(
                    function ($query) use ($categories) {
                        $rawSql = array();
                        foreach ($categories as $category) {
                            !empty($category) && array_push($rawSql, ' FIND_IN_SET(' . $category . ',CategoryId) ');
                        }
                        !empty($rawSql) && $query->whereRaw(implode(' OR ', $rawSql));
                    }
                );

            }

            if (!$entity->isEmpty('categoryStatus'))
            {
                $categoryStatus = $entity->categoryStatus;
                switch ($categoryStatus) {
                    case 'YES' :
                        $query->whereNotNull('CategoryId')->where('CategoryId','<>','');
                        break;
                    case 'NO' :
                        $query->whereRaw("( CategoryId IS NULL OR NameOptimized = '')");
                        break;
                }
            }

            if (!$entity->isEmpty('ppcStatus'))
            {
                $ppcStatus = $entity->ppcStatus;
                $query->where('PPCStatus','=',$ppcStatus);
            }
            if (!$entity->isEmpty('nameStatus')) {
                $nameStatus = $entity->nameStatus;

                switch ($nameStatus) {
                    case 'YES' :
                        $query->where('NameOptimized','<>','');
                        break;
                    case 'NO' :
                        $query->whereRaw("( NameOptimized IS NULL OR NameOptimized = '')");
                        break;
                }

            }

            if (!$entity->isEmpty('logoStatus'))
            {
                $logoStatus = $entity->logoStatus;
                switch ($logoStatus) {
                    case 'YES' :
                        $query->where('LogoName','LIKE','%,%');
                        break;
                    case 'NO' :
                        $query->whereRaw("( LogoName = '' OR LogoName IS NULL)");
                        break;
                }
            }

            if (!$entity->isEmpty('shippingCountry'))
            {
                $shippingCountry = $entity->shippingCountry;
                $query->where(
                    function ($query) use ($shippingCountry) {
                        $rawSql = array();
                        foreach ($shippingCountry as $country) {
                            !empty($country) && array_push($rawSql, " FIND_IN_SET('" . $country . "',CountryCode) ");
                        }
                        !empty($rawSql) && $query->whereRaw(implode(' OR ', $rawSql));
                    }
                );
            }

            if (!$entity->isEmpty('currentStatus'))
            {
                $currentStatus = $entity->currentStatus;
                $query->where('SupportType',$currentStatus);
            }

            if (!$entity->isEmpty('order')) {
                $order = $entity->order;
                if (is_array($order)) {
                    foreach ($order as $ok => $ov) {
                        $query->orderBy($ok,$ov);
                    }
                }
            }

            if (!$entity->isEmpty('network'))
            {
                $networks = $entity->network;
                $query->where(
                    function ($query) use ($networks) {
                        $rawSql = array();
                        foreach ($networks as $network) {
                            !empty($network) && array_push($rawSql, ' FIND_IN_SET(' . $network . ',Affids) ');
                        }
                        !empty($rawSql) && $query->whereRaw(implode(' OR ', $rawSql));
                    }
                );
            }

            if (!$entity->isEmpty('cooperationStatus'))
            {
                $cooperationStatus = $entity->cooperationStatus;
                $query->where('StoreAffSupport','=',$cooperationStatus);
            }

            if (!$entity->isEmpty('storeIds'))
            {
                $storeIds = $entity->storeIds;
                if (is_array($storeIds)) {
                    $query->where(
                        function ($query) use ($storeIds) {
                            $chunkStores = array_chunk($storeIds, 100);
                            foreach ($chunkStores as $stores) {
                                $query->orWhereIn('ID',$stores);
                            }
                        }
                    );
                }
            }
        }

        return $query;
    }

    public function getStores($entity)
    {
        if ($entity instanceof StoreEntity) 
        {
            $model = new Store();
            $query = $model::query();
            $query = $this->prefixQuery($query, $entity);
            if (!$entity->isEmpty('paginate')) {
                $query->take($entity->limit)->skip(($entity->offset-1)* $entity->limit);
            }
            $rs = $query->get(array('*'));

            return $rs;
        }
    }

    public function countStores($entity)
    {
        if ($entity instanceof StoreEntity)
        {
            $model = new Store();
            $query = $model::query();
            $query = $this->prefixQuery($query, $entity);
            
            return $query->count();
        }
    }


    public function currencyFix($name = '')
    {
        $cf = array(
            'USD' => '$',
            'CNY' => '¥',
            'GBP' => '£',
            'EUR' => '€',
            'BRL' => 'R$',
            'INR' => 'Rs.',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'CHF' => '₣',
            'ZAR' => 'R',
            'DHS' => 'د.إ',
        );

        return isset($cf[$name]) ? $cf[$name] : '$';
    }

    public function getCommissionRates($storeId = 0)
    {
        $ccs = false;

        if (!empty($storeId)) {
            $smodel = new Store();
            $store = $smodel::query()->find($storeId);
            $programs = $store->programs()->wherePivot('Outbound','<>','')->get();
            //中间数组1:获得初始数据
            $ccsa = array();
            foreach ($programs as $program) {
                $itl = $program->intell()->first();
                if (!empty($itl)) {
                    $prefix = !empty($itl->CommissionCurrency) ? $this->currencyFix($itl->CommissionCurrency) : '';
                    $subfix = $itl->CommissionType != 'Percent' ? '' : '%';
                    $type = $itl->CommissionType;
                    !empty($type) && array_set($ccsa, $type, array_merge(array_get($ccsa, $type, array()), array($prefix . $itl->CommissionUsed . $subfix)));
                }
            }
            //中间数据2；分组求极值
            $ccst = array();
            foreach ($ccsa as $type => $cc) {
                if (max($cc) != min($cc)) {
                    array_set($ccst, $type, ['max' => max($cc), 'min' => min($cc)]);
                } else {
                    array_set($ccst, $type, min($cc));
                }
            }
            //目标数据
            $ccs = array();
            foreach ($ccst as $type =>$cmx) {
                if (is_array($cmx)) {
                    array_push($ccs, $cmx['min'] . '~' . $cmx['max']);
                } else {
                    array_push($ccs, $cmx);
                }
            }


        }

        return empty($ccs) ? '/' : implode(',', $ccs);
    }
    
    public function getActiveStoreIds($sDate = 0, $eDate = 0)
    {
        if (empty($sDate) && empty($eDate)) {
            return false;
        }

        $sph = new StoreProgramHistory();
        $query = $sph::query();
        if (!empty($sDate)) {
            $query->where('enddate','>', $sDate);
        }
        if (!empty($eDate)) {
            $query->where('startdate','<', $eDate);
        }

        return $query->distinct()->lists('storeid');
    }
    
    
    public function getClicksStoreIds($sDate = 0, $eDate = 0)
    {
        if (empty($sDate) && empty($eDate)) {
            return false;
        }
        $sdm = new StaticsDomainBr();
        $query = $sdm::query();
        if (!empty($sDate)) {
            $query->where('createddate','>', $sDate);
        }
        if (!empty($eDate)) {
            $query->where('createddate','<', $eDate);
        }

        return $query->distinct()->lists('storeId');
    }
    
    public function getCalculationData($entity)
    {
        
    }
    
    
}