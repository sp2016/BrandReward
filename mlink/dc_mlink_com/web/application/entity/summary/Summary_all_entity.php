<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Summary_all_entity
 *
 * @property  array $filter
 * @property array $field
 * @property integer outid
 */
class Summary_all_entity extends Basic_entity
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
        'store_type' => array('NONE','CONTENT','PROMOTION','ALL','MIXED'),
        'publisher_type' => array('NONE','CONTENT','PROMOTION','MIXED'),
        'store_ppc_status' => array('PPCAllowed','Mixed','NotAllow','UNKNOWN'),
    );

    public function _initialize()
    {
        parent::_initialize();
        !$this->is_empty('field') ? $this->filter($this->field) : $this->filter();
    }
}