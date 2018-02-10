<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Model \Publisher
 */
class Publisher extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    protected $table = 'publisher';
    const UPDATED_AT = 'LastUpdateTime';
    
    public function country()
    {
        return $this->belongsTo('CountryCodes', 'Country', 'id');
    }
}