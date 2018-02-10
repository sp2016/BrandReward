<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:25
 */
class Deal_domain_data_logic extends Deal_basic_data_logic
{
    private $domain_id;
    private $store_id;

    protected $allow_cal_type = array('DATE' => 'createddate','SITE' => 'site','DOMAIN' => 'domainId', 'STORE' => 'storeId');

    public function __construct($entity)
    {
        parent::__construct($entity);
        $model = new  StaticsDomainBr();
        $this->query = $model::query();
        if ($entity instanceof BasicEntity)
        {
            //设置domainIds编号
            !$entity->is_empty('domain_ids') && $this->set_domain_id($entity->domain_ids);
            //设置storeIds编号
            !$entity->is_empty('store_ids') && $this->set_store_id($entity->store_ids);
        }
    }

    /**
     * @return mixed
     */
    public function get_store_id()
    {
        return $this->store_id;
    }

    /**
     * @param mixed $storeId
     */
    public function set_store_id($store_id)
    {
        if (!is_array($store_id)) {
            $store_id = array($store_id);
        }
        $this->store_id = $store_id;
    }

    /**
     * @return mixed
     */
    public function get_domain_id()
    {
        return $this->domain_id;
    }

    /**
     * @param mixed $domain_id
     */
    public function set_domain_id($domain_id)
    {
        if (!is_array($domain_id)) {
            $domain_id = array($domain_id);
        }
        $this->domain_id = $domain_id;
    }


    public function get_filter_condition()
    {
        parent::get_filter_condition();
        if ($this->query instanceof Builder) {
            $storeId = $this->get_store_id();
            if (!empty($storeId)) {
                $this->query->whereIn('StoreId',$storeId);
            }
            $domain_id = $this->get_domain_id();
            if (!empty($domain_id)) {
                $this->query->where(function ($query) use ($domain_id) {
                    $dids = array_chunk($domain_id, 100);
                    foreach ($dids as $did) {
                        $query->orWhereIn('domainid',$did);
                    }
                });
            }
        }
    }
    

}