<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class PublisherAccount
 */
class PublisherAccount extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    protected $table = 'publisher_account';
    const UPDATED_AT = 'LastUpdateTime';
    
    public function publisher()
    {
        return $this->belongsTo('Publisher', 'PublisherId', 'ID');
    }
}