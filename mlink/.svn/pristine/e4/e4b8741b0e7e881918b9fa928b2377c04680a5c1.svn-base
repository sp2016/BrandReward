<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Network_entity
 *
 * @property  integer $offset
 * @property  integer $limit
 * @property  array   $order
 * @property  boolean $paginate
 */
class Network_entity extends Basic_entity
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
    
    protected  $check_keys = array(
        'ntw_level' => array('TIER1','TIER2'),
        'ntw_isactive' => array('YES','NO'),
        'ntw_finance_if_received' => array('YES','NO'),
        'crawl_transaction' => array('YES','NO'),
        'crawl_program' => array('YES','NO'),
    );
    
    public function _initialize()
    {
        parent::_initialize();
        //检查是否符合规范
        if (!$this->is_empty('ntw_type'))
        {
            $ntw_type = strtoupper($this->ntw_type);
            if (in_array($ntw_type, array('NETWORK','INHOUSE')))
            {
                $ntw_type_tag = false;
                switch ($ntw_type) {
                    case 'NETWORK' :
                        $ntw_type_tag = 'NO';
                        break;
                    case 'INHOUSE' :
                        $ntw_type_tag = 'YES';
                        break;
                }
                !empty($ntw_type_tag) && $this->merge(['ntw_type' => $ntw_type_tag]);
            }
        }
        
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