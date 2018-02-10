<?php
namespace App\Http\Logic\CalculationLogic;
/**
 * Class StoreCalculationLogic
 * @package App\Http\Logic\CalculationLogic
 */
class StoreCalculationLogic extends CalculationLogic
{
    public function getCalLogicName()
    {
        if ($this->isEmpty('startDate') && $this->isEmpty('endDate')) {
            return static::DEAL_STORE_DATA_LOGIC;
        }
        if ($this->isEmpty('affiliateIds') && $this->isEmpty('countries') ) {
            return static::DEAL_DOMAIN_DATA_LOGIC;
        } else {
            return static::DEAL_DATA_LOGIC;
        }
    }
}