<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:25
 */
class Deal_affiliate_domain_logic extends Deal_basic_data_logic
{
    private $affiliate_id;

    protected $allow_cal_type = array('DATE' => 'createddate','SITE' => 'site', 'AFFILIATE' => 'affId');

    public function __construct($entity)
    {
        parent::__construct($entity);
        $model = new  StaticsAffiliateBr();
        $this->query = $model::query();
        if ($entity instanceof Basic_entity)
        {
            //设置affiliateId编号
            !$entity->is_empty('affiliate_ids') && $this->set_affiliate_id($entity->affiliate_ids);
        }
    }
    
    /**
     * @return mixed
     */
    public function get_affiliate_id()
    {
        return $this->affiliate_id;
    }

    /**
     * @param mixed $affiliate_id
     */
    public function set_affiliate_id($affiliate_id)
    {
        $this->affiliate_id = $affiliate_id;
    }
    

    public function get_filter_condition()
    {
        parent::get_filter_condition();
        $affiliate = $this->get_affiliate_id();
        if (!empty($affiliate)) {
            !is_array($affiliate) && $affiliate = array($affiliate);
            array_push($this->filter, array('affId', 'IN', $affiliate));
        }
    }
    
}