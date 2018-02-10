<?php

class Transaction {

    function __construct(){
        if(!isset($this->Mysql))$this->Mysql = new Mysql();
    }


    //根据联盟ID获取爬取transaction所需信息
    public function getAccountInfoById($affid = 0){
        if($affid){
            $sql = "SELECT ID,Alias,Account,Password FROM wf_aff WHERE ID = {$affid}";
            return $this->Mysql->getRows($sql);
        } else {
            return 0;
        }
    }

    //获取所有具有alias信息的联盟
    public function getAllAffInfo(){
        $sql = "SELECT ID,Alias,Account,Password FROM wf_aff WHERE Alias IS NOT NULL AND Alias NOT LIKE ''";
        $rows = $this->Mysql->getRows($sql);
        if(count($rows)) return $rows;
        else return 0;
    }

}