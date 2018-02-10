<?php
namespace App\Http\Logic\CalculationLogic;
/**
 * Class PublisherCalculationLogic
 * @package App\Http\Logic\CalculationLogic
 * @description 
 */
class PublisherCalculationLogic extends CalculationLogic
{
    public function getCalLogicName()
    {
        if (!$this->isEmpty('domainIds') || !$this->isEmpty('programIds')) {
            if ($this->isEmpty('affiliateIds') && $this->isEmpty('countries') ) {
                return static::DEAL_DOMAIN_DATA_LOGIC;
            } else {
                return static::DEAL_DATA_LOGIC;
            }
        }

        return static::DEAL_AFFILIATE_DOMAIN_LOGIC;
    }
}