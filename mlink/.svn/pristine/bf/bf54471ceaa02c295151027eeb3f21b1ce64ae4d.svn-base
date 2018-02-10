<?php
namespace App\Http\Controllers;

use App\Http\Entity\Statistics\StatisticsQueryEntity;
use App\Http\Entity\Statistics\StoreStaticsQueryEntity;
use App\Http\Entity\Store\StoreEntity;
use App\Http\Logic\CalculationLogic\DateCalculationLogic;
use App\Http\Logic\CalculationLogic\StoreCalculationLogic;
use App\Http\Logic\StoreLogic;
use App\Model\Publisher;
use Illuminate\Http\Request;

/**
 * Class StoreController
 * @package App\Http\Controllers
 *          
 */
class StoreController extends Controller
{
    /**
     * @description : 商家列表功能能
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getStores(Request $request)
    {
        $entity = new  StoreEntity($request->all());
        $storeLogic = new StoreLogic();
        //商家数据(单页数据:主体数据一)
        //有效商家数据
        $actives = $storeLogic->getActiveStoreIds($entity->startDate,$entity->endDate);
        //click商家数据
        $clicks = $storeLogic->getClicksStoreIds($entity->startDate,$entity->endDate);
        if (!$entity->isEmpty('display'))
        {
            $display = $entity->display;
            switch ($display) {
                case 'Active' :
                    //查询规定时间内有效的商家列表
                    $entity->merge(['storeIds' => $actives]);
                    break;
                case 'Clicks' :
                    //查询规定的时间内有流量的商家列表
                    $entity->merge(['storeIds' => $clicks]);
                    break;
            }
        }
        $stores = $storeLogic->getStores($entity);
        //符合查询条件的全部商家数量
        $total = $storeLogic->countStores($entity);
        //规定时间内有效的商家数量
        $aEntity = $entity;
        $aEntity->merge(['storeIds' => $actives]);
        $activeTotal = $storeLogic->countStores($aEntity);
        //规定时间内有clicks的商家数量
        $cEntity = $entity;
        $cEntity->merge(['storeIds' => $clicks]);
        $clicksTotal = $storeLogic->countStores($cEntity);
        //当前全部的商家数量
        $bEntity = $entity;
        $bEntity->offsetUnset('storeIds');
        $allTotal = $storeLogic->countStores($bEntity);
        //统计数据：获取商家domain列表
        $domains = array();
        foreach ($stores as $store) {
            $domain = $store->domains()->lists('ID');
            !empty($domain) && $domains = array_merge($domains,$domain);
        }
        //统计数据(单页数据:主体数据二)
        $staticsEntity = new StatisticsQueryEntity();
        !$entity->isEmpty('dateType') && $staticsEntity->set('dateType', $entity->dateType);
        !$entity->isEmpty('startDate') && $staticsEntity->set('startDate', $entity->startDate);
        !$entity->isEmpty('endDate') && $staticsEntity->set('endDate', $entity->endDate);
        $storeIds = $stores->lists('ID');
        !empty($storeIds) ? $staticsEntity->set('storeIds',$storeIds) : $staticsEntity->set('storeIds',array(-1));
        !empty($domains) ? $staticsEntity->set('domainIds',$domains) : $staticsEntity->set('domainIds',array(-1));
        $staticsEntity->set('countries',$entity->shippingCountry);
        $staticsEntity->set('affiliateIds',$entity->network);
        if(!$entity->isEmpty('dataType')) {
            $dataType = $entity->dataType;
            if ($dataType == 1) {
                $pModel = new Publisher();
                $exceptSites = $pModel::query()
                    ->leftJoin('publisher_account','publisher_account.PublisherId','=', 'publisher.ID')
                    ->where('publisher.Tax','=',0)
                    ->whereNotNull('publisher_account.ApiKey')
                    ->lists('publisher_account.ApiKey');
                $staticsEntity->set('exceptSite', $exceptSites);
            }
        }
        //获取匹配统计逻辑
        $storeCalLogic = new StoreCalculationLogic($staticsEntity);
        $calLogic = $storeCalLogic->getInstance($staticsEntity);
        //执行查询操作
        $data = $calLogic->getCalculationData('STORE');
        $sData = array();
        foreach ($stores as $store) {
            $item = !empty($data) ? $data->where('StoreId', $store->ID)->pop() : false;
            $rcls = !empty($item) ? $item->CLS - $item->ROB : 0;
            $cls = !empty($item) ? $item->CLS : 0;
            $rob = !empty($item) ? $item->ROB : 0;
            $rop = !empty($item) ? $item->ROP : 0;
            $sal = !empty($item) ? $item->SAL : 0;
            $cms  = !empty($item) ? $item->CMS : 0;
            array_set(
                $sData,
                $store->ID,
                array_merge(
                    $store->toArray(),
                    array(
                        'StoreId' => $store->ID,
                        'RCLS'    => number_format($rcls),
                        'CLS'     => number_format($cls),
                        'ROB'     => number_format($rob),
                        'ROP'     => number_format($rop),
                        'ORD'     => !empty($item) ? number_format($item->ORD) : 0,
                        'SAL'     => "$" .number_format($sal, 2, '.', ','),
                        'CMS'     => "$" .number_format($cms, 2, '.', ','),
                        'SRV'     => !empty($item) ? number_format($item->SRV, 2) : 0,
                        'EPC'     => "$" . number_format($rcls > 0 ? $cms / $rcls : 0.00, 2, '.', ','),
                        'CPC'     => $store->coupons()->where('status', 'active')->count(), //商家促销的数量
                        'CCR'     => empty($storeLogic->getCommissionRates($store->ID)) ? '-' : $storeLogic->getCommissionRates($store->ID),
                    )
                )
            );
        }
        //汇总数据(从日期数据中获取:主体数据三)
        //注意点:若有查询特定商家，不能清空domainIds
        if ($entity->isEmpty('name')) {
            $staticsEntity->offsetUnset('domainIds');
        } else {
            //全部商家数据(不分页)
            $pEntity = $entity;
            $pEntity->merge(['paginate',false]);
            $pStores = $storeLogic->getStores($pEntity);
            $pStoreIds = $pStores->lists('ID');
            !empty($pStoreIds) ? $staticsEntity->set('storeIds',$pStoreIds) : $staticsEntity->set('storeIds',array(-1));
            $pDomains = array();
            foreach ($pStores as $pStore) {
                $domain = $pStore->domains()->lists('ID');
                !empty($domain) && $pDomains = array_merge($pDomains,$domain);
            }
            !empty($pDomains) ? $staticsEntity->set('domainIds',$pDomains) : $staticsEntity->set('domainIds',array(-1));
        }
        if (!$staticsEntity->isEmpty('startDate') || !$staticsEntity->isEmpty('endDate'))
        {
            //A:若有查询时间范围的策略
            //执行查询操作
            $dateCalLogic = new DateCalculationLogic($staticsEntity);
            $dateCalLogicObj = $dateCalLogic->getInstance($staticsEntity);
            $dateData = $dateCalLogicObj->getCalculationData('DATE');
        } else {
            //B.若无查询时间范围的策略
            $storeCalLogicObj = $storeCalLogic->getInstance($staticsEntity);
            //执行查询操作
            $dateData = $storeCalLogicObj->getCalculationData('STORE');
        }

        $rdata = [
            'total'     => $total,//用于分页
            'activeTotal' => $activeTotal,//规定时间区间有效的商家数量（符合条件)
            'clicksTotal' => $clicksTotal,//规定时间区间有click的商家数量（符合条件)
            'allTotal'   => $allTotal,//当前全部商家数量（符合条件)
            'offset' => ceil($total / $entity->limit),
            'page'      => $entity->offset,
            'pageSize'  => $entity->limit,
            'data'      => array_values($sData),
            'totalData' => [
                'RCLS'    => number_format($dateData->sum('CLS') - $dateData->sum('ROB')),
                'CLS'     => number_format($dateData->sum('CLS')),
                'ROB'     => number_format($dateData->sum('ROB')),
                'ROP'     => number_format($dateData->sum('ROP')),
                'ORD'     => number_format($dateData->sum('ORD')),
                'SAL'     => "$".number_format($dateData->sum('SAL'),2),
                'CMS'     => "$".number_format($dateData->sum('CMS'),2),
                'SRV'     => "$".number_format($dateData->sum('SRV'),2),
            ]
        ];
        
        return response()->json($rdata);

    }


    public function getStoreDomains(Request $request)
    {
        $storeId = $request->get('storeId');
    }
}