<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT . 'init.php');

if (isset($_POST['act'])) {

    switch ($_POST['act']) {
        case 'check_publish_account':
            if (!isset($_POST['account']) || empty($_POST['account'])) {
                echo 1;
                exit();
            }
            if (!preg_match('/^[0-9a-zA-Z@_.]+$/', $_POST['account'])) {
                echo 2;
                exit();
            }
            $objAccount = new Account();
            $row = $objAccount->table('publisher')->where('UserName = "' . addslashes($_POST['account']) . '"')->findone();
            if ($row) {
                echo 3;
                exit();
            }
            echo 0;
            exit();
            break;
        case 'publish_sign_up':
            $objAccount = new Account();
            $res = $objAccount->new_publisher($_POST);
            //do publisher sign up
            echo $res;
            exit();
            break;
        case 'publish_login':
            $objAccount = new Account();
            $res = $objAccount->login($_POST);
            echo $res;
            exit();
            break;
        case 'publish_logout':
            $objAccount->logout();
            echo 1;
            break;
        case 'publish_change_pwd':
            $data = $_POST;
            $data['ID'] = $USERINFO['ID'];
            $objAccount = new Account();
            $res = $objAccount->change_password($data);
            echo $res;
            exit();
            break;
            case 'show_profile_account':
            $objAccount = new Account;
            $categoryiesOfContent = array(
                    '1.WEB-BASED (DESKTOP/MOBILE)' => array(
                        'a' => 'E-commerce',
                        'b' => 'Price Comparison',
                        'c' => 'Loyalty Websites (Cashback, Incentive, Rewards, Points, etc.)',
                        'd' => 'Cause-Related Marketing',
                        'e' => 'Coupon, Rebate, Deal, Discount Websites',
                        'f' => 'Content and niche market websites',
                        'g' => 'Product Review Site',
                        'h' => 'Blogs (Typically with an RSS feed)',
                        'i' => 'E-mail Marketing',
                        'j' => 'Registration or co-registration',
                        'k' => 'Shopping Directories',
                        'l' => 'Gaming',
//                         'm' => 'Adbars & Toolbars',
                        'n' => 'Virtual currency',
                        'o' => 'File sharing platform',
                        'p' => 'Video sharing platform',
                        'q' => 'Other',
                    ),
                    '2.MOBILE APP' => array(
                        'a' => 'E-commerce',
                        'b' => 'Price Comparison',
                        'c' => 'Loyalty Websites (Cashback, Incentive, Rewards, Points, etc.)',
                        'd' => 'Cause-Related Marketing',
                        'e' => 'Coupon, Rebate, Deal, Discount Websites',
                        'f' => 'Content and niche market websites',
                        'g' => 'Product Review Site',
                        'h' => 'Blogs (Typically with an RSS feed)',
                        'i' => 'E-mail Marketing',
                        'j' => 'Registration or co-registration',
                        'k' => 'Shopping Directories',
                        'l' => 'Gaming',
//                         'm' => 'Adbars & Toolbars',
                        'n' => 'File sharing platform',
                        'o' => 'Video sharing platform',
                        'p' => 'Other',
                    )


                );
            $user_profile = $objAccount->get_account_info($USERINFO['ID']);
           // print_r($user_profile);
            $is_form = isset($_POST['is_form']) ? $_POST['is_form'] : 0;
            $countryOption = getDictionary('country');
            $catArr = getCategory();

            $objTpl->assign('catArr', $catArr);
            $objTpl->assign('user_profile', $user_profile);
            $objTpl->assign('countryOption', $countryOption);
            $objTpl->assign('categoryiesOfContent', $categoryiesOfContent);
            $objTpl->assign('is_form', $is_form);
            echo $objTpl->fetch('s_profile_account.html');
            exit();
            break;
        case 'edit_profile_account':
            $data = $_POST;
            $data['ID'] = $USERINFO['ID'];
            $objAccount = new Account;
            $res = $objAccount->edit_publish_account($data);
            echo $res;
            break;
        case 'show_profile_site':
            $site_id = isset($_POST['site_id']) ? $_POST['site_id'] : 0;
            $type = isset($_POST['type']) ? $_POST['type'] : 'edit';
            $countryOption = getDictionary('country');
            $sitetypeOption = getDictionary('sitetype');

            if ($site_id > 0) {
                $objAccount = new Account;
                $uid = $USERINFO['ID'];
                $site_info = $objAccount->table('publisher_account')->where('ID = ' . intval($site_id) . ' AND PublisherId = ' . intval($uid))->findone();

                $objTpl->assign('site_info', $site_info);
            }

            $objTpl->assign('sitetypeOption', $sitetypeOption);
            $objTpl->assign('countryOption', $countryOption);
            $objTpl->assign('site_id', $site_id);
            $objTpl->assign('type', $type);

            echo $objTpl->fetch('s_profile_site.html');
            exit();
            break;
        case 'edit_profile_site':
            $objAccount = new Account;
            $data = $_POST;
            if (isset($data['ID']) && $data['ID'] > 0) {
                $act = 'edit';
            } else {
                $act = 'add';
            }
            $data['PublisherId'] = $USERINFO['ID'];
            $ID = $objAccount->edit_profile_site($data);
            $return_d = array();
            if ($ID > 0) {
                if ($act == 'add')
                    $return_d['res'] = 1;
                else
                    $return_d['res'] = 2;
                $return_d['data'] = $objAccount->table('publisher_account')->where('ID = ' . intval($ID))->field('ID,Domain,Alias,ApiKey,SiteType,TargetCountry')->findone();
                $countryOption = getDictionary('country');
                $sitetypeOption = getDictionary('sitetype');

                $return_d['data']['SiteType'] = $sitetypeOption[$return_d['data']['SiteType']];
                $return_d['data']['TargetCountry'] = $countryOption[$return_d['data']['TargetCountry']];
            } else {
                $return_d['res'] = 3;
            }
            echo json_encode($return_d);
            exit();
            break;
        case 'tip_publisher':
            $str = '';
            if (isset($_POST['keywords'])) {
                $_POST['keywords'] = trim($_POST['keywords']);
                if (!empty($_POST['keywords'])) {
                    $objAccount = new Account;
                    $rows = $objAccount->table('publisher')->where('UserName LIKE "' . addslashes($_POST['keywords']) . '%"')->field('ID,UserName')->find();

                    $tmp = array();
                    foreach ($rows as $v) {
                        $tmp[] = $v['UserName'] . ' (' . $v['ID'] . ')';
                    }
                    $str = join('|', $tmp);

                }
            }

            echo $str;
            exit();
            break;
        //tip_wf
        case 'tip_wf':

            if (isset($_POST['keywords'])) {
                $_POST['keywords'] = trim($_POST['keywords']);
                if (!empty($_POST['keywords'])) {
                    $objAccount = new Account;

                    $rows = $objAccount->table('wf_aff')->where('Name LIKE "%' . addslashes($_POST['keywords']) . '%"')->field('ID,Name')->find();

                    $tmp = array();

                    foreach ($rows as $v) {
                        $tmp[] = $v['Name'];


                    }

                    $str = join('|', $tmp);
                }
            }

            echo $str;
            exit();
            break;

        //tip_PDC_Add
        case 'tip_PDC_Add':

            if (isset($_POST['domain']) && isset($_POST['status'])) {
                $str = "";
                $_POST['domain'] = trim($_POST['domain']);


                $sql = 'SELECT COUNT(*) AS count
							FROM r_domain_program_ctrl a inner join domain b on a.DomainId = b.ID  WHERE  b.Domain  ="' . $_POST['domain'] . '" AND `Status` = "Active"';

                $count = mysql_query($sql);
                $result = mysql_fetch_array($count, MYSQL_ASSOC);


                if ($result['count'] == 0 || $_POST['status'] == "Inactive") {

                    if (!empty($_POST['domain']) && strlen($_POST['domain']) >= 3) {
                        $objAccount = new Account;

                        $rows = $objAccount->table('domain')->where('Domain LIKE "' . addslashes($_POST['domain']) . '%"')->field('ID,Domain')->limit(100)->find();
                        if ($rows) {
                            $tmp = array();

                            foreach ($rows as $v) {
                                $tmp[] = $v['Domain'];


                            }

                            $str = join('|', $tmp);
                        } else {
                            $str = "there is no such Domain in table 'domain'";
                        }
                    }
                } else {

                    $str = "Cannot add for this Domain already exists and its status is active";

                }

            }

            echo $str;
            exit();
            break;
        //tip_PDC_edit
        case 'tip_PDC_edit':

            if (isset($_POST['domain']) && isset($_POST['status']) && isset($_POST['id'])) {
                $str = " ";
                $_POST['domain'] = trim($_POST['domain']);


                $sql = 'SELECT COUNT(*) AS count
							FROM r_domain_program_ctrl a inner join domain b on a.DomainId = b.ID  WHERE  b.Domain  ="' . $_POST['domain'] . '" AND `Status` = "Active" AND a.ID <> "' . $_POST['id'] . '"';

                $count = mysql_query($sql);
                $result = mysql_fetch_array($count, MYSQL_ASSOC);


                if ($result['count'] == 0 || $_POST['status'] == "Inactive") {

                    if (!empty($_POST['domain']) && strlen($_POST['domain']) >= 3) {
                        $objAccount = new Account;

                        $rows = $objAccount->table('domain')->where('Domain LIKE "' . addslashes($_POST['domain']) . '%"')->field('ID,Domain')->limit(100)->find();
                        if ($rows) {
                            $tmp = array();

                            foreach ($rows as $v) {
                                $tmp[] = $v['Domain'];


                            }

                            $str = join('|', $tmp);
                        } else {
                            $str = "there is no such Domain in table 'domain'";
                        }
                    }
                } else {

                    $str = "Cannot edit for this Domain already exists and its status is active";

                }

            }

            echo $str;
            exit();
            break;
        //tip_PDC_Domain
        case 'tip_PDC_Domain':
	    global $db_bdg01;
	    $db_bdg01 = new Mysql('bdg_go_base', 'bdg01.i.mgsvr.com', 'bdg_slave', 'SHDbdsg32B');

            if (isset($_POST['keywords'])) {
                $str = "";
                $_POST['keywords'] = trim($_POST['keywords']);
                if (!empty($_POST['keywords']) && strlen($_POST['keywords']) >= 3) {
                    //$objAccount = new Account;

                    //$rows = $objAccount->table('domain')->where('Domain NOT LIKE "%/%" AND Domain LIKE "%' . addslashes($_POST['keywords']) . '%"')->field('ID,Domain')->limit(100)->find();
		    $sql = 'SELECT DISTINCT(o.Domain) as Domain FROM domain AS o LEFT JOIN domain_outgoing_default_other  AS a ON o.ID = a.DID LEFT JOIN program_intell as b ON a.PID = b.`ProgramId` WHERE  o.Domain LIKE  "%' . addslashes($_POST['keywords']) . '%" AND a.Site IS NOT NULL AND b.`AffId` NOT IN (191,223,578)';
		    $rows = $db_bdg01->getRows($sql);
                    if ($rows) {
                        $tmp = array();

                        foreach ($rows as $v) {
                            $tmp[] = $v['Domain'];


                        }

                        $str = join('|', $tmp);
                    } else {
                        $str = "there is no such Doamin in table 'domain'";
                    }
                }
            }

            echo $str;
            exit();
            break;

        //tip_PDC_Program
        case 'tip_PDC_Program':

            if (isset($_POST['keywords'])) {
                $str = "";
                $_POST['keywords'] = trim($_POST['keywords']);


// 						$sql = 'SELECT COUNT(*) AS count
// 							FROM domain WHERE Domain ="'.$_POST['keywords'].'"';

// 						$count = mysql_query($sql);
// 						$result = mysql_fetch_array($count,MYSQL_ASSOC);
//					if($result['count']==0){
                if (!empty($_POST['keywords']) && strlen($_POST['keywords']) >= 3) {
                    $objAccount = new Account;

                    $rows = $objAccount->table('program')->where('Name LIKE "%' . addslashes($_POST['keywords']) . '%"')->field('ID,Name')->limit(100)->find();
                    if ($rows) {
                        $tmp = array();

                        foreach ($rows as $v) {
                            $tmp[] = $v['Name'];


                        }

                        $str = join('|', $tmp);
                    } else {
                        $str = "there is no such Program in table 'program'";
                    }
                }
// 							else{
// 								$str ="Cannot add for this Domain already exists";
// 								}	
                //					}


            }

            echo $str;
            exit();
            break;
        //tip_Domain_add
        case 'tip_Domain_add':

            if (isset($_POST['keywords'])) {
                $str = "";
                $_POST['keywords'] = trim($_POST['keywords']);


                $sql = 'SELECT COUNT(*) AS count
							FROM domain WHERE Domain ="' . $_POST['keywords'] . '"';

                $count = mysql_query($sql);
                $result = mysql_fetch_array($count, MYSQL_ASSOC);


                if ($result['count'] == 0) {

                    if (!empty($_POST['keywords']) && strlen($_POST['keywords']) >= 3) {
                        $objAccount = new Account;

                        $rows = $objAccount->table('domain')->where('Domain LIKE "' . addslashes($_POST['keywords']) . '%"')->field('ID,Domain')->limit(100)->find();
                        if ($rows) {
                            $tmp = array();

                            foreach ($rows as $v) {
                                $tmp[] = $v['Domain'];


                            }

                            $str = join('|', $tmp);
                        } else {
                            $str = "there is no such Doamin in table 'domain'";
                        }
                    }
                } else {
                    $str = "Cannot add for this Domain already exists";
                }

            }

            echo $str;
            exit();
            break;
        case 'tip_site':
            $str = '';
            if (isset($_POST['keywords'])) {
                $_POST['keywords'] = trim($_POST['keywords']);
                if (!empty($_POST['keywords'])) {
                    $objAccount = new Account;
                    $rows = $objAccount->table('publisher_account')->where('Alias LIKE "' . addslashes($_POST['keywords']) . '%"')->field('ID,Alias')->find();

                    $tmp = array();
                    foreach ($rows as $v) {
                        $tmp[] = $v['Alias'] . ' (' . $v['ID'] . ')';
                    }
                    $str = join('|', $tmp);

                }
            }

            echo $str;
            exit();
            break;

        case 'tip_affiliate':

            if (isset($_POST['keywords'])) {
                $str = "";
                $_POST['keywords'] = trim($_POST['keywords']);
                if (!empty($_POST['keywords'])) {
                    $objAccount = new Account;

                    $rows = $objAccount->table('wf_aff')->where('(`Name` LIKE "%' . addslashes($_POST['keywords']) . '%" OR `ShortName` LIKE "' . addslashes($_POST['keywords']) . '%" OR `ID` LIKE "' . addslashes($_POST['keywords']) . '%") AND IsActive = "YES"')->field('ID,Name')->limit(100)->find();//全名搜索
                    if ($rows) {
                        $tmp = array();

                        foreach ($rows as $v) {
                            $tmp[] = $v['Name'];


                        }


                        $str = join('|', $tmp);
                    } else {
                        $str = "there is no such affiliate in table 'wf_aff'";
                    }
                }
            }

            echo $str;
            exit();
            break;
        //BatchOperator in table_change_log_batch
        case 'tip_batchoperator':
            if (isset($_POST['keywords'])) {
                $str = "";
                $_POST['keywords'] = trim($_POST['keywords']);
                if (!empty($_POST['keywords'])) {
                    $objAccount = new Account;
                    $rows = $objAccount->table('table_change_log_batch')->where('(`BatchOperator` LIKE "%' . addslashes($_POST['keywords']) . '%" OR `BatchId` LIKE "' . addslashes($_POST['keywords']) . '%")')->field('distinct BatchOperator')->limit(100)->find();//全名搜索
                    if ($rows) {
                        $tmp = array();
                        foreach ($rows as $v) {
                            $tmp[] = $v['BatchOperator'];
                        }
                        $str = join('|', $tmp);
                    } else {
                        $str = "there is no such operator in table 'table_change_log_batch'";
                    }
                }
            }
            echo $str;
            exit();
            break;
        //tip_PDL
        case 'tip_PDL':

            if (isset($_POST['keywords'])) {
                $str = "";
                $_POST['keywords'] = trim($_POST['keywords']);


                if (!empty($_POST['keywords']) && strlen($_POST['keywords']) >= 3) {
//					$index = 'create index idx_u on program (ID,Name)';
//					$index = 'drop index idx_u on program';
//					mysql_query($index);
                    $sql_add = 'SELECT i.AffId , p.ID ,p.Name From program AS p INNER JOIN program_intell AS i ON i.ProgramId = p.ID INNER JOIN wf_aff AS w ON w.ID = i.AffId WHERE p.Name LIKE "' . addslashes($_POST['keywords']) . '%" AND w.Name = "' . addslashes($_POST['aff']) . '" LIMIT 20';
                    $objAdd = new LibFactory();
                    $rows = $objAdd->getRows($sql_add);

                    if ($rows) {

                        $tmp = array();

                        foreach ($rows as $v) {
                            $tmp[] = $v['Name'] . ' [PID:' . $v['ID'] . '] AffId:[' . $v['AffId'] . ']';


                        }

                        $str = join('|', $tmp);

                    } else {
                        $str = "there is no such Program in table 'program'";
                    }
                }

            }

            echo $str;
            exit();
            break;
        case 'tip_PDL_sub':

            if (isset($_POST['keywords'])) {
                $str = "";
                $_POST['keywords'] = trim($_POST['keywords']);


                $sql = 'SELECT ID FROM program WHERE Name = "' . $_POST['keywords'] . '"';
                $pdlobj = new LibFactory();
                $rows = $pdlobj->getRows($sql);
                if (empty($rows)) {
                    $str = 'there is no such program in table program';

                } else {
                    //检查在表intell中是否存在这样的program-affiliate�?
                    $sql_intell = 'SELECT p.ID FROM program AS p INNER JOIN program_intell AS i ON p.ID = i.ProgramId WHERE p.Name = "' . $_POST['keywords'] . '" AND i.AffId = "' . $_POST['affId'] . '"';
                    $rows_intell = $pdlobj->getRows($sql_intell);
                    if (empty($rows_intell)) {
                        $str = 'there is no such program-Affiliate combination in table program_intell';
                    } else {

                        $sql_ctrl = 'SELECT ProgramId FROM r_domain_program_ctrl WHERE ProgramId = "' . $rows_intell[0]['ID'] . '" AND DomainId = "' . $_POST['DomainId'] . '"';
                        $rows_ctrl = $pdlobj->getRows($sql_ctrl);
                        if (!empty($rows_ctrl)) {
                            $str = 'this program-domain combination has already existed';
                        }
                    }
                }

            }

            echo $str;
            exit();
            break;
        //tip_PDL_aff
        case 'tip_PDL_aff':

            if (isset($_POST['keywords'])) {
                $str = "";
                $_POST['keywords'] = trim($_POST['keywords']);


                if (!empty($_POST['keywords']) && strlen($_POST['keywords']) >= 2) {
                    $objAccount = new Account;

                    $rows = $objAccount->table('wf_aff')->where('Name LIKE "' . addslashes($_POST['keywords']) . '%"')->field('ID,Name')->limit(20)->find();
                    if (!empty($rows)) {

                        foreach ($rows as $v) {
                            $tmp[] = $v['Name'] . ' [Id:' . $v['ID'] . ']';
                        }

                        $str = join('|', $tmp);

                    } else {
                        $str = "there is no such Affiliate in table 'wf_aff'";
                    }
                }
            }
            echo $str;
            exit();
            break;

        case 'tip_domain':

            if (isset($_POST['keywords'])) {
                $str = "";
                $_POST['keywords'] = trim($_POST['keywords']);
                if (!empty($_POST['keywords']) && strlen($_POST['keywords']) >= 3) {
                    $objAccount = new Account;

                    $rows = $objAccount->table('domain')->where('Domain LIKE "%' . addslashes($_POST['keywords']) . '%" OR ID LIKE "' . addslashes($_POST['keywords']) . '%"')->field('ID,Domain')->limit(100)->find();
                    if ($rows) {
                        $tmp = array();

                        foreach ($rows as $v) {
                            $tmp[] = $v['Domain'];


                        }

                        $str = join('|', $tmp);
                    } else {
                        $str = "there is no such Doamin in table 'domain'";
                    }
                }
            }

            echo $str;
            exit();
            break;

        //tip_Program
        case 'tip_program':

            if (isset($_POST['keywords'])) {
                $str = "";
                $_POST['keywords'] = trim($_POST['keywords']);
                if (!empty($_POST['keywords']) && strlen($_POST['keywords']) >= 3) {
                    $objAccount = new Account;

                    $row_aff = array();
                    if (isset($_POST['affname'])) {
                        $row_aff = $objAccount->table('wf_aff')->where('`Name` = "' . addslashes(trim($_POST['affname'])) . '"')->field('ID')->findone();
                    }

                    $where_str = '(Name LIKE "%' . addslashes($_POST['keywords']) . '%" OR IdInAff LIKE "' . addslashes($_POST['keywords']) . '%")';//and优先级强于or，所有前面的or要加括号
                    if (!empty($row_aff)) {
                        $where_str .= ' AND AffId = ' . intval($row_aff['ID']);
                    }
                    $rows = $objAccount->table('program')->where($where_str)->field('ID,Name')->limit(100)->find();
                    if ($rows) {
                        $tmp = array();

                        foreach ($rows as $v) {
                            $tmp[] = $v['Name'];


                        }

                        $str = join('|', $tmp);
                    } else {
                        $str = "there is no such Program in table 'program'";
                    }

                }
            }

            echo $str;
            exit();
            break;
        case 'tip_history_domain_detail':

            if (isset($_POST['date'])) {


                $sql = 'SELECT Alias,SUM(Commission) as Commission,SUM(Sales) as Sales,count(*) as num
					FROM rpt_transaction_unique WHERE CreatedDate = "' . $_POST['date'] . '" AND DomainUsed = ":DOMAIN:' . $_POST['domain'] . '" GROUP BY Alias ORDER BY Alias';
                $objDetail = new LibFactory();
                $rows = $objDetail->getRows($sql);    //$rows是一个二维数组，用json返回

            }

            echo json_encode($rows);
            exit();
            break;


        case 'tip_history_domain_publisher':


            $lib = new LibFactory();
            $where_his_arr = array();
            $where_now_arr = array();
            $where_his_arr_click = array();
            $where_now_arr_click = array();

            if (!empty($_POST['his_from'])) {
                $where_his_arr[] = 'Createddate >= "' . addslashes($_POST['his_from']) . '"';
                $where_his_arr_click[] = 'createddate >= "' . addslashes($_POST['his_from']) . '"';
            }

            if (!empty($_POST['his_to'])) {
                $where_his_arr[] = 'Createddate <= "' . addslashes($_POST['his_to']) . '"';
                $where_his_arr_click[] = 'createddate <= "' . addslashes($_POST['his_to']) . '"';
            }

            if (!empty($_POST['now_from'])) {
                $where_now_arr[] = 'Createddate >= "' . addslashes($_POST['now_from']) . '"';
                $where_now_arr_click[] = 'createddate >= "' . addslashes($_POST['now_from']) . '"';
            }

            if (!empty($_POST['now_to'])) {
                $where_now_arr[] = 'Createddate <= "' . addslashes($_POST['now_to']) . '"';
                $where_now_arr_click[] = 'createddate <= "' . addslashes($_POST['now_to']) . '"';
            }
            $where_his_str = join(' AND ', $where_his_arr);
            $where_his_str = $where_his_str ? ' WHERE ' . $where_his_str : '';
            $where_now_str = join(' AND ', $where_now_arr);
            $where_now_str = $where_now_str ? ' WHERE ' . $where_now_str : '';


            $where_his_str_click = join(' AND ', $where_his_arr_click);
            $where_his_str_click = $where_his_str_click ? ' WHERE ' . $where_his_str_click : '';
            $where_now_str_click = join(' AND ', $where_now_arr_click);
            $where_now_str_click = $where_now_str_click ? ' WHERE ' . $where_now_str_click : '';


            $sql_his = 'SELECT Alias,count(*) as ordernum,sum(Sales) as ordersales,sum(Commission) as commission FROM rpt_transaction_unique ' . $where_his_str . ' AND domainUsed = ":DOMAIN:' . $_POST['domain'] . '" GROUP BY Alias';
            $row_his = $lib->getRows($sql_his);

            $sql_now = 'SELECT Alias,count(*) as ordernum,sum(Sales) as ordersales,sum(Commission) as commission FROM rpt_transaction_unique ' . $where_now_str . ' AND domainUsed = ":DOMAIN:' . $_POST['domain'] . '" GROUP BY Alias';
            $row_now = $lib->getRows($sql_now);
            // 		echo $sql_his;
            // 		echo "<br />";
            // 		echo $sql_now;
            // 		echo "<br />";
            $affs = array();
            $data_his = array();
            $data_now = array();
            foreach ($row_his as $k => $v) {
                $affs[] = $v['Alias'];
                $data_his[$v['Alias']] = $v;
            }

            foreach ($row_now as $k => $v) {
                if (!in_array($v['Alias'], $affs)) { //in_array在数组中搜索固定值，存在，返回true
                    $affs[] = $v['Alias'];        //存放所有row_his和row_now中的IdInAff
                }
                $data_now[$v['Alias']] = $v;
            }
            sort($affs);


            $aff_data = array();
            foreach ($affs as $k => $v) {
                $tmp = array(
                    'name' => $v,
                    'his_ordernum' => isset($data_his[$v]['ordernum']) ? $data_his[$v]['ordernum'] : 0,
                    'his_ordersales' => isset($data_his[$v]['ordersales']) ? $data_his[$v]['ordersales'] : 0,
                    'his_commission' => isset($data_his[$v]['commission']) ? $data_his[$v]['commission'] : 0,
                    'now_ordernum' => isset($data_now[$v]['ordernum']) ? $data_now[$v]['ordernum'] : 0,
                    'now_ordersales' => isset($data_now[$v]['ordersales']) ? $data_now[$v]['ordersales'] : 0,
                    'now_commission' => isset($data_now[$v]['commission']) ? $data_now[$v]['commission'] : 0,
                );
                $tmp['diff_ordernum'] = $tmp['now_ordernum'] - $tmp['his_ordernum'];
                $tmp['diff_ordersales'] = $tmp['now_ordersales'] - $tmp['his_ordersales'];
                $tmp['diff_commission'] = $tmp['now_commission'] - $tmp['his_commission'];


                $tmp['per_ordernum'] = $tmp['his_ordernum'] > 0 ? number_format($tmp['now_ordernum'] / $tmp['his_ordernum'] * 100, 2, '.', '') : 0;
                $tmp['per_ordersales'] = $tmp['his_ordersales'] > 0 ? number_format($tmp['now_ordersales'] / $tmp['his_ordersales'] * 100, 2, '.', '') : 0;
                $tmp['per_commission'] = $tmp['his_commission'] > 0 ? number_format($tmp['now_commission'] / $tmp['his_commission'] * 100, 2, '.', '') : 0;

                $tmp['per_ordernum'] = $tmp['per_ordernum'] - 100;
                $tmp['per_ordersales'] = $tmp['per_ordersales'] - 100;
                $tmp['per_commission'] = $tmp['per_commission'] - 100;


                if ($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] == 0) {
                    $tmp['per_ordernum'] = 0;
                } elseif ($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] > 0) {
                    $tmp['per_ordernum'] = 100;
                } elseif ($tmp['his_ordernum'] == 0 && $tmp['now_ordernum'] < 0) {
                    $tmp['per_ordernum'] = -100;
                }

                if ($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] == 0) {
                    $tmp['per_ordersales'] = 0;
                } elseif ($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] > 0) {
                    $tmp['per_ordersales'] = 100;
                } elseif ($tmp['his_ordersales'] == 0 && $tmp['now_ordersales'] < 0) {
                    $tmp['per_ordersales'] = -100;
                }

                if ($tmp['his_commission'] == 0 && $tmp['now_commission'] == 0) {
                    $tmp['per_commission'] = 0;
                } elseif ($tmp['his_commission'] == 0 && $tmp['now_commission'] > 0) {
                    $tmp['per_commission'] = 100;
                } elseif ($tmp['his_commission'] == 0 && $tmp['now_commission'] < 0) {
                    $tmp['per_commission'] = -100;
                }


                //-----------------------------------------------------CR的计算---------------------------------------------------------------------------

                $tmp['his_cr'] = $tmp['his_ordersales'] > 0 ? number_format($tmp['his_commission'] / $tmp['his_ordersales'] * 100, 2, '.', '') : 0;
                $tmp['now_cr'] = $tmp['now_ordersales'] > 0 ? number_format($tmp['now_commission'] / $tmp['now_ordersales'] * 100, 2, '.', '') : 0;


                if ($tmp['his_commission'] == 0 && $tmp['his_ordersales'] == 0) {
                    $tmp['his_cr'] = 0;
                }
                if ($tmp['now_commission'] == 0 && $tmp['now_ordersales'] == 0) {
                    $tmp['now_cr'] = 0;
                }


                $tmp['diff_cr'] = $tmp['now_cr'] - $tmp['his_cr'];//可能为负数
                $tmp['per_cr'] = $tmp['his_cr'] > 0 ? number_format($tmp['now_cr'] / $tmp['his_cr'] * 100, 2, '.', '') : 0;//只有分母大于零，才能用这个公式，分母等于零的情况，在下面
                $tmp['per_cr'] = $tmp['per_cr'] - 100;

                if ($tmp['his_cr'] == 0 && $tmp['now_cr'] == 0) {
                    $tmp['per_cr'] = 0;
                } elseif ($tmp['his_cr'] == 0 && $tmp['now_cr'] > 0) {
                    $tmp['per_cr'] = 100;
                }


                $aff_data[$v] = $tmp;
            }


            //---------------------------------------------------------click计算---------------------------------------------------------------------------
            $sql_his_click = 'SELECT COUNT(*) AS click,Alias FROM bd_out_tracking ' . $where_his_str_click . ' AND domainUsed = ":DOMAIN:' . $_POST['domain'] . '" GROUP BY Alias';
            $row_his_click = $lib->getRows($sql_his_click);

            $sql_now_click = 'SELECT COUNT(*) AS click,Alias FROM bd_out_tracking ' . $where_now_str_click . ' AND domainUsed = ":DOMAIN:' . $_POST['domain'] . '" GROUP BY Alias';
            $row_now_click = $lib->getRows($sql_now_click);


            $affs_click = array();
            $data_his_click = array();
            $data_now_click = array();
            foreach ($row_his_click as $k => $v) {

                $data_his_click[$v['Alias']] = $v['click'];
            }

            foreach ($row_now_click as $k => $v) {

                $data_now_click[$v['Alias']] = $v['click'];
            }


            foreach ($aff_data as &$v) {
                $v['his_click'] = isset($data_his_click[$v['name']]) ? $data_his_click[$v['name']] : 0;
                $v['now_click'] = isset($data_now_click[$v['name']]) ? $data_now_click[$v['name']] : 0;
                $v['diff_click'] = $v['now_click'] - $v['his_click'];
                $v['per_click'] = $v['his_click'] > 0 ? number_format($v['diff_click'] / $v['his_click'] * 100, 2, '.', '') : 100;
            }


            echo json_encode($aff_data);
            exit();
            break;

        case 'check_jump':
            if (isset($_POST['id']) && isset($_POST['res'])) {
                $objOutlog = new Outlog;
                $row = $objOutlog->table('chk_url_jump_res')->where('id = ' . intval($_POST['id']))->findone();
                if ($row) {
                    $sql = 'UPDATE chk_url_jump_res SET checkres = "' . $_POST['res'] . '" WHERE id = ' . intval($_POST['id']);
                    $objOutlog->query($sql);
                }
            }
            break;

        case 'check_jump_mer':
            if (isset($_POST['id']) && isset($_POST['res'])) {
                $objOutlog = new Outlog;
                $row = $objOutlog->table('chk_url_jump_mer')->where('id = ' . intval($_POST['id']))->findone();
                if ($row) {
                    $sql = 'UPDATE chk_url_jump_mer SET checkres = "' . $_POST['res'] . '" WHERE id = ' . intval($_POST['id']);
                    $objOutlog->query($sql);
                }
            }
            break;

        case 'remark_jump_mer':
            if (isset($_POST['id'])) {
                $objOutlog = new Outlog;
                $row = $objOutlog->table('chk_url_jump_mer')->where('id = ' . intval($_POST['id']))->findone();

                $op_name = $_SERVER['PHP_AUTH_USER'] ? $_SERVER['PHP_AUTH_USER'] : 'system';
                $op_time = date('Y-m-d H:i:s');

                if ($row) {
                    $more_set = '';
                    if (isset($_POST['check_res']) && in_array($_POST['check_res'], array('yes', 'no', 'oe', 're'))) {
                        $more_set .= ',checkres = "' . $_POST['check_res'] . '"';
                    }
                    $sql = 'UPDATE chk_url_jump_mer SET remark = "' . addslashes($_POST['remark']) . '",op_name = "' . addslashes($op_name) . '",op_time = "' . $op_time . '" ' . $more_set . ' WHERE id = ' . intval($_POST['id']);

                    $objOutlog->query($sql);
                }
            }
            break;
        case 'get_file_line':
            $file_path = $_POST['file'];
            $line = 0;
            $fp = fopen($file_path, 'r');

            if ($fp) {
                while (!feof($fp)) {
                    fgets($fp);
                    $line++;
                }
                fclose($fp);
            }
            echo $line;
            exit();
            break;
        case 'send_contact_message':
            $objAccount = new Account();
            echo $objAccount->send_contact_message();

            exit();
            break;
        case 'retrieve_password':
            $objAccount = new Account();
            echo $objAccount->retrieve_password();
            exit();
            break;
        case 'send_partnership_message':
            $objAccount = new Account();
            echo $objAccount->send_partnership_message();
            exit();
            break;
        case 'publish_forgotPwd':
            $objAccount = new Account();
            echo $objAccount->check_email();
            exit();
            break;
        case 'upload_transaction_file':

            break;
        case 'show_domain_list':
            $storeData = json_decode($_POST['storeData'], true);
            $html = '';

            $objAccount = new Account;
            $storeId = $_POST['storeId'];


            $sites_rows = $objAccount->table('publisher_account')->where('PublisherId = ' . intval($storeData['uid']))->find();
            $sites = array();
            foreach ($sites_rows as $v) {
                $sites[] = addslashes($v['ApiKey']);
            }
            $where_str = '';
            $where_arr = array();
            $where_arr[] = 'a.createddate >= "' . $storeData['tran_from'] . '"';
            $where_arr[] = 'a.createddate <= "' . $storeData['tran_to'] . '"';
            $where_arr[] = 'a.site IN ("' . join('","', $sites) . '")';
