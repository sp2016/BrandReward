<?php
class transaction extends LibFactory{
    var $AffNameIdMap = array();
    var $AffNameStatusMap = array();
    var $data_dir = '';
    var $add_file = array();
    var $update_file = array();

    function __construct(){
        parent::__construct();
        global $aff_conf;
        $this->AffNameIdMap = _array_column($aff_conf,'affid','af');
        $this->AffNameStatusMap = _array_column($aff_conf,'status','af');
        foreach($this->AffNameStatusMap as $af=>$status){
             foreach($status as $k=>$v){
                 $this->AffNameStatusMap[$af][strtolower($k)] = $v;
             }
        }
    }

    function info(){
        $method = array(
                array(
                    'name'=>'update_data',
                    'desc'=>'update transaction to database with type (increase data which is diffrence)',
                    'argv'=>'',
                    ),
                array(
                    'name'=>'get_data',
                    'desc'=>'get transaction data by param',
                    'argv'=>'createdDate(:2015-05-05)|updateDate(:2015-05-05),[afname],[site]',
                    ),
                array(
                    'name'=>'get_data_log',
                    'desc'=>'get transaction data(only change log) by param',
                    'argv'=>'createdDate(:2015-05-05)|updateDate(:2015-05-05),[afname]',
                    ),
                array(
                    'name'=>'search_data_by_sid',
                    'desc'=>'search transaction data by sid',
                    'argv'=>'sid',
                    ),
                array(
                    'name'=>'info_data',
                    'desc'=>'info transaction data with (domainUsed,programId,Visited,VisitedDate,Alias)',
                    'argv'=>'',
                    ),
                 array(
                     'name'=>'update_BRID',
                     'desc'=>'to handle the unknown transaction data',
                     'argv'=>'',
                     ),
                );
        return $method;
    }

    function t_id_encode($time,$id){
        $time_int = strtotime($time);
        $id_int = 100000000 + $id%899999999;
        $loop = floor($id/899999999);
        $serverid = 'bdg02';
        $uuid = dechex($time_int).'-'.$serverid.'-'.$loop.'-'.dechex($id_int).chr(rand(97,122)).chr(rand(97,122));
        return $uuid;
    }

    function update_BRID(){
        debug('start update BRID...'.date('Y-m-d H:i:s'));
        $pz = 500;
        while(1){
            $sql = 'SELECT ID,Created FROM rpt_transaction_unique WHERE BRID = "" LIMIT '.$pz;
            $row = $this->getRows($sql);

            if(empty($row))
                break;

            foreach($row as $k=>$v){
                $row[$k]['BRID'] = $this->t_id_encode($v['Created'],$v['ID']);
                unset($row[$k]['Created']);
            }

            if(!empty($row)){
                $sql = $this->getBatchUpdateSql($row,'rpt_transaction_unique','ID');    
                $this->query($sql);
            }
        }
        debug('end update BRID...'.date('Y-m-d H:i:s'));
    }

