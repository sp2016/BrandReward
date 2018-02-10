<?php
defined('BASEPATH') OR exit('No direct script access allowed');;
/**
 * Model \PaymentRemit
 */
class PaymentPending extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $table = 'payments_pending';
    public $timestamps = false;

    public function publisherAccount()
    {
        return $this->belongsTo('PublisherAccount','Site', 'ApiKey');
    }
}