//            $where_arr[] = 'b.DomainAffSupport = "YES"';
            $where_arr[] = 'a.domainId > 0';
            $where_arr[] = 'a.storeId = "' . $storeId . '"';
            if (!empty($where_arr)) {
                $where_str = ' WHERE ' . join(' AND ', $where_arr);
            }
            $sql = 'SELECT a.domainId,c.Domain,SUM(a.showrevenues) as Commission,SUM(a.sales) as Sales,SUM(a.orders) as num,SUM(a.clicks) as clicks
					FROM statis_domain a LEFT JOIN domain c ON a.domainId = c.ID ' . $where_str . ' GROUP BY a.domainId HAVING Commission > 0 ORDER BY Commission';
            $domain_arr = $objAccount->getRows($sql);
            foreach ($domain_arr as $v) {
                $html = $html . '<tr class="domain" style="background-color:#b7b8cd"><td>' . $v["Domain"] . '</td><td>$' . round($v["Commission"], 2) . '</td><td>' . $v["num"] . '</td><td></td></tr>';
            }
            echo $html;
            exit();
            break;
        case 'show_advertiser_domain_list':
            $html = '';
            $objAccount = new Account();
            $sql = 'SELECT a.DID,a.PID,a.Key,a.Site,b.StoreId,c.CountryName FROM domain_outgoing_default_other a LEFT JOIN r_store_domain b ON a.DID = b.DomainId LEFT JOIN country_codes c ON a.Site = c.CountryCode WHERE b.StoreId = ' . $_POST['storeId'] . ' ORDER BY c.CountryName';
            $domain_arr = $objAccount->getRows($sql);

            foreach ($domain_arr as $v) {
                if ($v['CountryName'])
                    $classifyArr[$v['Key']][] = $v['CountryName'];
                if ($v['Site'] == 'global')
                    $classifyArr[$v['Key']][] = 'Global';
                if ($v['Site'] && $v['Site'] != 'global' && $v['Site'] != 'uk' && !$v['CountryName'])
                    $classifyArr[$v['Key']][] = $v['Site'];
                if($v['Site'] == 'uk')
                    $classifyArr[$v['Key']][] = 'United Kingdom';
            }

            foreach ($classifyArr as $k => $v) {
                /*foreach ($v as $sub_v) {
                    $select = $select.$sub_v.',';
                }*/
                $siteStr = implode(',', $v);
                $select = '<td style="width: 40%">' . $siteStr . '</td>';
                $html = $html . '<tr style="background-color:#b7b8cd"><td style="width: 30%">' . $k . '</td><td style="width: 30%">CPS</td>' . $select . '</tr>';
            }

            echo $html;
            exit();
            break;
        case 'signPage1':
            $objAccount = new Account();
            $res = $objAccount->sign1($_POST);
            echo $res;
            exit();
            break;
        case 'signPage2':
            $objAccount = new Account();
            $res = $objAccount->sign2($_POST);
            echo $res;
            exit();
            break;
        case 'signPage3':
            $objAccount = new Account();
            $res = $objAccount->sign3($_POST);
            echo $res;
            exit();
            break;

        case 'language':
            $data['suc'] = false;
            if(isset($_POST['country']) && $_POST['country']){
                if(isset($LANG[$_POST['country']])){
                    if(isset($_COOKIE['LANG'])){
                        setcookie('LANG',$_POST['country']);
                    }else{
                        setcookie('LANG',$_POST['country'],3600*24*30);
                        $_SESSION['LANG'] = $_POST['country'];
                    }
                    $data['suc'] = true;
                }
            }
            echo json_encode($data);
            break;

        default:
            # code...
            break;
    }
}elseif(isset($_GET['act'])){
    switch ($_GET['act']){
        case 'trafficExport':
            $listObj = new Account();
            $listFunction = 'getTrafficRpt';
            $jsonstr = $_GET['jsonstr'];
            $obj = new Tools();
            $obj->get_csv_export($listObj,$listFunction,$jsonstr);
            exit();
            break;
    }
} else {
    echo 0;
    exit();
}
?>
