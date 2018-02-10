<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Product_entity
 *
 * @property  integer $offset
 * @property  integer $limit
 * @property  array   $order
 * @property  boolean $paginate
 */
class Payment_entity extends Basic_entity
{
    protected $allow_fields = array(
        'ALL',
        'pay_pending_id',
        'pay_pending_amount',
        'pay_pending_pendingdate',
        'pay_id',
        'pay_remit_id',
        'pub_id',
        'pub_email',
        'pub_manager',
        'site_id',
        'site_name',
        'site_key',
        'site_domain',
    );

    protected $check_keys = array(
        'status' => array('PENDING', 'PAID'),
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