<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_logic
{
    private function prefix_query($query = null, $entity = null)
    {
        if ($entity instanceof Basic_entity && $query instanceof \Illuminate\Database\Eloquent\Builder)
        {
            //时间类型(输入:CLICK,TRANSACTION;输出:Visited，CreatedDate)
            $m_time_type = array('CLICK' => 'Visited', 'TRANSACTION' => 'CreatedDate');
            $e_time_type = !$entity->is_empty('time_type') ? strtoupper($entity->time_type) : false;
            $time_type_key = in_array($e_time_type, array('CLICK', 'TRANSACTION')) ? $e_time_type : 'TRANSACTION';
            $time_type_value = array_get($m_time_type,$time_type_key,'CreatedDate');
            //时间范围
            if (!$entity->is_empty('start_date'))
            {
                $start_date = $entity->start_date;
                $query->where($time_type_value,'>=',$start_date);
            }
            if (!$entity->is_empty('end_date'))
            {
                $end_date = $entity->end_date;
                $query->where($time_type_value,'<=',$end_date);
            }

            if (!$entity->is_empty('adv_id'))
            {
                $adv_id = $entity->adv_id;
                $store_model = new Store();
                $store_query = $store_model::query();
                !is_array($adv_id) && $adv_id = array($adv_id);
                $store_query->whereIn('ID',$adv_id);
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
            
            if (!$entity->is_empty('ntw_id'))
            {
                $ntw_id = $entity->ntw_id;
                !is_array($ntw_id) && $ntw_id = array($ntw_id);
                $query->whereIn('AffId',$ntw_id);
            }

            if (!$entity->is_empty('pub_id'))
            {
                $pub_id = $entity->pub_id;
                !is_array($pub_id) && $pub_id = array($pub_id);
                $query->whereHas(
                    'publisherAccount',
                    function ($query) use ($pub_id)
                    {
                        $query->whereIn('PublisherId',$pub_id);
                    }
                );
            }
            
            if (!$entity->is_empty('site_id'))
            {
                $site_id = $entity->site_id;
                !is_array($site_id) && $site_id = array($site_id);
                $query->whereIn('Site',$site_id);
            }

            if (!$entity->is_empty('link_id'))
            {
                $link_id = $entity->link_id;
                !is_array($link_id) && $link_id = array($link_id);
                $query->whereIn('linkId',$link_id);
            }
            
            
            if (!$entity->is_empty('tran_country'))
            {
                $tran_country = $entity->tran_country;
                !is_array($tran_country) && $tran_country = array($tran_country);
                $query->whereIn('Country',$tran_country);
            }

            if (!$entity->is_empty('tran_state'))
            {
                $tran_state = $entity->tran_state;
                !is_array($tran_state) && $tran_state = array($tran_state);
                $query->whereIn('State',$tran_state);
            }
            //
            if (!$entity->is_empty('tran_sid'))
            {
                $tran_sid = $entity->tran_sid;
                !is_array($tran_sid) && $tran_sid = array($tran_sid);
                $query->whereIn('SID',$tran_sid);
            }

            if (!$entity->is_empty('tran_pub_trackingid'))
            {
                $tran_pub_tracking_id = $entity->tran_pub_trackingid;
                !is_array($tran_pub_tracking_id) && $tran_pub_tracking_id = array($tran_pub_tracking_id);
                $query->whereIn('PublishTracking',$tran_pub_tracking_id);
            }
        }
    }

    public function get_transactions($entity)
    {
        $model = new TransactionUnique();
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

    public function get_transaction_total($entity)
    {
        $model = new TransactionUnique();
        $query = $model::query();
        if ($entity instanceof Basic_entity)
        {
            $this->prefix_query($query,$entity);

            return $query->count();
        }

        return 0;
    }
}