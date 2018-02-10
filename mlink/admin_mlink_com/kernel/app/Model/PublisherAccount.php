<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PublisherAccount extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'ID';
    protected $table = 'publisher_account';
    const UPDATED_AT = 'LastUpdateTime';
}