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
class Product_entity extends Basic_entity
{
    protected $allow_fields = array(
        'ALL',
        'pdt_id',
        'pdt_title',
        'pdt_desc',
        'pdt_linkid',
        'pdt_commission_rate',
        'pdt_image',
        'pdt_addtime',
        'adv_id',
        'adv_name',
        'adv_category',
        'ntw_id',
        'ntw_name',
        'pdt_country',
        'pdt_source',
        'pdt_lang'
    );

    protected $check_keys = array(
        'pdt_source' => array('NETWORK', 'EMAIL', 'MANUAL', 'WHITE LIST'),
        'pdt_lang' => array('EN', 'FR', 'DE', 'IT', 'NL', 'ES', 'PT', 'SE'),
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