    function update_unknown(){
        debug('start update unknown...'.date('Y-m-d H:i:s'));
        #将所有site = unknown & referrer != '' 的记录检查状态更新为0 , 每次更新记录都重新检查一遍
        $sql = 'UPDATE rpt_transaction_unique SET ReferrerCheck = 0 WHERE Site = "unknown" AND Referrer != ""';
        $this->query($sql);

        $pz = 500;
        $i=0;
        while(1){
            $sql = 'SELECT ID,Created,CreatedDate,Commission,Referrer from rpt_transaction_unique WHERE Site = "unknown" AND Referrer != "" and ReferrerCheck = 0 LIMIT '.$pz;
            $rows = $this->getRows($sql);

            if(empty($rows)){
                debug('end update unknown...'.date('Y-m-d H:i:s'));
                break;
            }

            $up_data = array();

            $uniqueids = array();
            foreach($rows as $k=>$v){
                $uniqueids[] = $v['ID'];
                $i++;
                if(preg_match('/https?:\/\/r.brandreward.com/',$v['Referrer'])){
                    preg_match('/key=(.*?)[&|$]/',$v['Referrer'],$m);
                    if(isset($m[1]) && !empty($m[1])){
                        $sql = 'SELECT * FROM publisher_account as a left join publisher as p on a.PublisherId = p.ID WHERE a.ApiKey = "'.addslashes($m[1]).'"';
                        $p = $this->getRow($sql);
                        if($p){

                            preg_match('/id=(.*?)[&|$]/',$v['Referrer'],$nn);
                            $publishTracking = isset($nn[1])?$nn[1]:'';

                            $Tax = intval($p['Tax']);
                            $RefId = intval($p['RefID']);
                            $RefRate = $RefId>0?intval($p['RefRate']):0;
                            $ShowRate = 100 - $Tax - $RefRate;

                            $Commission = $v['Commission'];
                            $RefCommission = 0;
                            if($RefRate > 0){
                                $RefCommission = bcmul($Commission,$RefRate,4);
                                $RefCommission = bcdiv($RefCommission,100,4);
                            }
                            $ShowCommission = 0;
                            if($ShowRate > 0){
                                $ShowCommission = bcmul($Commission,$ShowRate,4);
                                $ShowCommission = bcdiv($ShowCommission,100,4);
                            }
                            $TaxCommission = $Commission - $RefCommission - $ShowCommission;

                            $up_data[] = array(
                                'ID'=>$v['ID'],
                                'Site'=>$p['ApiKey'],
                                'Alias'=>$p['Alias'],
                                'PublishTracking'=>$publishTracking,
                                'Visited'=>$v['Created'],
                                'VisitedDate'=>$v['CreatedDate'],
                                'Tax'=>$Tax,
                                'TaxCommission'=>$TaxCommission,
                                'RefPublisherId'=>$RefId,
                                'RefRate'=>$RefRate,
                                'RefCommission'=>$RefCommission,
                                'ShowRate'=>$ShowRate,
                                'ShowCommission'=>$ShowCommission,
                                'ReferrerCheck'=>1,
                            );

                        }
                    }
                }
            }

            $sql = 'UPDATE rpt_transaction_unique SET ReferrerCheck = 1 WHERE id in ('.join(',',$uniqueids).')';
            $this->query($sql);

            if(!empty($up_data)){
                $sql = $this->getBatchUpdateSql($up_data,'rpt_transaction_unique','ID');    
                $this->query($sql);
            }
        }
        
    }

    function search_data_by_sid($data){
        $data['sid'] = urldecode($data['sid']);
        if(!isset($data['sid']) || empty($data['sid'])){
            return '';
        }

        $sid = $data['sid'];
        if(strpos($sid,',') !== false){
            $tmp = explode(',',$sid);
            foreach($tmp as $k=>$v){
                if(empty($v)){
                    unset($tmp[$k]);
                    continue;
                }
                $tmp[$k] = addslashes($v);
            }
            $sid = join('","',$tmp);
        }else{
            $sid = addslashes($sid);
        }
        $rows = $this->table('bd_out_tracking')->where('sessionId IN ("'.$sid.'")')->field('publishTracking,sessionId')->find();

        $contents = '';
        if(!empty($rows)){
            foreach($rows as $k=>$v){
                $contents .= $v['sessionId']."\t".$v['publishTracking']."\n";
            }
        }
        
        echo $contents;
        exit();
    }

    function get_data($data){
        $row = array();
        $content = '';

        if(isset($data['createddate']) || isset($data['updateddate'])){
            $filter = array();

            if(isset($data['createddate']) && $data['createddate']){
                $filter[] = 'CreatedDate = "'.addslashes($data['createddate']).'"';
            }

            if(isset($data['updateddate']) && $data['updateddate']){
                $filter[] = 'UpdatedDate = "'.addslashes($data['updateddate']).'"';
            }

            if(isset($data['afname'])){
                $filter[] = 'Af = "'.addslashes($data['afname']).'"';
            }

            if(isset($data['site'])){
                $filter[] = 'site = "'.addslashes($data['site']).'"';
            }



            $where_str = join(' AND ',$filter);
            $column = 'Af,Created,Updated,Sales,Commission,IdInAff,ProgramName,OrderId,TradeKey,SID,PublishTracking,Site';
            $row = $this->table('rpt_transaction_unique')->where($where_str)->field($column)->find();
            if(!empty($row)){
                foreach($row as $k=>$v){
                    $content .= join("\t",$v)."\n";
                }
            }
        }

        echo $content;
        exit();
    }

    function get_data_log($data){
        $row = array();
        $content = '';

        if(isset($data['createddate']) || isset($data['updateddate'])){
            $filter = array();

            if(isset($data['createddate']) && $data['createddate']){
                $filter[] = 'a.CreatedDate = "'.addslashes($data['createddate']).'"';
            }

            if(isset($data['updateddate']) && $data['updateddate']){
                $filter[] = 'a.UpdatedDate = "'.addslashes($data['updateddate']).'"';
            }

            if(isset($data['afname'])){
                $filter[] = 'a.Af = "'.addslashes($data['afname']).'"';
            }

            $where_str = join(' AND ',$filter);
            $sql = 'SELECT 
                    a.Af,a.Created,a.Updated,a.Sales,a.Commission,a.IdInAff,a.ProgramName,a.OrderId,a.SID,a.TradeKey,b.PublishTracking,b.Site 
                    FROM rpt_transaction_base AS a 
                    LEFT JOIN rpt_transaction_unique AS b ON a.TradeKey = b.TradeKey 
                    WHERE '.$where_str;
            $row = $this->getRows($sql);
            if(!empty($row)){
                foreach($row as $k=>$v){
                    $content .= join("\t",$v)."\n";
                }
            }
        }

        echo $content;
        exit();
    }

