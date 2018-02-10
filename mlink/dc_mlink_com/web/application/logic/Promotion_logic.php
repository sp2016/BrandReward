<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promotion_logic
{

    public function prefix_query($query = null,$entity = null)
    {
        if ($entity instanceof Basic_entity &&  $query instanceof \Illuminate\Database\Eloquent\Builder)
        {
            if (!$entity->is_empty('keyword'))
            {
                $keyword = $entity->keyword;
                $query->where(
                    function ($subQuery) use($keyword)
                    {
                        $subQuery->orWhere('CouponCode','LIKE','%' . $keyword . '%');
                        $subQuery->orWhere('Title','LIKE','%' . $keyword . '%');
                        $subQuery->orWhere('Desc','LIKE','%' . $keyword . '%');
                        $subQuery->orWhereHas('store',
                            function ($storeQuery) use ($keyword) {
                                $storeQuery->where(
                                    function ($storeQuery) use ($keyword) {
                                        $storeQuery->orWhere('Name', 'LIKE', '%' . $keyword . '%');
                                        $storeQuery->orWhere('NameOptimized', 'LIKE', '%' . $keyword . '%');
                                    }
                                );
                            }
                        );
                    }
                );
            }
            if (!$entity->is_empty('promo_title'))
            {
                $title = $entity->promo_title;
                $query->where('Title', 'LIKE', '%' . $title . '%');
            }

            if (!$entity->is_empty('promo_couponcode'))
            {
                $coupon_code = $entity->promo_couponcode;
                $query->where('CouponCode', 'LIKE', '%' . $coupon_code . '%');
            }
            !$entity->is_empty('promo_id') && $query->where('ID',$entity->promo_id);
            !$entity->is_empty('promo_linkid') && $query->where('EncodeId',$entity->promo_linkid);
            !$entity->is_empty('adv_id') && $query->where('StoreId', $entity->adv_id);
            if (!$entity->is_empty('ntw_id'))
            {
                $ntw_id = $entity->ntw_id;
                $query->whereHas(
                    'program',
                    function ($subQuery) use($ntw_id){
                        $subQuery->where('AffId', $ntw_id);
                    }
                );
            }
            !$entity->is_empty('promo_period_from') && $query->where('EndDate','>=',$entity->promo_period_from);
            !$entity->is_empty('promo_period_to') && $query->where('StartDate','<=',$entity->promo_period_to);
            
            !$entity->is_empty('promo_type') && $query->whereIn('Type',$entity->promo_type);
            if (!$entity->is_empty('promo_country')) {
                $query->where(function($query) use($entity)
                {
                    $countries = $entity->promo_country;
                    $rawSql = array();
                    foreach ($countries as $country) {
                        !empty($country) && array_push($rawSql, " FIND_IN_SET('" . $country . "',country) ");
                    }
                    !empty($rawSql) && $query->whereRaw(implode(' OR ', $rawSql));
                });

            }
            !$entity->is_empty('promo_source') && $query->whereIn('source',$entity->promo_source);
            !$entity->is_empty('promo_lang') && $query->whereIn('language',$entity->promo_lang);

            if (!$entity->is_empty('promo_status'))
            {
                $promo_status = $entity->promo_status;
                $query->where(function($query) use($promo_status)
                {
                    foreach ($promo_status as $p_status)
                    {
                        switch (strtoupper($p_status))
                        {
                            case 'OFFLINE' :
                                $query->orWhere(function($query) {
                                    $query->orWhere('EndDate', '<', date('Y-m-d'));
                                    $query->orWhere('StartDate', '>', date('Y-m-d'));
                                    $query->orwhere('EndDate','=','0000-00-00 00:00:00');
                                });
                                break;
                            case 'ONLINE' :
                                $query->orWhere(function($query) {
                                    $query->where('StartDate','<=',date('Y-m-d'));
                                    $query->where('EndDate','>=',date('Y-m-d'));
                                });
                                break;
                            case 'ONGOING' :
                                $query->orWhere(function($query){
                                    $query->orWhere(function($query) {
                                        $query->where('StartDate','<=',date('Y-m-d H:i:s'));
                                        $query->where('EndDate','>=',date('Y-m-d H:i:s'));
                                    });
                                    $query->orWhere(function($query) {
                                        $query->where('EndDate','>=',date('Y-m-d H:i:s'));
                                        $query->where('StartDate','=','0000-00-00 00:00:00');
                                    });
                                    $query->orWhere(function($query) {
                                        $query->where('EndDate','=','0000-00-00 00:00:00');
                                        $query->where('StartDate','<=',date('Y-m-d H:i:s'));
                                    });
                                });
                                break;
                            case 'NOTSTART' :
                                $query->orWhere('StartDate','>',date('Y-m-d H:i:s'));
                                break;
                            case 'END' :
                                $query->orWhere(function($query) {
                                    $query->where('EndDate','<',date('Y-m-d H:i:s'));
                                    $query->where('EndDate','<>','0000-00-00 00:00:00');
                                });
                                break;
                        }
                    }

                });

            }
        }
    }

    public function get_promotions($entity)
    {
        $model = new Coupon();
        $query = $model::query();
        if ($entity instanceof Basic_entity)
        {
            $this->prefix_query($query,$entity);
            if (!$entity->is_empty('paginate')) {
                $query->take($entity->limit)->skip(($entity->offset-1)* $entity->limit);
            }
            
            return $query->get(array('*'));
        }
    }

    public function get_promotion_total($entity)
    {
        $model = new Coupon();
        $query = $model::query();
        if ($entity instanceof Basic_entity)
        {
            $this->prefix_query($query,$entity);
           
            return $query->count();
        }
        
        return 0;   
    }
}