<?php
namespace App\Http\Logic\DealDataLogic;
use App\Http\Entity\BasicEntity;
use App\Model\StaticsBr;
use Illuminate\Database\Eloquent\Builder;

/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:24
 */
class DealDataLogic extends DealBasicDataLogic
{
    private $affiliateId;
    private $programId;
    private $domainId;
    private $country;
    
    protected $allowCalType = array('DATE' => 'createddate','SITE' => 'site', 'AFFILIATE' => 'affId','DOMAIN' => 'domainid', 'PROGRAM' => 'programid', 'COUNTRY' => 'country');

    public function __construct($entity)
    {
        parent::__construct($entity);
        $model = new  StaticsBr();
        $this->query = $model::query();
        if ($entity instanceof BasicEntity)
        {
            //设置domainIds编号
            !$entity->isEmpty('domainIds') && $this->setDomainId($entity->domainIds);
            //设置programId编号
            !$entity->isEmpty('programIds') && $this->setProgramId($entity->programIds);
            //设置affiliateId编号
            !$entity->isEmpty('affiliateIds') && $this->setAffiliateId($entity->affiliateIds);
            //设置国家编号
            !$entity->isEmpty('countries') && $this->setCountry($entity->countries);


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
        $this->domainId = $domainId;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function checkCalType($type)
    {
        $isAllow = parent::checkCalType($type);
        if (empty($isAllow)) {
            switch ($type) {
                case 'STORE' :
                    $this->query->leftJoin('r_store_domain', 'statis_br.domainid', '=', 'r_store_domain.DomainId');
                    array_set($this->allowCalType, 'STORE', 'r_store_domain.StoreId');
                    break;
            }
        }
        return true;
    }

    public function getFilterCondition()
    {
        parent::getFilterCondition();
        if ($this->query instanceof Builder) {
            $countries = $this->getCountry();
            if (!empty($countries)) {
                $this->query->whereIn('country',$countries);
            }
            $affiliateId = $this->getAffiliateId();
            if (!empty($affiliateId)) {
                $this->query->whereIn('affid',$affiliateId);
            }

            $domainId = $this->getDomainId();
            if (!empty($domainId)) {
                $this->query->where(function ($query) use ($domainId) {
                    $dids = array_chunk($domainId, 100);
                    foreach ($dids as $did) {
                        $query->orWhereIn('statis_br.domainid',$did);
                    }
                });
            }
            $programId = $this->getProgramId();
            if (!empty($programId)) {
                $this->query->where(function ($query) use ($programId) {
                    $pids = array_chunk($programId, 100);
                    foreach ($pids as $pid) {
                        $query->orWhereIn('statis_br.programid',$pid);
                    }
                });
            }
        }
    }

}