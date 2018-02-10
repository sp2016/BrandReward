<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Model \StaticsBr
 */
class StaticsBr extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = ['createddate','affId','site','programid','domainid','country'];
    public $incrementing = false;
    protected $table = 'statis_br';
    public $timestamps = false;
}