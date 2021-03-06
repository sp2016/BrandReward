<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Summary_group_entity
 *
 * @property  array $filter
 * @property array $field
 * @property integer outid
 */
class Summary_group_entity extends Basic_entity
{
    protected $allow_fields = array(
        'ALL',
        'clicks',
        'clicks_robot',
        'clicks_maybe_robot',
        'sales',
        'commission',
        'commission_b',
        'commission_p',
    );

    protected $check_keys = array(
        'data_type' => array('ALL', 'PUBLISHER'),
        'time_type' => array('CLICK', 'TRANSACTION'),
        'sort_by' =>  array(
            'COMMISSION_DESC',
            'COMMISSION_ASC',
            'CLICKS_DESC',
            'CLICKS_ASC',
            'ORDERS_DESC',
            'ORDERS_ASC',
            'SALES_DESC',
            'SALES_ASC',
            'CLICKS_ROBOT_ASC',
            'CLICKS_ROBOT_DESC',
            'CLICKS_MAYBE_ROBOT_ASC',
            'CLICKS_MAYBE_ROBOT_DESC',
        ),
        'store_type' => array('NONE','CONTENT','PROMOTION','ALL','MIXED'),
        'store_ppc_status' => array('PPCAllowed','Mixed','NotAllow','UNKNOWN'),
        'publisher_type' => array('NONE','CONTENT','PROMOTION','MIXED'),
        'group' => array('ADVERTISER', 'NETWORK','PUBLISHER'),
    );

    public function _initialize()
    {
        parent::_initialize();
        !$this->is_empty('field') ? $this->filter($this->field) : $this->filter();
    }
}