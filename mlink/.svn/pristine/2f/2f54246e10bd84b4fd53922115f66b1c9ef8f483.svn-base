<?php
namespace App\Http\Logic\DealDataLogic;
use App\Model\StaticsProgramBr;

/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:26
 */
class DealProgramDataLogic extends DealBasicDataLogic
{
    private $programId;
    protected $allowCalType = array('DATE' => 'createddate','SITE' => 'site','PROGRAM' => 'programid');

    /**
     * @return mixed
     */
    public function getProgramId()
    {
        return $this->programId;
    }

    /**
     * @param mixed $programId
     */
    public function setProgramId($programId)
    {
        $this->programId = $programId;
    }


    public function __construct($entity)
    {
        parent::__construct($entity);
        $model = new  StaticsProgramBr();
        $this->query = $model::query();
        if ($entity instanceof BasicEntity)
        {
            //设置programId编号
            !$entity->isEmpty('programIds') && $this->setProgramId($entity->programIds);
            
        }
    }
    
}