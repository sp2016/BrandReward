<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
/**
 * Model \ProgramIntell
 */
class ProgramIntell extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'ID';
    protected $table      = 'program_intell';
    const UPDATED_AT = 'LastUpdateTime';
}