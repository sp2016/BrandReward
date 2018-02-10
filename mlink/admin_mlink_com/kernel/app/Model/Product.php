<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
/**
 * Model \Coupon
 */
class Product extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $table = 'product_feed';
    public $timestamps = false;
}