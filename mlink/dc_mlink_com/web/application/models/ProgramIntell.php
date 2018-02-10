<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Model \ProgramIntell
 */
class ProgramIntell extends \Illuminate\Database\Eloquent\Model
{
    protected $primaryKey = 'ID';
    protected $table      = 'program_intell';
    const UPDATED_AT = 'LastUpdateTime';
}