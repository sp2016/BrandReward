<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
/**
 * Model \Store
 */
class StoreProgramHistory extends Model
{
    protected $connection   = 'mysql';
    protected $primaryKey   = 'id';
    public    $incrementing = false;
    protected $table        = 'store_program_history';
    public    $timestamps   = false;

}