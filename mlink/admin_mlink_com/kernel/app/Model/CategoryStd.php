<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CategoryStd
 * @package App\Model
 */
class CategoryStd extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'ID';
    protected $table = 'category_std';
    public $timestamps = false;
}