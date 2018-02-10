<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:24
 */
class Deal_data_logic extends Deal_basic_data_logic
{
    private $affiliate_id;
    private $program_id;
    private $domain_id;
    private $country;
    
    protected $allow_cal_type = array('ALL' => '','DATE' => 'createddate','SITE' => 'site', 'AFFILIATE' => 'affId','DOMAIN' => 'domainid', 'PROGRAM' => 'programid', 'COUNTRY' => 'country');

    public function __construct($entity)
    {
        parent::__construct($entity);
        $model = new  StaticsBr();
        $this->query = $model::query();
        if ($entity instanceof Basic_entity)
        {
            //设置domain_ids编号
            !$entity->is_empty('domain_ids') && $this->set_domain_id($entity->domain_ids);
            //设置programId编号
            !$entity->is_empty('program_ids') && $this->set_program_id($entity->program_ids);
            //设置affiliateId编号
            !$entity->is_empty('affiliate_ids') && $this->set_affiliate_id($entity->affiliate_ids);
            //设置国家编号
            !$entity->is_empty('countries') && $this->set_country($entity->countries);
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

    /**
     * @return mixed
     */
    public function get_program_id()
    {
        return $this->program_id;
    }

    /**
     * @param mixed $program_id
     */
    public function set_program_id($program_id)
    {
        $this->program_id = $program_id;
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
        $this->domain_id = $domain_id;
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

    public function check_cal_type($type)
    {
        $is_allow = parent::check_cal_type($type);
        if (empty($is_allow)) {
            switch ($type) {
                case 'STORE' :
                    $this->query->leftJoin('r_store_domain', 'statis_br.domainid', '=', 'r_store_domain.DomainId');
                    array_set($this->allow_cal_type, 'STORE', 'r_store_domain.StoreId');
                    break;
            }
        }
        return true;
    }

    public function get_filter_condition()
    {
        parent::get_filter_condition();
        if ($this->query instanceof \Illuminate\Database\Eloquent\Builder) {
            $countries = $this->get_country();
            if (!empty($countries)) {
                $this->query->whereIn('country',$countries);
            }
            $affiliate_id = $this->get_affiliate_id();
            if (!empty($affiliate_id)) {
                $this->query->whereIn('affid',$affiliate_id);
            }

            $domain_id = $this->get_domain_id();
            if (!empty($domain_id)) {
                $this->query->where(function ($query) use ($domain_id) {
                    $dids = array_chunk($domain_id, 100);
                    foreach ($dids as $did) {
                        $query->orWhereIn('statis_br.domainid',$did);
                    }
                });
            }
            $program_id = $this->get_program_id();
            if (!empty($program_id)) {
                $this->query->where(function ($query) use ($program_id) {
                    $pids = array_chunk($program_id, 100);
                    foreach ($pids as $pid) {
                        $query->orWhereIn('statis_br.programid',$pid);
                    }
                });
            }
        }
    }

}