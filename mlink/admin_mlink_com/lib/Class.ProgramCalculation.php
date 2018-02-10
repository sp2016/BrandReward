<?php
/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/11/24
 * Time: 9:42
 */
class ProgramCalculation extends DataCalculation
{
    protected $program = array();
    protected $programSql;
    protected $table = 'statis_program_br';
    /**
     * @return array
     */
    public function getProgram()
    {
        return $this->program;
    }

    /**
     * @param array $program
     */
    public function setProgram($program = array())
    {
        if (is_array($program)) {
            $this->program = $program;
        }
    }

    /**
     * @return mixed
     */
    public function getProgramSql()
    {
        return $this->programSql;
    }

    /**
     * @param mixed $programSql
     */
    public function setProgramSql($programSql)
    {
        $this->programSql = $programSql;
    }

    
    
    public function doInitFilterCondition()
    {
        parent::doInitFilterCondition();
        $programs = $this->getProgram();
        if (!empty($programs)) {
            if (is_array($programs)) {
                array_push($this->filter, " programId IN (" .implode(',', $programs) .")" );
            }
        }

        $programSql = $this->getProgramSql();
        if (!empty($programSql)) {
            array_push($this->filter, ' programId IN (' . $programSql . ')');
        }

    }





    function doCalculateByStore()
    {
        return false;
    }

    function doCalculateByDomain()
    {
        return false;
    }
    
    function doCalculateByAffiliate()
    {
        return false;
    }

    function doCalculateByCountry()
    {
        return false;
    }

}