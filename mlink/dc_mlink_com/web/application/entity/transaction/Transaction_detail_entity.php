<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Transaction_detail_entity
 *
 * @property  array $filter
 * @property array $field
 * @property integer outid
 */
class Transaction_detail_entity extends Basic_entity
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

    public function _initialize()
    {
        parent::_initialize();
        !$this->is_empty('field') ? $this->filter($this->field) : $this->filter();

        if (!$this->is_empty('tran_id')) {
            $this->merge(['id' => $this->attributes['tran_id']]);
            unset($this->attributes['tran_id']);
        }
    }
}