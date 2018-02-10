<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Outbound_log_logic
{
    private function prefix_query($query = null,$entity = null)
    {
        if ($entity instanceof Basic_entity && $query instanceof \Illuminate\Database\Eloquent\Builder)
        {
            if (!$entity->is_empty('start_date'))
            {
                $query->where('createddate', '>=', $entity->start_date);
            }
            if (!$entity->is_empty('end_date'))
            {
                $query->where('createddate', '<=', $entity->end_date);
            }
            //查询商家列表
            $store_model = new Store();
            //商家查询条件标记
            $store_query_bool = false;
            $store_query = $store_model::query();
            if (!$entity->is_empty('adv_support_sitetype'))
            {
                $store_query_bool = true;
                $adv_support_sitetype = $entity->adv_support_sitetype;
                !is_array($adv_support_sitetype) && $adv_support_sitetype = array($adv_support_sitetype);
                $store_query->whereIn('SupportType',$adv_support_sitetype);
            }
            if (!$entity->is_empty('adv_id'))
            {
                $store_query_bool = true;
                $adv_id = $entity->adv_id;
                !is_array($adv_id) && $adv_id = array($adv_id);
                $store_query->whereIn('ID',$adv_id);
            }
            if (!$entity->is_empty('adv_valid_country'))
            {
                $store_query_bool = true;
                $adv_valid_country = $entity->adv_valid_country;
                $store_query->where(
                    function ($query) use ($adv_valid_country) {
                        $rawSql = array();
                        foreach ($adv_valid_country as $country) {
                            !empty($country) && array_push($rawSql, " FIND_IN_SET('" . $country . "',CountryCode) ");
                        }
                        !empty($rawSql) && $query->whereRaw(implode(' OR ', $rawSql));
                    }
                );
            }
            if (!empty($store_query_bool))
            {
                $stores = $store_query->get(array('*'));
                $domains = array();
                foreach ($stores as $store) {
                    $domain = $store->domains()->lists('ID');
                    !empty($domain) && $domains = array_merge($domains, $domain);
                }
                $query->where(
                    function ($subQuery) use ($domains) {
                        $domains_chunk = array_chunk($domains, 100);
                        foreach ($domains_chunk as $c_domain) {
                            $subQuery->orWhereIn('domainId',$c_domain);
                        }
                    }
                );

            }
            //发布者查询条件标记
            $publisher_query_bool = false;
            $publisher_model = new Publisher();
            $publisher_query = $publisher_model::query();
            if (!$entity->is_empty('pub_id'))
            {
                $publisher_query_bool = true;
                $pub_id = $entity->pub_id;
                !is_array($pub_id) && $pub_id = array($pub_id);
                $publisher_query->whereIn('ID',$pub_id);
            }
            if (!$entity->is_empty('pub_sitetype'))
            {
                $publisher_query_bool = true;
                $pub_sitetype = $entity->pub_sitetype;
                !is_array($pub_sitetype) && $pub_sitetype = array($pub_sitetype);
                $publisher_query->whereIn('SiteOption',$pub_sitetype);
            }
            if (!$entity->is_empty('pub_type'))
            {
                $publisher_query_bool = true;
                switch (strtoupper($entity->pub_type)) {
                    case 'MK' :
                        $publisher_query->where(
                            function ($query) {
                                $query->orWhere('ID', '<=', 10);
                                $query->orWhereIn('ID', array(90692, 54, 432));
                            }
                        );
                        break;
                    case 'BR' :
                        $publisher_query->where('ID', '>', 10);
                        $publisher_query->whereNotIn('ID', array(90692, 54, 432));
                        break;
                }
            }

            if (!empty($publisher_query_bool))
            {
                $publisher_ids = $publisher_query->lists('ID');
                $query->whereHas('publisherAccount',
                    function ($subQuery) use ($publisher_ids) {
                        if (empty($publisher_ids)) {
                            $subQuery->whereIn('PublisherId',$publisher_ids);
                        } else {
                            $publisher_id_chunk = array_chunk($publisher_ids, 100);
                            foreach ($publisher_id_chunk as $p_site_id) {
                                $subQuery->orWhereIn('PublisherId',$p_site_id);
                            }
                        }

                    }
                );
            }

            if (!$entity->is_empty('site_id'))
            {
                $site_id = $entity->site_id;
                !is_array($site_id) && $site_id = array($site_id);
                $query->whereHas('publisherAccount',
                    function ($subQuery) use ($site_id) {
                        $site_id_chunk = array_chunk($site_id, 100);
                        foreach ($site_id_chunk as $c_site_id) {
                            $subQuery->orWhereIn('ID',$c_site_id);
                        }
                    }
                );
            }
            if (!$entity->is_empty('link_id'))
            {
                $link_id = $entity->link_id;
                !is_array($link_id) && $link_id = array($link_id);
                $query->where(
                    function ($subQuery) use ($link_id) {
                        $link_id_chunk = array_chunk($link_id, 100);
                        foreach ($link_id_chunk as $c_link_id) {
                            $subQuery->orWhereIn('linkId',$c_link_id);
                        }
                    }
                );
            }
            if (!$entity->is_empty('ntw_id'))
            {
                $ntw_id = $entity->ntw_id;
                !is_array($ntw_id) && $ntw_id = array($ntw_id);
                $query->where(
                    function ($subQuery) use ($ntw_id) {
                        $ntw_id_chunk = array_chunk($ntw_id, 100);
                        foreach ($ntw_id_chunk as $c_ntw_id) {
                            $subQuery->orWhereIn('affId',$c_ntw_id);
                        }
                    }
                );
            }
            if (!$entity->is_empty('click_country'))
            {
                $click_country = $entity->click_country;
                !is_array($click_country) && $click_country = array($click_country);
                $query->where(
                    function ($subQuery) use ($click_country) {
                        $click_country_chunk = array_chunk($click_country, 100);
                        foreach ($click_country_chunk as $c_click_country) {
                            $subQuery->orWhereIn('country',$c_click_country);
                        }
                    }
                );
            }
        }

        return $query;
    }

    public function get_logs($entity = null)
    {
        $model = new OutboundLogMin();
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



    public function get_logs_total($entity = null)
    {
        $model = new OutboundLogMin();
        $query = $model::query();
        if ($entity instanceof Basic_entity)
        {
            $this->prefix_query($query,$entity);
           
            return $query->count();
        }
        
        return 0;
    }
}