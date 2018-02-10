<?php

class StoreMerge extends LibFactory
{
    public function getStoreMergeList($alias = null,$domain = null)
    {
        $sql_names_set = 'SET NAMES utf8';
        $this->query($sql_names_set);
        $alias = mysql_real_escape_string($alias);
        $domain = mysql_real_escape_string($domain);
        $whereSql = 'WHERE s.Name IS NOT NULL';
        if (!empty($domain)) {
            $whereSql .= ' AND (s.Name LIKE "%'.$domain.'%" OR s.NameOptimized LIKE "%' . $domain.'%")';
        }
        if (!empty($alias)) {
            $whereSql .= ' AND c.CustomName LIKE "%'.$alias.'%"';
        }
        $sql = "SELECT `CustomName` AS `alias`,c.StoreID,IF(s.NameOptimized='' OR s.NameOptimized IS NULL,s.Name,s.NameOptimized) AS `Stores`,c.Status,c.IsActive, MIN(c.`AddTime`) AS `AddTime`,MAX(c.`UpdateTime`) AS `UpdateTime` FROM store_custom c  LEFT JOIN `store`s ON s.ID = c.StoreID $whereSql GROUP BY `CustomName`,s.Name  ";
        $rows = $this->getRows($sql);

        return $rows;
    }

    public function getStores($store = null)
    {
        $sql_names_set = 'SET NAMES utf8';
        $this->query($sql_names_set);
        $whereSql = '';
        $list = array();
        if (!empty($store)) {
            $store = mysql_real_escape_string($store);
            $whereSql = 'WHERE Name LIKE "%'.$store.'%" OR NameOptimized LIKE "%' . $store . '%"';
            $sql = "SELECT `ID`,`Name` FROM store $whereSql";
            $rows = $this->getRows($sql);
            foreach ($rows as $row) {
                $id = isset($row['ID']) ? $row['ID'] : 0;
                $name = isset($row['Name']) ? $row['Name'] : 0;
                !empty($id) && array_push($list, array($id => $name));
            }
        }

        return $list;
    }

    public function doSearchStore($alias = null)
    {
        $sql_names_set = 'SET NAMES utf8';
        $this->query($sql_names_set);
        $string = '';
        $alias = mysql_real_escape_string($alias);
        if (!empty($alias)) {
            $sql = 'SELECT COUNT(*) AS count FROM store WHERE Name ="' . $alias . '"';
            $result = $this->query($sql);
            if ($result['count'] == 0) {
                if (!empty($alias) && strlen($alias) >= 3) {
                    $subSql = "SELECT IF(NameOptimized='' OR NameOptimized IS NULL,Name,NameOptimized) AS `Name`,store.ID FROM store WHERE Name LIKE '%" . $alias . "%' OR NameOptimized LIKE '%" . $alias . "%'";
                    $rows = $this->getRows($subSql);
                    if ($rows) {
                        $tmp = array();
                        foreach ($rows as $v) {
                            if (isset($v['Name']) && isset($v['ID'])){
                                array_push($tmp, $v['ID'] . '.' .$v['Name']);
                            }

                        }
                        $string = join('|', $tmp);
                    }
                }
            }
        }

        return $string;
    }

    public function doSearchStoreDomain($storeId = null)
    {
        $sql_names_set = 'SET NAMES utf8';
        $this->query($sql_names_set);
        $string = '';
        if (!empty($storeId)) {
            $subSql = "SELECT D.`Domain`,D.`ID` FROM r_store_domain R  LEFT JOIN domain D ON D.ID = R.DomainId WHERE R.DomainAffSupport = 'YES' AND  R.StoreId  = '" . $storeId . "' UNION SELECT D.`Domain`,D.`ID` FROM store_custom C LEFT JOIN domain D ON C.Domain = D.Domain WHERE IsActive = 'Active' AND StoreId = " . $storeId;
            $rows = $this->getRows($subSql);
            if ($rows) {
                $tmp = array();
                foreach ($rows as $v) {
                    if (isset($v['Domain']) && $v['ID']) {
                        $cSql = "SELECT COUNT(*) AS `cnt` FROM store_custom C WHERE IsActive = 'Active' and C.Domain='" . $v['Domain'] . "' AND StoreId = " . $storeId;
                        $cest = $this->getRow($cSql);
                        $tag = '0';
                        if ($cest['cnt'] > 0) {
                            $tag = '1';
                        }
                        array_push($tmp,  $v['ID'] . '$' . $v['Domain'] . '$' . $tag);
                    }
                }
                $string = join('|', $tmp);
            }
        }

        return $string;
    }


    public function getStoreMerge($alias = null,$merged = null)
    {
        $sql_names_set = 'SET NAMES utf8';
        $this->query($sql_names_set);
        $alias = mysql_real_escape_string($alias);
        $merged = mysql_real_escape_string($merged);
        $whereSql = '';
        if (!empty($alias) && !empty($merged)) {
            $whereSql = 'WHERE c.CustomName LIKE "%'.$alias.'%" AND (c.StoreId = ' . $merged . ')';
        } else {
            return false;
        }
        $sql = "SELECT `CustomName` AS `alias`,c.StoreId,IF(NameOptimized='' OR NameOptimized IS NULL,Name,NameOptimized)  AS `Stores`,c.Status,c.IsActive, MIN(c.`AddTime`) AS `AddTime`,MAX(c.`UpdateTime`) AS `UpdateTime` FROM store_custom c LEFT JOIN `store`s ON s.ID = c.StoreId $whereSql GROUP BY `CustomName` ";
        $row = $this->getRow($sql);

        return $row;
    }

