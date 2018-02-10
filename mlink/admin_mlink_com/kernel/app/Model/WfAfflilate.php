<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
/**
 * Model \WfAffiliate
 */
class WfAfflilate extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'ID';
    protected $table = 'wf_aff';
    const UPDATED_AT = 'LastUpdateTime';
}