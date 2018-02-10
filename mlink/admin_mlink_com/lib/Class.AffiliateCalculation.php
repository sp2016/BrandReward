<?php
/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/11/23
 * Time: 16:58
 */
class AffiliateCalculation extends  DataCalculation
{

    protected $affiliate = array();
    protected $table = 'statis_affiliate_br';

    /**
     * @return mixed
     */
    public function getAffiliate()
    {
        return $this->affiliate;
    }

    /**
     * @param mixed $affiliate
     */
    public function setAffiliate($affiliate = array())
    {
        if (is_array($affiliate)) {
            $this->affiliate = $affiliate;
        }
    }

    public function doInitFilterCondition()
    {
        parent::doInitFilterCondition();
        $affiliate = $this->getAffiliate();
        if (!empty($affiliate)) {
            if (is_array($affiliate)) {
                array_push($this->filter, " affid IN (" .implode(',', $affiliate) .")" );
            }
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

    function doCalculateByProgram()
    {
        return false;
    }

    function doCalculateByCountry()
    {
        return false;
    }
}