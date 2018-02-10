<?php
namespace App\Http\Logic\DealDataLogic;
use App\Http\Entity\BasicEntity;
use App\Model\StaticsAffiliateBr;

/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:25
 */
class DealAffiliateDomainLogic extends DealBasicDataLogic
{
    private $affiliateId;

    protected $allowCalType = array('DATE' => 'createddate','SITE' => 'site', 'AFFILIATE' => 'affId');

    public function __construct($entity)
    {
        parent::__construct($entity);
        $model = new  StaticsAffiliateBr();
        $this->query = $model::query();
        if ($entity instanceof BasicEntity)
        {
            //设置affiliateId编号
            !$entity->isEmpty('affiliateIds') && $this->setAffiliateId($entity->affiliateIds);
        }
    }
    
    /**
     * @return mixed
     */
    public function getAffiliateId()
    {
        return $this->affiliateId;
    }

    /**
     * @param mixed $affiliateId
     */
    public function setAffiliateId($affiliateId)
    {
        $this->affiliateId = $affiliateId;
    }
    

    public function getFilterCondition()
    {
        parent::getFilterCondition();
        $affiliate = $this->getAffiliateId();
        if (!empty($affiliate)) {
            !is_array($affiliate) && $affiliate = array($affiliate);
            array_push($this->filter, array('affId', 'IN', $affiliate));
        }
    }
    
}