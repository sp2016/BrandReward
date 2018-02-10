<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Model \WfAffiliate
 */
class FinanceRevenueAccount extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    protected $table = 'fin_rev_acc';
    public $timestamps = false;
}