<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StaticsProgramBr
 * @package App\Model
 */
class StaticsProgramBr extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = ['createddate','programId','site'];
    public $incrementing = false;
    protected $table = 'statis_program_br';
    public $timestamps = false;
}