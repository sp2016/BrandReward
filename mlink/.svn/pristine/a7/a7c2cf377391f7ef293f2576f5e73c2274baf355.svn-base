<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:24
 */
class Deal_data_month_logic extends Deal_basic_data_logic
{
    private $affiliate_id;
    private $store_id;
    private $publisher_id;
    private $country;

    protected $allow_cal_type = array('ALL' => '','PUBLISHER' => 'Site','DATE' => 'createdmonth','SITE' => 'site', 'AFFILIATE' => 'affid','STORE' => 'storeid', 'COUNTRY' => 'country');

    public function __construct($entity)
    {
        parent::__construct($entity);
        $model = new  StaticsBrMonth();
        $this->query = $model::query();
        if ($entity instanceof Basic_entity)
        {
            //store_id
            !$entity->is_empty('store_ids') && $this->set_store_id($entity->store_ids);
            //publisher_id;
            !$entity->is_empty('publisher_id') && $this->set_publisher_id($entity->publisher_id);
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
    public function get_publisher_id()
    {
        return $this->publisher_id;
    }

    /**
     * @param mixed $publisher_id
     */
    public function set_publisher_id($publisher_id)
    {
        $this->publisher_id = $publisher_id;
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
        $this->store_id = $store_id;
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
        if ($this->query instanceof \Illuminate\Database\Eloquent\Builder) {
            $data_type = $this->get_data_type();
            if (!empty($data_type) && $data_type != 1) {
                $this->query->whereHas(
                    'publisherAccount',
                    function ($subQuery) {
                        $subQuery->whereNotIn('PublisherId',array(90692,54,432));
                        $subQuery->where('PublisherId','>',10);
                    });
            }
            $start_month = $this->get_start_date();
            if (!empty($start_month)) {
                $start_month_date = date_create($start_month);

                $this->query->where('createdmonth','=',date_format($start_month_date, 'ym'));
            }

            $sites = $this->get_site();
            if (!empty($sites)) {
                if (!is_array($sites)) {
                    $sites = array($sites);
                }
                $this->query->whereIn('site',$sites);
            }

            $except_sites = $this->get_except_site();
            if (!empty($except_sites)) {
                if (!is_array($except_sites)) {
                    $except_sites = array($except_sites);
                }
                $this->query->whereNotIn('site',$except_sites);
            }
            
            $countries = $this->get_country();
            if (!empty($countries)) {
                $this->query->whereIn('country',$countries);
            }
            $affiliate_id = $this->get_affiliate_id();
            if (!empty($affiliate_id)) {
                $this->query->whereIn('affid',$affiliate_id);
            }

            $store_id = $this->get_store_id();
            if (!empty($store_id)) {
                $this->query->where(function ($query) use ($store_id) {
                    $dids = array_chunk($store_id, 500);
                    foreach ($dids as $did) {
                        $query->orWhereIn('storeid',$did);
                    }
                });
            }
            $publisher_id = $this->get_publisher_id();
            if (!empty($publisher_id)) {
                $this->query->where(function ($query) use ($publisher_id) {
                    $pids = array_chunk($publisher_id, 500);
                    foreach ($pids as $pid) {
                        $query->orWhereIn('Publisherid',$pid);
                    }
                });
            }

            $store_type = $this->get_store_type();
            if (!empty($store_type)) {
                $this->query->whereHas(
                    'store',
                    function ($subQuery) use($store_type){
                        $subQuery->where('SupportType', $store_type);
                    }
                );
            }
            $store_name_status = $this->get_store_name_status();
            if (!empty($store_name_status)) {
                $this->query->whereHas(
                    'store',
                    function ($subQuery) use($store_name_status){
                        switch ($store_name_status) {
                            case 'YES' :
                                $subQuery->where(new \Illuminate\Database\Query\Expression("NameOptimized != ''"));
                                break;
                            case 'NO' :
                                $subQuery->where(new \Illuminate\Database\Query\Expression("(NameOptimized IS NULL OR NameOptimized = ''"));
                                break;
                        }
                    }
                );
            }
            $store_picture_status = $this->get_store_picture_status();
            if (!empty($store_picture_status)) {
                $this->query->whereHas(
                    'store',
                    function ($subQuery) use($store_picture_status){
                        switch ($store_picture_status) {
                            case 'YES' :
                                $subQuery->where(new \Illuminate\Database\Query\Expression("LogoName LIKE '%,%'"));
                                break;
                            case 'NO' :
                                $subQuery->where(new \Illuminate\Database\Query\Expression("(LogoName = '' OR LogoName IS NULL)"));
                                break;
                        }
                    }
                );
            }

            $store_network_status = $this->get_store_network_status();
            if (!empty($store_network_status)) {
                $this->query->whereHas(
                    'store',
                    function ($subQuery) use($store_network_status){
                        switch ($store_network_status) {
                            case 'YES' :
                                $subQuery->where('StoreAffSupport', 'YES');
                                break;
                            case 'NO' :
                                $subQuery->where('StoreAffSupport', 'NO');
                                break;
                        }

                    }
                );
            }
            $store_ppc_status = $this->get_store_ppc_status();
            if (!empty($store_ppc_status)) {
                $this->query->whereHas(
                    'store',
                    function ($subQuery) use($store_ppc_status){
                        $subQuery->where('PPCStatus', $store_ppc_status);

                    }
                );
            }
            $publisher_type = $this->get_publisher_type();
            if (!empty($publisher_type)) {
                $this->query->whereHas(
                    'publisher',
                    function ($subQuery) use($publisher_type){
                        $subQuery->where('SiteOption', $publisher_type);

                    }
                );
            }

        }
    }

}