    function update_data($data){
        debug('start update data...'.date('Y-m-d H:i:s'));
        $this->data_dir = CRAWL_FILE_DIR;  // /home/bdg/transaction/server_transaction/data
        $this->get_update_file($data);
        $this->do_update_file();

        $this->info_data();
        $this->update_BRID();
        $this->update_unknown();
        debug('end update data...'.date('Y-m-d H:i:s'));
    }

    function info_data(){
        debug('start info data...');
        #update site
        #update affid
        #update publishTracking

        $site_tracking_code = array(
            's01'=>'csus',
            's09'=>'csca',
            's17'=>'csau',
            's02'=>'csuk',
            's29'=>'csde',
            's49'=>'csusmob',
            's10'=>'csie',
            's32'=>'csnz',
            's42'=>'ds',
            's70'=>'hd',
            's16'=>'dealsalbum',
            's16'=>'dealsalbum',
            's46'=>'dc2012',
            's501'=>'acapp',
            's15'=>'anypromocodes',
            's08'=>'c4lp',
            's40'=>'coupondealpro',
            's43'=>'cs6rlease',
            's05'=>'cs3soft',
            's06'=>'cs4soft',
            's07'=>'cs4softus',
            's38'=>'seekcoupon',
            's03'=>'codes',
            's04'=>'perfect',
            's36'=>'esw4u',
            's52'=>'cs6upgrade',
            's37'=>'ifunbox',
            's45'=>'shopwithcoupon',
            's59'=>'laihaitao',
            's61'=>'fiberforme',
            's39'=>'tipdownload',
            's63'=>'ccm',
            's71'=>'fscoupon',
            's64'=>'walletsaving',
            's69'=>'paydayloan',
            's65'=>'appholic',
            's47'=>'bfdc',
            's73'=>'dsfr',
            's72'=>'dscn',
            's76'=>'csuk_in',
            's74'=>'csfr',
            's75'=>'dsde',
            );

        $site_alias_map = array(
            'bfdc'        =>'c4ca4238a0b923820dcc509a6f75849b',
            'csde'        =>'c81e728d9d4c2f636f067f89cc14862c',
            'hd'          =>'a87ff679a2f3e71d9181a67b7542122c',
            'ds'          =>'eccbc87e4b5ce2fe28308fd9f2a7baf3',
            'csuk'        =>'e4da3b7fbbce2345d7772b0674a318d5',
            'csus'        =>'1679091c5a880faf6fb5e6087eb1b2dc',
            'csau'        =>'8f14e45fceea167a5a36dedd4bea2543',
            'csca'        =>'c9f0f895fb98ab9159f51fd0297e236d',
            'csfr'        =>'45c48cce2e2d7fbdea1afc51c7c6ad26',
            'dsfr'        =>'d3d9446802a44259755d38e6d163e820',
            'dscn'        =>'6512bd43d9caa6e02c990b0a82652dca',
            'bdg'         =>'c20ad4d76fe97759aa27a0c99bff6710',
            'csuk_in'     =>'aab3238922bcc25a6f606eb525ffdc56',
            'dsde'        =>'9bf31c7ff062936a96d3c8bd1f8f2ff3',
        );

        $pageSize = 500;
        $debug_data = array();
        $countRow = $this->table('rpt_transaction_unique')->where('Site = "" OR Changed = 1')->count()->findone();
        $count = $countRow['tp_count'];

        $debug_data['pCount'] = $count;
        $page = ceil($count / $pageSize);
        $debug_data['page'] = $page;

        if($count > 0 ){
            $i = 0;
            while(1){
                $i++;
                $sql = 'SELECT a.af,a.ID,a.SID,a.AffId as OAffId,a.ProgramName as oProgramName,a.IdInAff as oIdInAff,a.Commission,a.TradeStatus,a.State,a.PaidDate,a.FineDate FROM rpt_transaction_unique as a  WHERE a.site = "" OR a.Changed = 1 LIMIT '.$pageSize;
                $row = $this->getRows($sql);

                if(empty($row))
                    break;

                $sids = array();
                foreach($row as $v){
                    if(!empty($v['SID'])){
                        $sids[] = $v['SID'];
                    }
                }

                $outlog = array();
                if(!empty($sids)){

                    $sql = "SELECT sessionId,site,publishTracking,created,domainUsed,domainId,programId,linkid,country FROM bd_out_tracking WHERE sessionid IN ('".join("','",$sids)."')";
                    $row_outlog = $this->getRows($sql);
                    
                    if(!empty($row_outlog)){
                        $programId_arr = array();
                        $site_arr = array();
                        foreach($row_outlog as $k=>$v){
                            $programId_arr[] = $v['programId'];
                            $site_arr[] = $v['site'];
                        }
                        $sql = "SELECT ID,AffId,Name,IdInAff FROM program WHERE ID IN (".join(',',$programId_arr).")";
                        $row_program = $this->getRows($sql);
                        $map_program = array();
                        foreach($row_program as $k=>$v){
                            $map_program[$v['ID']] = $v;
                        }
                        
                        $sql = "SELECT d.ApiKey,d.Alias,p.Tax,p.RefId,p.RefRate FROM 
                            publisher_account AS d  
                            LEFT JOIN publisher AS p ON d.PublisherId = p.ID 
                            WHERE d.ApiKey IN ('".join("','",$site_arr)."')";
                        $row_site = $this->getRows($sql);
                        $map_site = array();
                        foreach($row_site as $k=>$v){
                            $map_site[$v['ApiKey']] = $v;
                        }
                        foreach($row_outlog as $k=>$v){
                            $row_outlog[$k]['programName'] = isset($map_program[$v['programId']])?$map_program[$v['programId']]['Name']:'';
                            $row_outlog[$k]['AffId'] = isset($map_program[$v['programId']])?$map_program[$v['programId']]['AffId']:0;
                            $row_outlog[$k]['IdInAff'] = isset($map_program[$v['programId']])?$map_program[$v['programId']]['IdInAff']:'';
                            $row_outlog[$k]['Alias'] = isset($map_site[$v['site']])?$map_site[$v['site']]['Alias']:'';
                            $row_outlog[$k]['Tax'] = isset($map_site[$v['site']])?$map_site[$v['site']]['Tax']:0;
                            $row_outlog[$k]['RefId'] = isset($map_site[$v['site']])?$map_site[$v['site']]['RefId']:0;
                            $row_outlog[$k]['RefRate'] = isset($map_site[$v['site']])?$map_site[$v['site']]['RefRate']:0;
                        }
                    }
/*
                    $sql = 'SELECT b.sessionId,b.site,b.publishTracking,b.created,b.domainUsed,b.domainId,b.programId,b.linkid,c.AffId,c.Name AS programName,c.IdInAff,d.Alias,p.Tax,p.RefId,p.RefRate,b.country  
                            FROM bd_out_tracking AS b 
                            LEFT JOIN program AS c ON b.programId = c.ID 
                            LEFT JOIN publisher_account AS d ON b.site = d.ApiKey 
                            LEFT JOIN publisher AS p ON d.PublisherId = p.ID 
                            WHERE b.sessionid IN ("'.join('","',$sids).'")';

                    $row_outlog = $this->getRows($sql);
*/
                    foreach($row_outlog as $k=>$v){
                        $outlog[$v['sessionId']] = $v;
                    }
                }

                foreach($row as $k=>$v){
                    $row[$k]['site'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['site']:'';
                    $row[$k]['publishTracking'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['publishTracking']:'';
                    $row[$k]['created'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['created']:'';
                    $row[$k]['domainUsed'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['domainUsed']:'';
                    $row[$k]['domainId'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['domainId']:'';
                    $row[$k]['programId'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['programId']:'';
                    $row[$k]['AffId'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['AffId']:'';
                    $row[$k]['programName'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['programName']:'';
                    $row[$k]['IdInAff'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['IdInAff']:'';
                    $row[$k]['Alias'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['Alias']:'';
                    $row[$k]['Tax'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['Tax']:'';
                    $row[$k]['RefId'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['RefId']:'';
                    $row[$k]['RefRate'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['RefRate']:'';
                    $row[$k]['Country'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['country']:'';
                    $row[$k]['linkId'] = isset($outlog[$v['SID']])?$outlog[$v['SID']]['linkid']:0;
                }

                $up_data = array();

                foreach($row as $k=>$v){
                    $af = $v['af'];
                    $id = $v['ID'];
                    $sid = $v['SID'];
                    $site = $v['site'];
                    $created = $v['created'];
                    $domainUsed = $v['domainUsed'];
                    $domainId = $v['domainId'];
                    $programId = $v['programId'];
                    $programName = $v['programName']?$v['programName']:$v['oProgramName'];
                    $programIdInAff = $v['IdInAff']?$v['IdInAff']:$v['oIdInAff'];
                    $affid = $v['AffId']?$v['AffId']:$v['OAffId'];
                    $alias = $v['Alias'];
                    $publishTracking = $v['publishTracking'];
                    $Country = $v['Country'];
                    $linkId = $v['linkId'];
                    
                    if(!$site && $sid){
                        list($code) = explode('_',$sid);
                        if(isset($site_tracking_code[$code])){
                            $alias = $site_tracking_code[$code];
                            $site = isset($site_alias_map[$alias])?$site_alias_map[$alias]:$alias;
                        }
                    }
                    if(!$site && $publishTracking){
                        list($code) = explode('_',$publishTracking);
                        if(isset($site_tracking_code[$code])){
                            $alias = $site_tracking_code[$code];
                            $site = isset($site_alias_map[$alias])?$site_alias_map[$alias]:$alias;
                        }
                    }
                    $site = $site?$site:'unknown';
                    $alias = $alias?$alias:$site;

                    #add row Tax
                    $Tax = intval($v['Tax']);
                    $RefId = intval($v['RefId']);
                    $RefRate = $RefId>0?intval($v['RefRate']):0;
                    $ShowRate = 100 - $Tax - $RefRate;
                    if($v['FineDate'] != '0000-00-00'){
                        $ShowRate = $RefRate = 0;
                        $Tax = 100;
                    }

                    $Commission = $v['Commission'];
                    $RefCommission = 0;
                    $ShowCommission = 0;
                    $TaxCommission = 0;
                    if($ShowRate == 100){
                        $ShowCommission = $Commission;
                        $RefCommission = $TaxCommission = 0;
                    }elseif($Tax == 100){
                        $TaxCommission = $Commission;
                        $RefCommission = $ShowCommission = 0;
                    }else{
                        $ShowCommission = bcmul($Commission,$ShowRate,4);
                        if($ShowCommission < 1 && $ShowCommission > -1){
                            $ShowCommission = 0;
                        }else{
                            $ShowCommission = number_format(bcdiv($ShowCommission,100,4),2,'.','');
                        }   

                        $RefCommission = bcmul($Commission,$RefRate,4);
                        if($RefCommission < 1 && $RefCommission > -1){
                            $RefCommission = 0;
                        }else{
                            $RefCommission = number_format(bcdiv($RefCommission,100,4),2,'.','');
                        }   

                        $TaxCommission = $Commission - $RefCommission - $ShowCommission;
                    }

                    $state = $v['State'];
                    if($v['State'] != 'REMOVED'){
                        $stateMap = $this->AffNameStatusMap[$af];
                        $state = isset($stateMap[$v['TradeStatus']])?$stateMap[$v['TradeStatus']]:'PENDING';
                    }
                    
                    $up_data[] = array(
                        'ID'=>$id,
                        'Site'=>$site,
                        'Alias'=>$alias,
                        'Affid'=>$affid,
                        'PublishTracking'=>$publishTracking,
                        'Visited'=>$created,
                        'VisitedDate'=>substr($created,0,10),
                        'domainUsed'=>$domainUsed,
                        'domainId'=>$domainId,
                        'ProgramId'=>$programId,
                        'ProgramName'=>$programName,
                        'IdInAff'=>$programIdInAff,
                        'Tax'=>$Tax,
                        'TaxCommission'=>$TaxCommission,
                        'RefPublisherId'=>$RefId,
                        'RefRate'=>$RefRate,
                        'RefCommission'=>$RefCommission,
                        'ShowRate'=>$ShowRate,
                        'ShowCommission'=>$ShowCommission,
                        'Changed'=>0,
                        'Country'=>$Country,
                        'linkId'=>$linkId,
                        'State'=>$state,
                    );

                }

                if(!empty($up_data)){
                    $sql = $this->getBatchUpdateSql($up_data,'rpt_transaction_unique','ID');    
                    $this->query($sql);
                }
                
                $debug_data['doing'] = $i;
                debug('doing info data...all count('.$debug_data['pCount'].') doing page('.$i.'/'.$debug_data['page'].')');
            }
        }
        debug('end info data...');
    }

    function get_update_file($data){   //find files from rpt_transaction_file to update on the basis of md5
        debug('do check file md5...');
        $dir_name = getDir($this->data_dir,'dir',true);
        $dir_name_active = array_keys($this->AffNameIdMap);
        $dir_used = array_intersect($dir_name, $dir_name_active);

        foreach($dir_used as $k=>$v){
            $dir_used[$k] = $this->data_dir.'/'.$v;
        }

        $data_file = getDir($dir_used,'file');//all upd or dat files
        #get data file by time 
        foreach($data_file as $k=>$v){
            if(substr($v,-3) != 'dat'){ //filter files not dat
                unset($data_file[$k]);
                continue;
            }
                
            $file_time = substr(basename($v),8,-4);
            if(strtotime($file_time) < strtotime('2013-05-17')){
                unset($data_file[$k]);
                continue;
            }

            if(isset($data['from']) && strtotime($file_time) < strtotime($data['from'])){
                unset($data_file[$k]);
                continue;
            }

            if(isset($data['to']) && strtotime($file_time) > strtotime($data['to'])){
                unset($data_file[$k]);
                continue;
            }
        }

        #get data file if is changed by MD5
        $data_dir_len = strlen($this->data_dir);
        $data_file_info = array();
        foreach($data_file as $k=>$v){
            $fullname = $v;
            $pos = substr($v,$data_dir_len);
            $md5 = md5_file($v);

            $data_file_info[$pos]['file_path'] = $pos;
            $data_file_info[$pos]['file_md5'] = $md5;
        }
        unset($data_file);

        $file_path_list = array_keys($data_file_info);

        $file_path_in_database = array();
        if(!isset($data['cover']) || !$data['cover']){
            $tmp = $this->table('rpt_transaction_file')->where('file_path IN ("'.join('","',$file_path_list).'")')->find();
            foreach($tmp as $k=>$v){
                $file_path_in_database[$v['file_path']] = $v;
            }
            unset($tmp);    
        }

        $update_file = array();
        foreach($data_file_info as $k=>$v){
            if(isset($file_path_in_database[$k])){
                if($v['file_md5'] != $file_path_in_database[$k]['file_md5']){
                    $update_file[] = $v;
                }
            }else{
                $update_file[] = $v;
            }
        }
//$update_file = array(array('file_path'=>'/adcell/revenue_20170701.dat','file_md5'=>'123'));
        $this->update_file = $update_file;
    }

    function do_update_file(){
        foreach($this->update_file as $k=>$v){
            $this->do_update_data($v);
            debug('do update file md5 : '.$v['file_path']);
            //update file md5 recode

            $sql = 'SELECT af,max(updated) as updated From rpt_transaction_unique WHERE DataFile = "'.$v['file_path'].'" ';
            $rs = $this->getRows($sql);

            $sql = 'REPLACE INTO rpt_transaction_file SET file_path = "'.addslashes($v['file_path']).'" , file_md5 = "'.addslashes($v['file_md5']).'",af = "'.$rs[0]['af'].'",updated = "'.$rs[0]['updated'].'"';
            $this->query($sql);
        }
    }

    function format_txt2data($af,$Arr){
        $tmp = array();
        #交易生成时间使用联盟返回的数据,交易修改时间统一使用当前程序更新时间
        
        $tmp['Created'] = addslashes($Arr[0]);
        $tmp['CreatedDate'] = date('Y-m-d',strtotime($tmp['Created']));
        $tmp['Updated'] = date('Y-m-d H:i:s');
        $tmp['UpdatedDate'] = date('Y-m-d');

        $tmp['Sales'] = floatval($Arr[2]);
        $tmp['Commission'] = floatval($Arr[3]);
        $tmp['IdInAff'] = addslashes($Arr[4]);
        $tmp['ProgramName'] = addslashes($Arr[5]);
        $tmp['SID'] = addslashes($Arr[6]);
        $tmp['OrderId'] = isset($Arr[7])?addslashes($Arr[7]):'';
        $tmp['ClickTime'] = isset($Arr[8])?addslashes($Arr[8]):'';
        $tmp['TradeId'] = isset($Arr[9])?addslashes($Arr[9]):'';
        $tmp['TradeStatus'] = isset($Arr[10])?addslashes($Arr[10]):'';
        $tmp['OldCur'] = isset($Arr[11])?addslashes($Arr[11]):'';
        $tmp['OldSales'] = isset($Arr[12])?addslashes($Arr[12]):'';
        $tmp['OldCommission'] = isset($Arr[13])?addslashes($Arr[13]):'';
        $tmp['TradeType'] = isset($Arr[14])?addslashes($Arr[14]):'';
        $tmp['Referrer'] = isset($Arr[15])?addslashes($Arr[15]):'';

        $rejected_code = array('storniert','cancelled','declined','refunded','rejected','void','invalidated','denied');

        $tmp['TradeStatus'] = strtolower($tmp['TradeStatus']);
        $tmp['TradeCancelReason'] = isset($Arr[16])?addslashes($Arr[16]):'';
        if(in_array($tmp['TradeStatus'], $rejected_code)){
            $tmp['Commission'] = 0;
            $tmp['Sales'] = 0;
        }
       
        $stateMap = $this->AffNameStatusMap[$af];
        $tmp['State'] = isset($stateMap[$tmp['TradeStatus']])?$stateMap[$tmp['TradeStatus']]:'PENDING';

        return $tmp;
    }

    function do_update_data($data){
        $af = dirname($data['file_path']);
        $af = $af[0] == '/'?substr($af,1):$af;
        $AffId = 0;
        if(isset($this->AffNameIdMap[$af])){
            $AffId = $this->AffNameIdMap[$af];
        }
        
        debug('do update file data : '.$data['file_path']);
        //add transaction data to database
        $file_data = array();

        $file_full_path = $this->data_dir.$data['file_path'];
        
        if(is_file($file_full_path)){
            $fp=fopen($file_full_path,'r');
            while(!feof($fp)){
                $line=fgets($fp,4000);
                $line = trim($line);
                if(empty($line))
                    continue;

                $line = mb_convert_encoding($line,'UTF-8'); 
                $Arr = explode("\t",$line);
                if(count($Arr) < 5)
                    continue;

                $tmp = $this->format_txt2data($af,$Arr);      //extract values of some field from upd or dat files
                $tmp['Af'] = addslashes($af);
                $tmp['AffId'] = intval($AffId);
                $tmp['TradeKey'] = $this::getTradeKey($tmp['Af'],$tmp['Created'],$tmp['TradeId']); //使用TradeId，在文件中这个值保存的是联盟交易记录的uniqueid，由于SID存在后期找回的情况。所以暂时不用来定义唯一识别符
                $tmp['DataFile'] = $data['file_path'];
                $file_data[] = $tmp;
            }
            fclose($fp);
        }
        $this->do_update_data_step($file_data);
    }

    function do_update_data_step($file_data){ //find transactions to update to rpt_transaction_base
        $update_data = array();

        if(!empty($file_data)){
            $DataFile = $file_data[0]['DataFile'];
            
            $TradeKey_tmp = array();
            foreach($file_data as $k=>$v){
                $TradeKey_tmp[] = $v['TradeKey'];
            }

            $where_str = 'Af = "'.$file_data[0]['Af'].'" AND `DataFile` = "'.$DataFile.'" ';
            // $where_str .= 'AND TradeKey IN ("'.join('","', $TradeKey_tmp ).'")';
            $db_data = $this->table('rpt_transaction_unique')->where($where_str)->field('Created,CreatedDate,Updated,UpdatedDate,Sales,Commission,IdInAff,ProgramName,SID,OrderId,ClickTime,TradeId,TradeStatus,OldCur,OldSales,OldCommission,TradeType,Referrer,Af,AffId,TradeKey,DataFile,TradeCancelReason')->find();

            $db_data_base = $this->table('rpt_transaction_base')->where($where_str)->field('Created,CreatedDate,Updated,UpdatedDate,Sales,Commission,IdInAff,ProgramName,SID,OrderId,ClickTime,TradeId,TradeStatus,OldCur,OldSales,OldCommission,TradeType,Referrer,Af,AffId,TradeKey,DataFile')->find();

            $f_d = array();
            //format file data
            foreach($file_data as $k=>$v){
                if(!isset($f_d[$v['TradeKey']])){
                    $f_d[$v['TradeKey']] = $v;
                }else{
                    $f_d[$v['TradeKey']]['Commission'] = bcadd($f_d[$v['TradeKey']]['Commission'], $v['Commission'], 4);
                    $f_d[$v['TradeKey']]['Sales'] = bcadd($f_d[$v['TradeKey']]['Sales'], $v['Sales'], 4);
                }
            }

            #get update data
            $d_d_uniq = array(); //rpt_transaction_unique
            $d_d_base = array(); //rpt_transaction_base

            //format db data
            foreach($db_data as $k=>$v){
                $d_d_uniq[$v['TradeKey']] = $v;
            }

            foreach($db_data_base as $k=>$v){
                if(!isset($d_d_base[$v['TradeKey']])){
                    $d_d_base[$v['TradeKey']] = $v;
                }else{
                    $d_d_base[$v['TradeKey']]['Commission'] = bcadd($d_d_base[$v['TradeKey']]['Commission'], $v['Commission'], 4);
                    $d_d_base[$v['TradeKey']]['Sales'] = bcadd($d_d_base[$v['TradeKey']]['Commission'], $v['Commission'], 4);
                }
                $d_d_base[$v['TradeKey']]['TradeStatusArr'][] = $v['TradeStatus'];
            }

            $update_uniq = array();
            $update_base = array();
            foreach($f_d as $k=>$v){  //compaire $f_d with $d_d to decide which transaction to insert
                if(!isset($d_d_uniq[$k])){
                    $update_uniq[] = $v;
                }elseif($v['Sales'] != $d_d_uniq[$k]['Sales'] || $v['Commission'] != $d_d_uniq[$k]['Commission'] || $v['TradeStatus'] != $d_d_uniq[$k]['TradeStatus'] || $v['TradeCancelReason'] != $d_d_uniq[$k]['TradeCancelReason'] || $v['Referrer'] != $d_d_uniq[$k]['Referrer']){
                    $update_uniq[] = $v;
                }
                if(!isset($d_d_base[$k])){
                    $update_base[] = $v;
                }elseif($v['Sales'] != $d_d_base[$k]['Sales'] || $v['Commission'] != $d_d_base[$k]['Commission'] || !in_array($v['TradeStatus'],$d_d_base[$k]['TradeStatusArr'])){
                    $tmp = $v;
                    $tmp['Commission'] = bcsub($v['Commission'], $d_d_base[$k]['Commission'], 4);
                    $tmp['Sales'] = bcsub($v['Sales'], $d_d_base[$k]['Sales'], 4);
                    $update_base[] = $tmp;
                }
            }

            foreach($d_d_uniq as $k=>$v){
                if(!isset($f_d[$k])){
                    $tmp = $v;
                    $tmp['Commission'] = 0;
                    $tmp['Sales'] = 0;
                    $tmp['State'] = 'REMOVED';
                    $tmp['TradeCancelReason'] = 'REMOVED';
                    $update_uniq[] = $tmp;
                }
            }

            foreach($d_d_base as $k=>$v){
                if( !isset($f_d[$k]) && ($v['Commission'] != 0 || $v['Sales'] != 0) ){
                    $tmp = $v;
                    $tmp['Commission'] = bcsub(0, $v['Commission'], 4);
                    $tmp['Sales'] = bcsub(0, $v['Sales'], 4);
                    unset($tmp['TradeCancelReason']);
                    unset($tmp['TradeStatusArr']);
                    $update_base[] = $tmp;
                }
            }

            //update rpt_transaction_unique final data, unique
            if(!empty($update_uniq)){
                $tmp_data = array();
                foreach($update_uniq as $k=>$v){
                    $update_uniq[$k]['Updated'] = date('Y-m-d H:i:s');
                    $update_uniq[$k]['UpdatedDate'] = date('Y-m-d');
                    $update_uniq[$k]['Changed'] = 1;
                    $tmp_data[] = $update_uniq[$k];
                    if(count($tmp_data) > 99){
                        $sql = $this->getBatchUpdateSql($tmp_data,'rpt_transaction_unique','TradeKey');
                        $this->query($sql);
                        $tmp_data = array();
                    }
                }
                if(count($tmp_data) > 0){
                    $sql = $this->getBatchUpdateSql($tmp_data,'rpt_transaction_unique','TradeKey');
                    $this->query($sql);
                    $tmp_data = array();
                }
            }

            //update rpt_transaction_base change log
            if(!empty($update_base)){
                $tmp_data = array();
                foreach($update_base as $k=>$v){
                    $update_base[$k]['Updated'] = date('Y-m-d H:i:s');
                    $update_base[$k]['UpdatedDate'] = date('Y-m-d');
                    unset($update_base[$k]['TradeCancelReason']);
                    unset($update_base[$k]['TradeStatusArr']);
                    unset($update_base[$k]['State']);
                    $tmp_data[] = $update_base[$k];
                    if(count($tmp_data) > 99){
                        $sql = $this->getInsertSql($tmp_data,'rpt_transaction_base');
                        $this->query($sql);
                        $tmp_data = array();
                    }
                }
                if(!empty($tmp_data)){
                    $sql = $this->getInsertSql($tmp_data,'rpt_transaction_base');
                    $this->query($sql);
                    $tmp_data = array();
                }
            }

        }
    }

    static function getTradeKey($Af,$Created,$TradeId){
        $key = $Af.'_'.date('YmdHis',strtotime($Created)).'_'.md5($TradeId);
        return $key;
    }
}
?>
