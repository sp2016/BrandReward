<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Model \StaticsBr
 */
class StaticsBrDaily extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = ['createddate','affid','storeid','country','site'];
    public $incrementing = false;
    protected $table = 'statis_br_daily';
    public $timestamps = false;

    public function publisherAccount()
    {
        return $this->belongsTo('PublisherAccount', 'site', 'ApiKey');
    }

    public function publisher()
    {
        return $this->belongsTo('Publisher', 'Publisherid', 'ID');
    }

    public function store()
    {
        return $this->belongsTo('Store', 'storeid', 'ID');
    }


}