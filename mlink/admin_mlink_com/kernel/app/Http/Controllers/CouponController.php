<?php namespace App\Http\Controllers;
use App\Http\Entity\Coupon\CouponQueryEntity;
use App\Http\Logic\AfflilateLogic;
use App\Http\Logic\CountryLogic;
use App\Http\Logic\PublisherLogic;
use App\Http\Logic\StatisticsLogic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller {
    /**
     * @description 新增促销
     * @param Request $request
     */
    public function add(Request $request)
    {
        $entity = new CouponQueryEntity($request->all());
        
        return $entity;
    }

    /**
     * @description 修改促销
     * @param Request $request
     */
    public function update(Request $request)
    {
        
    }

    /**
     * @description 基础公用数据
     */
    public function common()
    {
        $data = array();
        $publishers = (new PublisherLogic())->getPublisherList();
        array_set($data, 'publishers', $publishers);

        $categories = (new StatisticsLogic())->getCategory();
        array_set($data, 'categories', $categories);
        $countryCodes = (new CountryLogic())->getCountryCode();
        array_set($countryCodes, 'global', 'Global');
        array_set($countryCodes, 'United Kingdom', 'UK');
        array_set($data, 'countryCodes', $countryCodes);

        $afflilates = (new AfflilateLogic())->getAfflilates();
        array_set($afflilates,'-1', 'Other');
        array_set($data, 'afflilates', $afflilates);

        return new JsonResponse($data);
    }

    /**
     * @description 获取促销列表
     */
    public function getCoupons(Request $request)
    {
        $entity = new CouponQueryEntity($request->all());
        var_dump($entity);DIE();
        return new  JsonResponse($entity);
//     
//        $DomainTotal = $merchant->GetContentNew($search, $page, $pagesize);
//        $DomainList = $DomainTotal['data'];
//        if (!empty($DomainList)) {
//            foreach ($DomainList as &$v) {
//                if ($v['StartDate'] == '0000-00-00 00:00:00') {
//                    $v['StartDate'] = 'N/A';
//                } else {
//                    $v['StartDate'] = date('Y-m-d', strtotime($v['StartDate']));
//                }
//                if ($v['EndDate'] == '0000-00-00 00:00:00') {
//                    $v['EndDate'] = 'N/A';
//                } else {
//                    $v['EndDate'] = date('Y-m-d', strtotime($v['EndDate']));
//                }
//                $v['AddTime'] = date('Y-m-d', strtotime($v['AddTime']));
//                if ($v['Type'] == 'Promotion') {
//                    //No Code Needed
//                    $v['CouponCode'] = '';
//                }
//                if (!empty($v['AffUrl'])) {
//                    $v['LinkUrl'] = $v['AffUrl'];
//                } else {
//                    $v['LinkUrl'] = $v['OriginalUrl'];
//                }
//            }
//        }
//        $res['clicks'] = $DomainTotal['clicks'];
//        $res['rclicks'] = $DomainTotal['rclicks'];
//        $res['rob'] = $DomainTotal['rob'];
//        $res['robp'] = $DomainTotal['robp'];
//        $res['orders'] = $DomainTotal['orders'];
//        $res['sales'] = "$" . $DomainTotal['sales'];
//        $res['commission'] = "$" . $DomainTotal['commission'];
//        $res['data'] = $DomainList;
//        $res['start'] = $page / $pagesize + 1;
//        $res['recordsFiltered'] = $DomainTotal['count'];
    }
}