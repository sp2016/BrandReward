<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:29
 */
class Deal_link_data_logic extends Deal_basic_data_logic
{
    private $link_id;
    private $country;

    protected $allow_cal_type = array('DATE' => 'createddate','SITE' => 'site','LINK' => 'linkid', 'COUNTRY' => 'country');


    public function __construct($entity)
    {
        parent::__construct($entity);
        $model = new  StaticsLink();
        $this->query = $model::query();
        if ($entity instanceof Basic_entity)
        {
            //设置affiliateId编号
            !$entity->is_empty('link_ids') && $this->set_link_id($entity->link_ids);
            //设置国家编号
            !$entity->is_empty('countries') && $this->set_country($entity->countries);
        }
    }
    
    /**
     * @return mixed
     */
    public function get_link_id()
    {
        return $this->link_id;
    }

    /**
     * @param mixed $link_id
     */
    public function set_link_id($link_id)
    {
        $this->link_id = $link_id;
    }

    /**
     * @return mixed
     */
    public function get_country()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function set_country($country)
    {
        $this->country = $country;
    }


    public function get_filter_condition()
    {
        parent::get_filter_condition();
        if ($this->query instanceof \Illuminate\Database\Eloquent\Builder) {
            $countries = $this->get_country();
            if (!empty($countries)) {
                $this->query->whereIn('country',$countries);
            }
            $link_id = $this->get_link_id();
            if (!empty($link_id)) {
                $this->query->where(function ($query) use ($link_id) {
                    $lids = array_chunk($link_id, 100);
                    foreach ($lids as $lid) {
                        $query->orWhereIn('linkid',$lid);
                    }
                });
            }
        }
    }
    
}