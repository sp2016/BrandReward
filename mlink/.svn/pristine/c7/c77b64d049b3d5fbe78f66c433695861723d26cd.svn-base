<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Network_entity
 *
 * @property  array $filter
 * @property array $field
 * @property integer $ntw_id
 */
class Network_detail_entity extends Basic_entity
{
    protected $allow_fields = array(
        'ALL',
        'ntw_id',
        'ntw_name',
        'ntw_short',
        'ntw_code',
        'ntw_domain',
        'ntw_login_url',
        'ntw_account',
        'ntw_password',
        'ntw_manager',
        'ntw_join_date',
        'ntw_rank',
        'ntw_isactive',
        'fin_acc_id',
        'fin_acc_name',
        'ntw_finance_if_received',
        'ntw_finance_payment_cycle',
        'ntw_finance_payment_remark',
        'ntw_comment'
    );

    public function _initialize()
    {
        parent::_initialize();
        !$this->is_empty('field') ? $this->filter($this->field) : $this->filter();
        
        if (!$this->is_empty('ntw_id')) {
            $this->merge(['id' => $this->attributes['ntw_id']]);
            unset($this->attributes['ntw_id']);
        }
    }
}