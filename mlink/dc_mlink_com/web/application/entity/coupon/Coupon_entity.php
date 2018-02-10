<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Coupon_entity
 *
 * @property  integer $offset
 * @property  integer $limit
 * @property  array   $order
 * @property  boolean $paginate
 */
class Coupon_entity extends Basic_entity
{
    protected $allow_fields = array(
        'ALL',
        'promo_id',
        'promo_title',
        'promo_desc',
        'promo_couponcode',
        'promo_linkid',
        'promo_commission_rate',
        'promo_period_from',
        'promo_period_to',
        'adv_id',
        'adv_name',
        'adv_category',
        'ntw_id',
        'ntw_name',
        'promo_type',
        'promo_country',
        'promo_source',
        'promo_status',
        'promo_lang',
    );
    
    protected  $check_keys = array(
        'promo_type' => array('COUPON', 'PROMOTION', 'DEAL', 'FREESHIPPING'),
        'promo_source' => array('NETWORK', 'EMAIL', 'MANUAL', 'WHITE LIST'),
        'promo_status' => array('OFFLINE', 'ONLINE', 'ONGOING', 'NOTSTART', 'END'),
        'promo_lang' => array('EN', 'FR', 'DE', 'IT', 'NL', 'ES', 'PT', 'SE'),
    );

    public function _initialize()
    {
        parent::_initialize();
        !$this->is_empty('page') && $this->merge(['offset' => $this->page]);
        !$this->is_empty('pagesize') && $this->merge(['limit' => $this->pagesize]);
        //查询字段:(数组)
        !$this->is_empty('field') ? $this->filter($this->field) : $this->filter();

        if ($this->is_empty('paginate')) {
            $this->merge(['paginate' => true]);
            $this->is_empty('offset') && $this->merge(['offset' => 0]);
            $this->is_empty('limit') && $this->merge(['limit' => 10]);
        }
    }
}