<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
/**
 * Model \Store
 */
class Store extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $table = 'store';
    public $timestamps = false;

    public function domains()
    {
        return $this->belongsToMany('App\Model\Domain','r_store_domain','StoreId','DomainId');
    }


    public function programs()
    {
        return $this->belongsToMany('App\Model\Program','r_store_program','StoreId','ProgramId');
    }

    
    public function coupons()
    {
        return $this->hasMany('App\Model\Coupon','StoreId','ID');
    }
    
    
    public function products()
    {
        return $this->hasMany('App\Model\Product','StoreId','ID');
    }

}