<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
/**
 * Model \Program
 */
class Program extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'ID';
    protected $table = 'program';
    const UPDATED_AT = 'LastUpdateTime';
    const CREATED_AT = 'AddTime';

    public function intell()
    {
        return $this->hasOne('App\Model\ProgramIntell','ProgramId','ID');
    }

}