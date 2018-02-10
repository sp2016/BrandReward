<?php
/**
 * Model \StaticsBr
 */
class StaticsBrMonth extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = ['createdmonth','affid','storeid','country','site'];
    public $incrementing = false;
    protected $table = 'statis_br_month';
    public $timestamps = false;

    public function publisherAccount()
    {
        return $this->belongsTo('PublisherAccount','site', 'ApiKey');
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