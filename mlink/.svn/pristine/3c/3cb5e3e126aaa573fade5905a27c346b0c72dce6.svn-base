<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Summary_query_entity
 *
 * @property string $date_type
 * @property string $end_date
 * @property array $publisher_ids
 * @property array $site_ids
 * @property array $affiliate_ids
 * @property array $domain_ids
 * @property array $store_ids
 * @property array $link_ids
 * @property array $countries
 *
 * @property integer $limit
 * @property integer $offset
 */
class Summary_query_entity extends Basic_entity
{
    public function _initialize()
    {
        parent::_initialize();
        $this->is_empty('offset') && $this->merge(['offset' => 1]);
        $this->is_empty('limit') && $this->merge(['limit' => 20]);

    }
}
