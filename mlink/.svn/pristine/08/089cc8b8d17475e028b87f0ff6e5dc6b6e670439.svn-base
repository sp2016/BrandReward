<?php
/**
 * Created by PhpStorm.
 * User: sdiao
 * Date: 2017/11/24
 * Time: 9:42
 */
class DomainCalculation extends DataCalculation
{
    protected $store = array();
    protected $domain = array();
    protected $exceptDomain = array();
    protected $domainSql;
    protected $storeSql;

    protected $table = 'statis_domain_br';


    /**
     * @return mixed
     */
    public function getDomainSql()
    {
        return $this->domainSql;
    }

    /**
     * @param mixed $domainSql
     */
    public function setDomainSql($domainSql = '')
    {
        if (!empty($domainSql) && is_string($domainSql)) {
            $this->domainSql = $domainSql;
        }
    }

    /**
     * @return mixed
     */
    public function getStoreSql()
    {
        return $this->storeSql;
    }

    /**
     * @param mixed $storeSql
     */
    public function setStoreSql($storeSql)
    {
        if (!empty($storeSql) && is_string($storeSql)) {
            $this->storeSql = $storeSql;
        }
        
    }

    /**
     * @return array
     */
    public function getExceptDomain()
    {
        return $this->exceptDomain;
    }

    /**
     * @param array $exceptDomain
     */
    public function setExceptDomain($exceptDomain)
    {
        $this->exceptDomain = $exceptDomain;
    }

    /**
     * @return array
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param array $store
     */
    public function setStore($store = array())
    {
        if (is_array($store)) {
            $this->store = $store;
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
        $domains = $this->getDomain();
        if (!empty($domains)) {
            if (is_array($domains)) {
                array_push($this->filter, " domainId IN (" .implode(',', $domains) .")" );
            }
        }
        $stores = $this->getStore();
        if (!empty($stores)) {
            if (is_array($stores)) {
                array_push($this->filter, " storeId IN (" .implode(',', $stores) .")" );
            }
        }

        $domainSql = $this->getDomainSql();
        if (!empty($domainSql)) {
            array_push($this->filter, ' domainid IN (' . $domainSql . ')');
        }

        $storeSql = $this->getStoreSql();
        if (!empty($storeSql)) {
            array_push($this->filter, ' storeId IN (' . $storeSql . ')');
        }

        $exceptDomain = $this->getExceptDomain();
        if (!empty($exceptDomain)) {
            array_push($this->filter, ' domainid NOT IN (' . implode(',', $exceptDomain) .")" );
        }
    }



    function doCalculateByProgram()
    {
        return false;
    }

    function doCalculateByAffiliate()
    {
        return false;
    }

    function doCalculateByCountry()
    {
        return false;
    }

}