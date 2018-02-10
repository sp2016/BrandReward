<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Model \Program
 */
class Program extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    protected $table = 'program';
    const UPDATED_AT = 'LastUpdateTime';
    const CREATED_AT = 'AddTime';

    public function intell()
    {
        return $this->hasOne('ProgramIntell','ProgramId','ID');
    }

    public function network()
    {
        return $this->belongsTo('WfAfflilate', 'AffId', 'ID');
    }
}