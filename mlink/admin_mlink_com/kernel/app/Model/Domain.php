<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
/**
 * Model \Domain
 */
class Domain extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $table = 'domain';
    public $timestamps = false;
}