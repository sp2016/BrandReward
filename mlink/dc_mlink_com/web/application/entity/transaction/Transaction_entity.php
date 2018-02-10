<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Transaction_entity
 *
 * @property  integer $offset
 * @property  integer $limit
 * @property  array   $order
 * @property  boolean $paginate
 */
class Transaction_entity extends Basic_entity
{
    protected $allow_fields = array(
        'ALL',
        'tran_id',
        'tran_brid',
        'ntw_id',
        'ntw_name',
        'pgm_id',
        'pgm_name',
        'dom_id',
        'dom_domain',
        'adv_id',
        'adv_name',
        'tran_original_sales',
        'tran_original_commission',
        'tran_original_currency',
        'tran_sales',
        'tran_commission',
        'tran_currency',
        'pub_commission_rate',
        'tran_commission_b',
        'tran_commission_p',
        'tran_sid',
        'tran_pub_trackingid',
        'tran_ntw_orderid',
        'tran_ntw_tradeid',
        'tran_state',
        'tran_cancel_reason',
        'tran_ispaid',
        'tran_isfine',
        'tran_isreceive',
        'tran_time_click',
        'tran_time_create',
        'tran_time_lastupdate'
);
    protected $check_keys =  array(
        'tran_state' => array('PENDING', 'PAID', 'CONFIRMED', 'FINE', 'REMOVE', 'CANCELL', 'RECEIVED'),
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