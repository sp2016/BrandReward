<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Outbound_entity
 *
 * @property  array $filter
 * @property array $field
 * @property integer outid
 */
class Outbound_detail_entity extends Basic_entity
{

    protected $allow_fields = array(
        'ALL',
        'out_id',
        'clicktime',
        'click_robot',
        'click_mayberobot',
        'landingpage_url',
        'referrer_url',
        'user_ip',
        'user_country',
        'site_key',
        'site_domain',
        'site_type',
        'site_country',
        'pub_id',
        'pub_email',
        'pub_manager',
        'adv_id',
        'adv_name',
        'dom_id',
        'dom_domain',
        'ntw_id',
        'ntw_name',
        'pgm_id',
        'pgm_idinaff',
        'pgm_name',
        'hasorder',
        'sales',
        'commission',
        'commission_b',
        'commission_p',
    );

    public function _initialize()
    {
        parent::_initialize();
        !$this->is_empty('field') ? $this->filter($this->field) : $this->filter();
        
        if (!$this->is_empty('outid')) {
            $this->merge(['id' => $this->attributes['outid']]);
            unset($this->attributes['outid']);
        }
    }
}