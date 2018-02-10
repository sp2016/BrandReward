<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Network_logic
{
    private function prefix_query($query = null,$entity = null)
    {
        if ($entity instanceof Basic_entity &&  $query instanceof \Illuminate\Database\Eloquent\Builder)
        {
            if (!$entity->is_empty('ntw_name'))
            {
                $query->where('Name', 'LIKE', '%' . $entity->ntw_name . '%');
            }
            if (!$entity->is_empty('ntw_domain'))
            {
                $query->where('Domain', 'LIKE', '%' . $entity->ntw_name . '%');
            }
            if (!$entity->is_empty('ntw_type'))
            {
                $query->where('IsInHouse', $entity->ntw_type);
            }
            if (!$entity->is_empty('ntw_level'))
            {
                $query->where('Level', $entity->ntw_level);
            }
            if (!$entity->is_empty('ntw_isactive'))
            {
                $query->where('IsActive', $entity->ntw_isactive);
            }
            if (!$entity->is_empty('ntw_finance_if_received'))
            {
                $query->where('RevenueReceived', $entity->ntw_finance_if_received);
            }
            if (!$entity->is_empty('crawl_transaction'))
            {
                $query->where('StatsReportCrawled', $entity->crawl_transaction);
            }
            if (!$entity->is_empty('crawl_program'))
            {
                $query->where('ProgramCrawled', $entity->crawl_program);
            }
            if (!$entity->is_empty('fin_id'))
            {
                $query->where('RevenueAccount', $entity->fin_id);
            }
        }
    }


    public function get_networks($entity = null)
    {
        $model = new WfAfflilate();
        $query = $model::query();
        if ($entity instanceof Basic_entity) {
            $this->prefix_query($query, $entity);
            if (!$entity->is_empty('paginate')) {
                $query->take($entity->limit)->skip(($entity->offset-1)* $entity->limit);
            }
            return $query->get(array('*'));
        }
    }

    public function get_network_total($entity = null)
    {
        $model = new WfAfflilate();
        $query = $model::query();
        if ($entity instanceof Basic_entity) {
            $this->prefix_query($query, $entity);
            return $query->count();
        }
        
        return 0;
    }
}