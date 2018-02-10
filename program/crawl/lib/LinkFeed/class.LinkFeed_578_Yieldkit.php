<?php

class LinkFeed_578_Yieldkit{

    var $key        = "d74bd16aa9e43735b4805218e280db66";
    var $secret     = "ed3550ea4185c62495d98669574cc668";
    var $site_id    = "79ee407e31ce4408bbde169c521ce6c2";
    var $url        = '';
    var $pagesize   = 100;
    var $page       = 1;

    function __construct($aff_id,$oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;       
        $this->info = $oLinkFeed->getAffById($aff_id);                            //返回一维数组，存储当前aff_id对应的各个字段值
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
        $this->HttpCrawler = new HttpCrawler();
        $this->DB = new ProgramDb();
        //$this->Url();
    }

    public function GetProgramFromAff(){
        $start = date("Y-m-d H:i:s");
        echo "##### start @$start#####\n";
        $result = $this->HttpCrawler->GetHttpResult($this->Url(1, 1));
        $result['content'] = $result['content']?(array)json_decode($result['content']): "";//var_dump($result['content']);exit;
        $total = $result['content']['total'] ? $result['content']['total'] : 0;
        echo "###total: $total , page : " . ceil($total/$this->pagesize) . " ###\n";
        $prgm_num = 0;
        for($i = 1; $i <= ceil($total/$this->pagesize); $i++){
        	$data = array();
            echo "#$i-";
            $result = $this->HttpCrawler->GetHttpResult($this->Url($i,$this->pagesize));
            $result['content'] = $result['content'] ? json_decode($result['content'], true) : "";

            echo $result['content']['size'];
            
            if($result['content']['advertisers']) {                
                foreach ($result['content']['advertisers'] as $key => $value) { 
                	//print_r($value);                   
                    $key = $value['id'];
                    $data[$key]['AffId'] = 578;
                    $data[$key]['Name'] = addslashes($value['name']);
                    $data[$key]['Description'] = addslashes($value['description']);
                    $data[$key]['IdInAff'] = $value['id'];
                    $data[$key]['Homepage'] = addslashes($value['url']);
                    $data[$key]['TargetCountryExt'] = implode('|', $value['countries']);
                    $data[$key]['StatusInAff'] = 'Active';
                    $data[$key]['Partnership'] = 'Active';
                    $data[$key]['LastUpdateTime'] = date("Y-m-d H:i:s");
                    $value['trackinglink'] ? $data[$key]['AffDefaultUrl'] = addslashes($value['trackinglink']) : $data[$key]['AffDefaultUrl'] = "";
                    $data[$key]['CommissionExt'] = array();
                    isset($value['currency']) ? true : $value['currency'] = "";
                    isset($value['payPerLead']) ? $data[$key]['CommissionExt'][] = "payPerLead:".$value['currency'].$value['payPerLead'] : false;
                    isset($value['payPerSale']) ? $data[$key]['CommissionExt'][] = "payPerSale:".$value['currency'].$value['payPerSale'] : false;
                    isset($value['minPayPerLead'])&&isset($value['maxPayPerLead'])&&!isset($value['payPerLead']) ? $data[$key]['CommissionExt'][] = "payPerLead:".$value['currency'].$value['minPayPerLead']."~".$value['maxPayPerLead'] : false;
                    isset($value['minPayPerSale'])&&isset($value['maxPayPerSale'])&&!isset($value['payPerSale']) ? $data[$key]['CommissionExt'][] = "payPerSale:".$value['currency'].$value['minPayPerSale']."~".$value['maxPayPerSale'] : false;
                    isset($data[$key]['CommissionExt']) ? $data[$key]['CommissionExt'] = implode("|",$data[$key]['CommissionExt']) : false;
                    isset($value['trackinglink']) ? $data[$key]['AffDefaultUrl'] = addslashes($value['trackinglink']) : $data[$key]['AffDefaultUrl'] = "";
                    isset($value['deeplink'])&& $value['deeplink']? $data[$key]['SupportDeepUrl'] = "YES" : $data[$key]['SupportDeepUrl'] = "UNKNOWN";
                    $prgm_num++;
                    //print_r($data);exit;
                }
                $this->DB->updateProgram(578, $data);                
//                usleep(200);
            }else{
            	echo "ERR";
            }
            echo ";\t";            
        }        
        echo "\n##### end ($prgm_num) @".date("Y-m-d H:i:s")."#####\n";
        
        if($prgm_num < 10){
        	mydie("die: only get $prgm_num p.\n");
        }
        
        /* if($total != $prgm_num){
        	mydie("die: total: $total <> get $prgm_num p.\n");
        } */
        $compare_prgmNum = array(
        		'total' => $total,
        		'prgm_num' => $prgm_num
        );
        $this->checkProgramOffline($this->info["AffId"], $start, $compare_prgmNum);
    }

    public function Url($page = 1,$pagesize = 100){
        //$this->page = $page;
        //$this->pagesize = $pagesize;
        $this->url = "http://api.yieldkit.com/v1/advertiser?api_key=".$this->key."&api_secret=".$this->secret."&site_id=".$this->site_id."&page_size=".$pagesize."&page=".$page."&format=json"; //."&country=US&q=1and1+internet&page_size=10&page=1&format=csv"
        return $this->url;
    }

	function checkProgramOffline($AffId, $check_date, $compare_prgmNum){
		$objProgram = new ProgramDb();
		$prgm = array();
		$prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
		if(count($prgm) > 1000 && $compare_prgmNum['total'] != $compare_prgmNum['prgm_num']){
			mydie("die: too many offline program (".count($prgm).").\n");
		}else{
			$objProgram->setProgramOffline($this->info["AffId"], $prgm);
			echo "\tSet (".count($prgm).") offline program.\r\n";
		}
	}
}