<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class CategoryStd
 */
class CategoryStd extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    protected $table = 'category_std';
    public $timestamps = false;
}