<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Class StaticsDomainBr
 */
class StaticsDomainBr extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = ['createddate','domainId','site'];
    public $incrementing = false;
    protected $table = 'statis_domain_br';
    public $timestamps = false;
}