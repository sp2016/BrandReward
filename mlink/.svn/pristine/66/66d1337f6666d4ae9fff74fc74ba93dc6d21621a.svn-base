<?php
defined('BASEPATH') OR exit('No direct script access allowed');;
/**
 * Model \Coupon
 */
class OutboundLogMin extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $table = 'bd_out_tracking_min';
    public $timestamps = false;
    
    public function outboundLog()
    {
        return $this->belongsTo('OutboundLog','id','id');
    }
    
    
    public function publisherAccount()
    {
        return $this->belongsTo('PublisherAccount','site', 'ApiKey');
    }
    
    public function domain()
    {
        return $this->belongsTo('Domain','domainId', 'ID');
    }


    public function program()
    {
        return $this->belongsTo('Program', 'programId', 'ID');
    }
    
    public function network()
    {
        return $this->belongsTo('WfAfflilate', 'affId', 'ID');
    }

    public function transactions()
    {
        return $this->hasMany('TransactionUnique',  'SID', 'sessionId');
    }

}