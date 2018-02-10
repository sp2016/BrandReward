<?php
/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/11/24
 * Time: 9:42
 */
class AllCalculation extends DataCalculation
{
    protected $affiliate = array();
    protected $program = array();
    protected $programSql = '';
    protected $domain = array();
    protected $domainSql = '';
    protected $country = array();

    protected $table = 'statis_br';
    //按照affiliate统计主键
    protected $calByAffiliateKey = 'affid';
    //按照Domain统计主键
    protected $calByDomainKey = 'domainid';
    //按照Program统计主键
    protected $calByProgramKey = 'programid';
    //按照Country统计主键
    protected $calByCountryKey = 'country';

    /**
     * @return string
     */
    public function getProgramSql()
    {
        return $this->programSql;
    }

    /**
     * @param string $programSql
     */
    public function setProgramSql($programSql = '')
    {
        if (!empty($programSql) && is_string($programSql)) {
            $this->programSql = $programSql;
        }
    }

    /**
     * @return string
     */
    public function getDomainSql()
    {
        return $this->domainSql;
    }

    /**
     * @param string $domainSql
     */
    public function setDomainSql($domainSql)
    {
        if (!empty($domainSql) && is_string($domainSql)) {
            $this->domainSql = $domainSql;
        }

    }
    
    /**
     * @return array
     */
    public function getAffiliate()
    {
        return $this->affiliate;
    }

    /**
     * @param array $affiliate
     */
    public function setAffiliate($affiliate = array())
    {
        if (is_array($affiliate)) {
            $this->affiliate = $affiliate;
        }
    }


    /**
     * @return array
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param array $country
     */
    public function setCountry($country = array())
    {
        if (is_array($country)) {
            $this->country = $country;
        }
    }

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
     * @return array
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param array $domain
     */
    public function setDomain($domain = array())
    {
        if (is_array($domain)) {
            $this->domain = $domain;
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
        $domain = $this->getDomain();
        if (!empty($domain)) {
            if (is_array($domain)) {
                array_push($this->filter, " domainid IN (" .implode(',', $domain) .")" );
            }
        }
        $program = $this->getProgram();
        if (!empty($program)) {
            if (is_array($program)) {
                array_push($this->filter, " programid IN (" .implode(',', $program) .")" );
            }
        }

        $programSql = $this->getProgramSql();
        if (!empty($programSql)) {
            array_push($this->filter, ' programid IN (' . $programSql . ')');
        }

        $domainSql = $this->getDomainSql();
        if (!empty($domainSql)) {
            array_push($this->filter, ' domainid IN (' . $domainSql . ')');
        }

        $country = $this->getCountry();
        if (!empty($country)) {
            $countryProcessedArray = array();
            foreach ($country as $co) {
                array_push($countryProcessedArray, "'" . $co . "'");
            }
            !empty($countryProcessedArray) && array_push($this->filter, " country IN (" .implode(',', $countryProcessedArray) .")" );
        }

    }

    /**
     * @return bool
     */
    function doCalculateByStore()
    {
        return false;
    }

}