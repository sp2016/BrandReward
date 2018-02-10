<?php
namespace App\Http\Logic\DealDataLogic;
use App\Http\Entity\BasicEntity;
use App\Model\StaticsDomainBr;
use Illuminate\Database\Eloquent\Builder;

/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:25
 */
class DealDomainDataLogic extends DealBasicDataLogic
{
    private $domainId;
    private $storeId;

    protected $allowCalType = array('DATE' => 'createddate','SITE' => 'site','DOMAIN' => 'domainId', 'STORE' => 'storeId');

    public function __construct($entity)
    {
        parent::__construct($entity);
        $model = new  StaticsDomainBr();
        $this->query = $model::query();
        if ($entity instanceof BasicEntity)
        {
            //设置domainIds编号
            !$entity->isEmpty('domainIds') && $this->setDomainId($entity->domainIds);
            //设置storeIds编号
            !$entity->isEmpty('storeIds') && $this->setStoreId($entity->storeIds);
        }
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param mixed $storeId
     */
    public function setStoreId($storeId)
    {
        if (!is_array($storeId)) {
            $storeId = array($storeId);
        }
        $this->storeId = $storeId;
    }

    /**
     * @return mixed
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @param mixed $domainId
     */
    public function setDomainId($domainId)
    {
        if (!is_array($domainId)) {
            $domainId = array($domainId);
        }
        $this->domainId = $domainId;
    }


    public function getFilterCondition()
    {
        parent::getFilterCondition();
        if ($this->query instanceof Builder) {
            $storeId = $this->getStoreId();
            if (!empty($storeId)) {
                $this->query->whereIn('StoreId',$storeId);
            }
            $domainId = $this->getDomainId();
            if (!empty($domainId)) {
                $this->query->where(function ($query) use ($domainId) {
                    $dids = array_chunk($domainId, 100);
                    foreach ($dids as $did) {
                        $query->orWhereIn('domainid',$did);
                    }
                });
            }
        }
    }
    

}