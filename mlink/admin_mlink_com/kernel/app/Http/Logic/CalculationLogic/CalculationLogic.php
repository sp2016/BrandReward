<?php
namespace App\Http\Logic\CalculationLogic;
use App\Http\Entity\BasicEntity;
use App\Http\Entity\Statistics\StatisticsQueryEntity;
use App\Http\Logic\BasicLogic;
use App\Http\Logic\StatisticsLogic;

/**
 * Class CalculationLogic
 * @package App\Http\Logic\CalculationLogic
 * @description 处理传入参数的统一处理 & 产生数据源结结构
 */
class CalculationLogic
{
    //时间参数
    protected $dateType;
    protected $startDate;
    protected $endDate;
    //发布者ids
    protected $publisherIds;
    //程序ids
    protected $programIds;
    //域名ids
    protected $domainIds;
    //联盟ids
    protected $affiliateIds;
    //商家ids
    protected $storeIds;
    //国家codes
    protected $countries;


    CONST DEAL_AFFILIATE_DOMAIN_LOGIC = 'DealAffiliateDomainLogic';

    CONST DEAL_DATA_LOGIC = 'DealDataLogic';

    CONST DEAL_DOMAIN_DATA_LOGIC = 'DealDomainDataLogic';

    CONST DEAL_LINK_DATA_LOGIC = 'DealLinkDataLogic';

    CONST DEAL_PROGRAM_DATA_LOGIC = 'DealProgramDataLogic';
    
    CONST DEAL_STORE_DATA_LOGIC = 'DealStoreDataLogic';
    
    public function __construct($entity)
    {
        if ($entity instanceof BasicEntity)
        {
            //设置查询日期类型;
            !$entity->isEmpty('dateType') ? $this->setDateType($entity->dateType) : $this->setDateType(1);
            //设置开始时间
            !$entity->isEmpty('startDate') && $this->setStartDate($entity->startDate);
            //设置结束时间
            !$entity->isEmpty('endDate') && $this->setEndDate($entity->endDate);
            //设置publisher的编号
            !$entity->isEmpty('publisherIds') && $this->setPublisherIds($entity->publisherIds);
            //设置programIds编号
            !$entity->isEmpty('domainIds') && $this->setDomainIds($entity->domainIds);
            //设置affiliateIds编号
            !$entity->isEmpty('affiliateIds') && $this->setAffiliateIds($entity->affiliateIds);
            //设置storeIds的编号
            !$entity->isEmpty('storeIds') && $this->setStoreIds($entity->storeIds);
            //设置countries
            !$entity->isEmpty('countries') &&  $this->setCountries($entity->countries);
        }
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return mixed
     */
    public function getPublisherIds()
    {
        return $this->publisherIds;
    }

    /**
     * @param mixed $publisherIds
     */
    public function setPublisherIds($publisherIds)
    {
        $this->publisherIds = $publisherIds;
    }

    /**
     * @return mixed
     */
    public function getProgramIds()
    {
        return $this->programIds;
    }

    /**
     * @param mixed $programIds
     */
    public function setProgramIds($programIds)
    {
        $this->programIds = $programIds;
    }

    /**
     * @return mixed
     */
    public function getDomainIds()
    {
        return $this->domainIds;
    }

    /**
     * @param mixed $domainIds
     */
    public function setDomainIds($domainIds)
    {
        $this->domainIds = $domainIds;
    }

    /**
     * @return mixed
     */
    public function getAffiliateIds()
    {
        return $this->affiliateIds;
    }

    /**
     * @param mixed $affiliateIds
     */
    public function setAffiliateIds($affiliateIds)
    {
        $this->affiliateIds = $affiliateIds;
    }

    /**
     * @return mixed
     */
    public function getDateType()
    {
        return $this->dateType;
    }

    /**
     * @param mixed $dateType
     */
    public function setDateType($dateType)
    {
        $this->dateType = $dateType;
    }

    /**
     * @return mixed
     */
    public function getStoreIds()
    {
        return $this->storeIds;
    }

    /**
     * @param mixed $storeIds
     */
    public function setStoreIds($storeIds)
    {
        $this->storeIds = $storeIds;
    }

    /**
     * @return mixed
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * @param mixed $countries
     */
    public function setCountries($countries)
    {
        $this->countries = $countries;
    }




    public function __get($name)
    {
       return $this->$name;
    }

    public function isEmpty($key = '')
    {
        $value = $this->$key;
        if (empty($value)) {
            return true;
        }
        
        return false;
    }
    
    
    public function getInstance($entity = null)
    {
        $calLogicName = 'App\\Http\\Logic\\DealDataLogic\\' . $this->getCalLogicName();
        
        return new $calLogicName($entity);
    }
    
}