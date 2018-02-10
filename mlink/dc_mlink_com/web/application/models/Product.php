<?php
defined('BASEPATH') OR exit('No direct script access allowed');;
/**
 * Model \Coupon
 */
class Product extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $table = 'product_feed';
    public $timestamps = false;

    public function store()
    {
        return $this->belongsTo('Store', 'StoreId', 'ID');
    }

    public function program()
    {
        return $this->belongsTo('Program', 'ProgramId', 'ID');
    }
}