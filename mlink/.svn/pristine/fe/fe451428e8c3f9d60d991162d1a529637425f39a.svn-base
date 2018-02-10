<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_logic
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
                        $subQuery->orWhere('ProductName','LIKE','%' . $keyword . '%');
                        $subQuery->orWhere('ProductDesc','LIKE','%' . $keyword . '%');
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
            if (!$entity->is_empty('pdt_title'))
            {
                $title = $entity->pdt_title;
                $query->where('ProductName', 'LIKE', '%' . $title . '%');
            }

            if (!$entity->is_empty('pdt_desc'))
            {
                $desc = $entity->pdt_desc;
                $query->where('ProductDesc', 'LIKE', '%' . $desc . '%');
            }

            !$entity->is_empty('pdt_id') && $query->where('ID',$entity->pdt_id);
            !$entity->is_empty('pdt_linkid') && $query->where('EncodeId',$entity->pdt_linkid);
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

            if (!$entity->is_empty('pdt_country')) {
                $query->where(function($query) use($entity)
                {
                    $countries = $entity->pdt_country;
                    !is_array($countries) && $countries = array($countries);
                    $rawSql = array();
                    foreach ($countries as $country) {
                        !empty($country) && array_push($rawSql, " FIND_IN_SET('" . $country . "',Country) ");
                    }
                    !empty($rawSql) && $query->whereRaw(implode(' OR ', $rawSql));
                });

            }
            !$entity->is_empty('pdt_source') && $query->whereIn('Source',$entity->pdt_source);
            !$entity->is_empty('pdt_lang') && $query->whereIn('Language',$entity->pdt_lang);
        }
    }


    public function get_products($entity)
    {
        if ($entity instanceof Basic_entity)
        {
            $model = new Product();
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
    }

    public function get_product_total($entity)
    {
        if ($entity instanceof Basic_entity)
        {
            $model = new Product();
            $query = $model::query();
            if ($entity instanceof Basic_entity)
            {
                $this->prefix_query($query,$entity);
                return $query->count();
            }
        }
        
        return 0;
    }
}