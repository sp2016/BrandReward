<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Model \WfAffiliate
 */
class WfAfflilate extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    protected $table = 'wf_aff';
    const UPDATED_AT = 'LastUpdateTime';

    /**
     * @return string
     */
    public function revenueAccount()
    {
        return $this->belongsTo('FinanceRevenueAccount', 'RevenueAccount', 'ID');
    }
}