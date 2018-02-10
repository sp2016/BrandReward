<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Model \TransactionUnique
 */
class TransactionUnique extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    protected $table = 'rpt_transaction_unique';
    const CREATED_AT = 'Created';
    const UPDATED_AT = 'Updated';

    public function publisherAccount()
    {
        return $this->belongsTo('PublisherAccount','Site', 'ApiKey');
    }

    public function network()
    {
        return $this->belongsTo('WfAfflilate', 'AffId', 'ID');
    }

    public function domain()
    {
        return $this->belongsTo('Domain','domainId','ID');
    }
}