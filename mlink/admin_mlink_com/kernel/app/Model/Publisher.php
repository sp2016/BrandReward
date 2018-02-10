<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
/**
 * Model \Publisher
 */
class Publisher extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'ID';
    protected $table = 'publisher';
    const UPDATED_AT = 'LastUpdateTime';
}