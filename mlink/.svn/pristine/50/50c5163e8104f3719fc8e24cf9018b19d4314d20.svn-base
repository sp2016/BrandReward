<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Calculation_logic
 * @description 处理传入参数的统一处理 & 产生数据源结结构
 */
class Calculation_logic
{

    //查询方式(M:月度，D:N天）
    protected $query_type;

    //时间参数
    protected $date_type;
    protected $start_date;
    protected $end_date;
    //程序ids
    protected $program_ids;
    //域名ids
    protected $domain_ids;
    //联盟ids
    protected $affiliate_ids;
    //商家ids
    protected $store_ids;
    //国家codes
    protected $countries;
    //链接ids
    protected $link_ids;


    CONST DEAL_AFFILIATE_DOMAIN_LOGIC = 'Deal_affiliate_domain_logic';

    CONST DEAL_DATA_LOGIC = 'Deal_data_logic';

    CONST DEAL_DOMAIN_DATA_LOGIC = 'Deal_domain_data_logic';

    CONST DEAL_LINK_DATA_LOGIC = 'Deal_link_data_logic';

    CONST DEAL_PROGRAM_DATA_LOGIC = 'Deal_program_data_logic';
    
    CONST DEAL_STORE_DATA_LOGIC = 'Deal_store_data_logic';
    
    CONST DEAL_DATA_DAILY_LOGIC = 'Deal_data_daily_logic';
    
    CONST DEAL_DATA_MONTH_LOGIC = 'Deal_data_month_logic';
    
    public function __construct($entity)
    {
        if ($entity instanceof Basic_entity)
        {
            //设置查询方式
            !$entity->is_empty('query_type') ? $this->set_query_type($entity->query_type) : $this->set_query_type('D');
            //设置查询日期类型;
            !$entity->is_empty('date_type') ? $this->set_date_type($entity->date_type) : $this->set_date_type(1);
            //设置开始时间
            !$entity->is_empty('start_date') && $this->set_start_date($entity->start_date);
            //设置结束时间
            !$entity->is_empty('end_date') && $this->set_end_date($entity->end_date);
            //设置domain_ids编号
            !$entity->is_empty('domain_ids') && $this->set_domain_ids($entity->domain_ids);
            //设置affiliate_ids编号
            !$entity->is_empty('affiliate_ids') && $this->set_affiliate_ids($entity->affiliate_ids);
            //设置store_ids的编号
            !$entity->is_empty('store_ids') && $this->set_store_ids($entity->store_ids);
            //设置countries
            !$entity->is_empty('countries') &&  $this->set_countries($entity->countries);
            //设置link_ids
            !$entity->is_empty('link_ids') &&  $this->set_link_ids($entity->link_ids);
        }
    }

    /**
     * @return mixed
     */
    public function get_query_type()
    {
        return $this->query_type;
    }

    /**
     * @param mixed $query_type
     */
    public function set_query_type($query_type)
    {
        $this->query_type = $query_type;
    }

    
    
    /**
     * @return mixed
     */
    public function get_link_ids()
    {
        return $this->link_ids;
    }

    /**
     * @param mixed $link_ids
     */
    public function set_link_ids($link_ids)
    {
        $this->link_ids = $link_ids;
    }


    /**
     * @return mixed
     */
    public function get_start_date()
    {
        return $this->start_date;
    }

    /**
     * @param mixed $start_date
     */
    public function set_start_date($start_date)
    {
        $this->start_date = $start_date;
    }

    /**
     * @return mixed
     */
    public function get_end_date()
    {
        return $this->end_date;
    }

    /**
     * @param mixed $end_date
     */
    public function set_end_date($end_date)
    {
        $this->end_date = $end_date;
    }

    /**
     * @return mixed
     */
    public function get_program_ids()
    {
        return $this->program_ids;
    }

    /**
     * @param mixed $program_ids
     */
    public function set_program_ids($program_ids)
    {
        $this->program_ids = $program_ids;
    }

    /**
     * @return mixed
     */
    public function get_domain_ids()
    {
        return $this->domain_ids;
    }

    /**
     * @param mixed $domain_ids
     */
    public function set_domain_ids($domain_ids)
    {
        $this->domain_ids = $domain_ids;
    }

    /**
     * @return mixed
     */
    public function get_affiliate_ids()
    {
        return $this->affiliate_ids;
    }

    /**
     * @param mixed $affiliate_ids
     */
    public function set_affiliate_ids($affiliate_ids)
    {
        $this->affiliate_ids = $affiliate_ids;
    }

    /**
     * @return mixed
     */
    public function get_date_type()
    {
        return $this->date_type;
    }

    /**
     * @param mixed $date_type
     */
    public function set_date_type($date_type)
    {
        $this->date_type = $date_type;
    }

    /**
     * @return mixed
     */
    public function get_store_ids()
    {
        return $this->store_ids;
    }

    /**
     * @param mixed $store_ids
     */
    public function set_store_ids($store_ids)
    {
        $this->store_ids = $store_ids;
    }

    /**
     * @return mixed
     */
    public function get_countries()
    {
        return $this->countries;
    }

    /**
     * @param mixed $countries
     */
    public function set_countries($countries)
    {
        $this->countries = $countries;
    }
    
    public function __get($name)
    {
       return $this->$name;
    }

    public function is_empty($key = '')
    {
        $value = $this->$key;
        if (empty($value)) {
            return true;
        }
        
        return false;
    }
    
    
    public function get_instance($entity = null)
    {
        $cal_logic_name = $this->get_cal_logic_name();
        
        return new $cal_logic_name($entity);
    }
    
}