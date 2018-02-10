<?php
namespace App\Http\Logic\DealDataLogic;
use App\Model\StaticsLink;

/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/12/15
 * Time: 10:29
 */
class DealLinkDataLogic extends DealBasicDataLogic
{
    private $linkId;
    private $country;

    protected $allowCalType = array('DATE' => 'createddate','SITE' => 'site','LINK' => 'linkid', 'COUNTRY' => 'country');


    public function __construct($entity)
    {
        parent::__construct($entity);
        $model = new  StaticsLink();
        $this->query = $model::query();
        if ($entity instanceof BasicEntity)
        {
            //设置affiliateId编号
            !$entity->isEmpty('linkIds') && $this->setLinkId($entity->linkIds);
            //设置国家编号
            !$entity->isEmpty('countries') && $this->setCountry($entity->countries);
        }
    }
    
    /**
     * @return mixed
     */
    public function getLinkId()
    {
        return $this->linkId;
    }

    /**
     * @param mixed $linkId
     */
    public function setLinkId($linkId)
    {
        $this->linkId = $linkId;
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

    
}