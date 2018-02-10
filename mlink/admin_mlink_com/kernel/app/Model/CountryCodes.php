<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CountryCodes
 * @package App\Model
 */
class CountryCodes extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'ID';
    protected $table = 'country_codes';
    public $timestamps = false;
}