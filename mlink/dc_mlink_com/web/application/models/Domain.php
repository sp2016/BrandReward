<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Model \Domain
 */
class Domain extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $table = 'domain';
    public $timestamps = false;
    
    public function store()
    {
        return $this->belongsToMany('Store','r_store_domain','DomainId','StoreId');
    }
}