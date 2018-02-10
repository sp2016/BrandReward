<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Model \Store
 */
class Store extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $table = 'store';
    public $timestamps = false;

    public function domains()
    {
        return $this->belongsToMany('Domain','r_store_domain','StoreId','DomainId');
    }


    public function programs()
    {
        return $this->belongsToMany('Program','r_store_program','StoreId','ProgramId');
    }

    
    public function coupons()
    {
        return $this->hasMany('Coupon','StoreId','ID');
    }
    
    
    public function products()
    {
        return $this->hasMany('Product','StoreId','ID');
    }


    public function category()
    {
        return $this->belongsTo('CategoryStd', 'CategoryId', 'ID');
    }
}