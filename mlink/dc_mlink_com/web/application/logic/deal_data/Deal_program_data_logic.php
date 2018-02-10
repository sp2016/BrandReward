<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:26
 */
class Deal_program_data_logic extends Deal_basic_data_logic
{
    private $program_id;
    protected $allow_cal_type = array('DATE' => 'createddate','SITE' => 'site','PROGRAM' => 'programid');

    /**
     * @return mixed
     */
    public function get_program_id()
    {
        return $this->program_id;
    }

    /**
     * @param mixed $program_id
     */
    public function set_program_id($program_id)
    {
        $this->program_id = $program_id;
    }


    public function __construct($entity)
    {
        parent::__construct($entity);
        $model = new  StaticsProgramBr();
        $this->query = $model::query();
        if ($entity instanceof Basic_entity)
        {
            //设置programId编号
            !$entity->is_empty('program_ids') && $this->set_program_id($entity->program_ids);
            
        }
    }
    
}