<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Class DealStoreDataLogic
 * @package App\Http\Logic\DealDataLogic
 */
class Deal_store_data_logic
{
    private $domain_id;
    private $store_id;
    private $query;
    protected $cal_key_fields;

    //是否分页
    protected $pageable = false;
    //Pages
    protected $offset = 1;
    //Limit
    protected $limit = 20;
    //排序Key
    protected $order_key;
    //排序方式：'ASC','DESC'
    protected $order_seq;

    //统计字段名称1
    protected $cal1_keys = array(
        'CLS' => 'Clicks',
        'ROB' => 'Clicks_robot',
        'ROP' => 'Clicks_robot_p',
        'ORD' => 0,
        'SAL' => 'Sales',
        'CMS' => 'Commission',
        'SRV' => 0,
    );

    public function get_calculation_data($type = 'STORE')
    {
        if ($type != 'STORE') {
            return false;
        }
        $this->get_filter_condition();
        $this->do_init_cal_key_fields();
        if (!$this->query instanceof Builder) {
            return false;
        }
        if (!empty($this->cal_key_fields)) {
            $this->query->select(
                \Illuminate\Database\Query\Expression('`ID` AS `StoreId`'),
                \Illuminate\Database\Query\Expression($this->cal_key_fields)
            );
        }

        $this->query->groupBy('ID');
        $pageable = $this->is_pageable();
        if (!empty($pageable)) {
            $this->query->take($this->get_limit())->skip($this->get_offset() * $this->get_limit());
        }

        return $this->query->get();
    }
    
    public function __construct($entity)
    {
        $model = new Store();
        $this->query = $model::query();
        if ($entity instanceof Basic_entity)
        {
            //设置storeIds编号
            !$entity->is_empty('store_ids') && $this->setStoreId($entity->store_ids);
            //设置domainIds编号(无storeIds备用方案)
            $entity->is_empty('store_ids') && !$entity->is_empty('domain_ids') && $this->set_domain_id($entity->domain_ids);

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
     * @param mixed $store_id
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

    /**
     * @return int
     */
    public function get_offset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function set_offset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function get_limit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function set_limit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return boolean
     */
    public function is_pageable()
    {
        return $this->pageable;
    }

    /**
     * @param boolean $pageable
     */
    public function set_pageable($pageable)
    {
        $this->pageable = $pageable;
    }

    /**
     * @return mixed
     */
    public function get_order_key()
    {
        return $this->order_key;
    }

    /**
     * @param mixed $order_key
     */
    public function set_order_key($order_key)
    {
        $this->order_key = $order_key;
    }

    /**
     * @return mixed
     */
    public function get_order_seq()
    {
        return $this->orderSeq;
    }

    /**
     * @param mixed $order_seq
     */
    public function set_order_seq($order_seq)
    {
        $this->order_seq = $order_seq;
    }




    public function getFilterCondition()
    {
        if ($this->query instanceof \Illuminate\Database\Eloquent\Builder) {
            $store_id = $this->get_store_id();
            if (!empty($store_id)) {
                $this->query->where(function ($query) use ($store_id) {
                    $sids = array_chunk($store_id, 100);
                    foreach ($sids as $sid) {
                        $query->orWhereIn('ID',$sid);
                    }
                });
            }
            $domain_id = $this->get_domain_id();
            if (!empty($domain_id)) {
                $this->query->whereHas('domains', function ($query) use ($domain_id) {
                    $dids = array_chunk($domain_id, 100);
                    foreach ($dids as $did) {
                        $query->whereIn('DomainId',$did);
                    }
                });
            }
        }
    }


    protected function do_init_cal_key_fields()
    {
        $cal_key_fields_array = array();
        foreach ($this->cal1_keys as $cal_key => $cal_value) {
            array_push($cal_key_fields_array, 'SUM(' . $cal_value . ') AS `' . $cal_key . '`');
        }

        if (empty($cal_key_fields_array)) {
            return false;
        }
        $this->cal_key_fields = implode(',', $cal_key_fields_array);

        return true;
    }
}