    public function doMergeStore($alias,$merged,$omerged = null)
    {
        $sql_names_set = 'SET NAMES utf8';
        $this->query($sql_names_set);
        $alias = mysql_real_escape_string($alias);
        $merged = mysql_real_escape_string($merged);
        $boolean = false;
        if (!empty($omerged)) {
            $omerged = mysql_real_escape_string($omerged);
            //$this->doDeleteStore($alias,$omerged);
        }
        $sql = "SELECT d.Domain,s.ID FROM store s LEFT JOIN r_store_domain r ON s.ID = r.StoreId LEFT JOIN domain d ON d.ID = r.DomainId WHERE r.DomainAffSupport = 'YES' AND  s.Name = '".$merged."' OR s.NameOptimized = '".$merged . "'";
        $rows = $this->getRows($sql);
        $opName = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'system';
        $iSql = "INSERT INTO `store_custom`(Domain,CustomName,Operator,StoreId) VALUES";
        $iArray = array();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                array_push($iArray, "('".$row['Domain'] . "','$alias','$opName',".$row['ID'].")") ;
            }
        }
        if (!empty($iArray)) {
            $boolean =  $this->query($iSql . implode(',', $iArray) ." ON DUPLICATE KEY UPDATE Status = 'RENEW',IsActive = 'Active'");
        }

        if (!empty($boolean)) {
            //开始更新domain表domainname字段为空
            $uArray = array();
            foreach ($rows as $row) {
                $domain = isset($row['Domain']) ? $row['Domain'] : '';
                !empty($domain) && array_push($uArray, "'" . $row['Domain'] . "'" );
            }
            if (!empty($uArray)) {
                $uSql = "UPDATE `domain` SET DomainName = NULL WHERE Domain IN (" .implode(',', $uArray) . ")";

                $this->query($uSql);
            }
        }

        return $boolean;
    }


    public function doMergeStoreDomains($alias = null,$merged = 0,$domains=array())
    {
        $sql_names_set = 'SET NAMES utf8';
        $this->query($sql_names_set);
        $alias = mysql_real_escape_string($alias);
        $merged = mysql_real_escape_string($merged);
        $boolean = false;
        if (empty($alias) || empty($merged) || empty($domains)) {
            return $boolean;
        }
        //删除非合并商家的domains
        $dSql = "UPDATE store_custom r SET IsActive = 'Inactive' WHERE r.StoreId = '".$merged."' AND r.Domain NOT IN (".implode(',',$domains) . ")";
        $this->query($dSql);
        $sql = "SELECT d.Domain,r.StoreId AS `ID` FROM  r_store_domain r LEFT JOIN domain d ON d.ID = r.DomainId WHERE r.DomainAffSupport = 'YES' AND  r.StoreId = '".$merged."' AND d.ID IN (".implode(',',$domains) . ")";
        $rows = $this->getRows($sql);
        $opName = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'system';
        $iSql = "INSERT INTO `store_custom`(Domain,CustomName,Operator,StoreId) VALUES";
        $iArray = array();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                array_push($iArray, "('".$row['Domain'] . "','$alias','$opName',".$row['ID'].")") ;
            }
        }
        if (!empty($iArray)) {
            $boolean =  $this->query($iSql . implode(',', $iArray) ." ON DUPLICATE KEY UPDATE Status = 'RENEW',IsActive = 'Active'");
        }
        if (!empty($boolean)) {
            //开始更新domain表domainname字段为空
            $uArray = array();
            foreach ($rows as $row) {
                $domain = isset($row['Domain']) ? $row['Domain'] : '';
                !empty($domain) && array_push($uArray, "'" . $row['Domain'] . "'" );
            }
            if (!empty($uArray)) {
                $uSql = "UPDATE `domain` SET DomainName = NULL WHERE Domain IN (" .implode(',', $uArray) . ")";

                $this->query($uSql);
            }
        }

        return $boolean;
    }




    public function doSetStoreActive($alias = null,$merged = null,$status = 'Active')
    {
        $sql_names_set = 'SET NAMES utf8';
        $this->query($sql_names_set);
        if (!empty($alias) && !empty($merged)) {
            $alias = mysql_real_escape_string($alias);
            $merged = mysql_real_escape_string($merged);
            $oSql = "SELECT d.Domain FROM store s LEFT JOIN r_store_domain r ON s.ID = r.StoreId LEFT JOIN domain d ON d.ID = r.DomainId WHERE r.DomainAffSupport = 'YES' AND  s.Name = '".$merged."' OR s.NameOptimized = '".$merged . "'";
            $oRows = $this->getRows($oSql);
            $oSql = "UPDATE `store_custom` SET IsActive = '$status',Status = 'RENEW' WHERE ID IN (";
            $oArray = array();
            if (!empty($oRows)) {
                foreach ($oRows as $row) {
                    $cSql = "SELECT * FROM `store_custom` WHERE Domain = '" . $row['Domain'] . "' AND CustomName = '$alias'";
                    $oCheck = $this->getRow($cSql);
                    !empty($oCheck) && array_push($oArray, $oCheck['ID']) ;
                }
            }
            if(!empty($oArray)){
                return $this->query($oSql . implode(',', $oArray) . ')');
            }
        }

        return false;
    }
}
