<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Summary_logic
{
    public function prefix_entity($entity = null)
    {
        $query_entity = new Summary_query_entity();
        if ($entity instanceof Basic_entity)
        {
            if (!$entity->is_empty('time_type'))
            {
                //只能选其一
                $date_type = $entity->time_type;
                switch ($date_type)
                {
                    case 'CLICK' :
                        $query_entity->merge(['date_type' => 2]);
                        break;
                    case 'TRANSACTION' :
                        $query_entity->merge(['date_type' => 1]);
                        break;
                }
            }
            if (!$entity->is_empty('start_date'))
            {
                $query_entity->merge(['start_date' => $entity->start_date]);
            }

            if (!$entity->is_empty('end_date'))
            {
                $query_entity->merge(['end_date' => $entity->end_date]);
            }

            if (!$entity->is_empty('adv_id'))
            {
                $adv_id = $entity->adv_id;
                is_string($adv_id) && $adv_id = json_decode($adv_id,true);
                $adv_id = is_array($adv_id) ? $adv_id : array($adv_id);
                $query_entity->merge(['store_ids' => $adv_id]);
            }
            if (!$entity->is_empty('ntw_id'))
            {
                $ntw_id = $entity->ntw_id;
                $query_entity->merge(['affiliate_ids' => is_array($ntw_id) ? $ntw_id : array($ntw_id)]);
            }

            if (!$entity->is_empty('pub_id'))
            {
                $pub_id = $entity->pub_id;
                is_string($pub_id) && $pub_id = json_decode($pub_id,true);
                $pub_id = is_array($pub_id) ? $pub_id : array($pub_id);

                $p_model = new PublisherAccount();
                $p_query = $p_model::query();
                $site_ids = $p_query->whereIn('PublisherId', $pub_id)->lists('ApiKey');
                $query_entity->merge(['site' => $site_ids]);
                //$query_entity->merge(['publisher_ids' => $pub_id]);
            }

            if (!$entity->is_empty('site_id'))
            {
                $site_id = $entity->site_id;
                $site_id = is_array($site_id) ? $site_id : array($site_id);
                $site_ids = $query_entity->is_empty('site') ? $site_id : array_merge(
                    $site_id,$query_entity->site
                );
                $query_entity->merge(['site' => $site_ids]);
            }

            if (!$entity->is_empty('link_id'))
            {
                $link_id = $entity->link_id;
                $query_entity->merge(['link_ids' => is_array($link_id) ? $link_id : array($link_id)]);
            }

            if (!$entity->is_empty('user_country'))
            {
                $user_country = $entity->user_country;
                $query_entity->merge(['countries' => is_array($user_country) ? $user_country : array($user_country)]);
            }
            
            //默认传入：Publisher All
            if (!$entity->is_empty('data_type'))
            {
                //只能选其一
                switch (strtoupper($entity->data_type))
                {
                    case 'ALL' :
                        $query_entity->merge(['data_type' => 1]);
                        break;
                    case 'PUBLISHER' :
                        $query_entity->merge(['data_type' => 2]);
                        break;
                }
            }

            if (!$entity->is_empty('store_type'))
            {
                $query_entity->merge(['store_type' => ucfirst(strtolower($entity->store_type))]);
            }

            if (!$entity->is_empty('publisher_type'))
            {
                $query_entity->merge(['publisher_type' => ucfirst(strtolower($entity->publisher_type))]);
            }
            
            if (!$entity->is_empty('store_network_status'))
            {
                $store_network_status = strtoupper($entity->store_network_status);
                in_array($store_network_status, array('YES','NO')) && $query_entity->merge(['store_network_status' =>$store_network_status]);
            }

            if (!$entity->is_empty('store_picture_status'))
            {
                $store_picture_status = strtoupper($entity->store_picture_status);
                in_array($store_picture_status, array('YES','NO')) && $query_entity->merge(['store_picture_status' =>$store_picture_status]);
            }

            if (!$entity->is_empty('store_ppc_status'))
            {
                $query_entity->merge(['store_ppc_status' => ucfirst(strtolower($entity->store_ppc_status))]);
            }

            if (!$entity->is_empty('store_name_status'))
            {
                $store_name_status = strtoupper($entity->store_name_status);
                in_array($store_name_status, array('YES','NO')) && $query_entity->merge(['store_name_status' =>$store_name_status]);
            }
            
        }
        
        return $query_entity;
    }
}