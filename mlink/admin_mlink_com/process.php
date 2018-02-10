<?php
include_once('conf_ini.php');
include_once(INCLUDE_ROOT . 'init.php');
global $db;

if (isset($_POST['act'])) {

    switch ($_POST['act']) {
        case 'UpdateCategory':
            $id = $_POST['id'];
            $cate = rtrim($_POST['cate'],',');
            $sql = "update store SET CategoryId = '{$cate}',CategoryHumanCtrl='YES' WHERE `ID` = '{$id}'";
            $res = $db->query($sql);
            if($res == 1){
                echo 1;
            }else{
                echo 2;
            }

            exit();
        case 'save_ignorelist':

            $ApiKey = $_POST['apikey']?$_POST['apikey']:'';
            $ignorelist = $_POST['ignorelist']?$_POST['ignorelist']:'';
            $ignorelist = trim($ignorelist);
            $ignorelist = trim($ignorelist,',');
            $ignorelist = str_replace(',',"\n",$ignorelist);

            $sql = 'update publisher_account SET JsIgnoreDomain = "'.$ignorelist.'" WHERE ApiKey = "'.addslashes($ApiKey).'"';
            $db->query($sql);
            exit();
        case 'save_whitelist':

            $ApiKey = $_POST['apikey']?$_POST['apikey']:'';
            $ignorelist = $_POST['ignorelist']?$_POST['ignorelist']:'';
            $ignorelist = trim($ignorelist);
            $ignorelist = trim($ignorelist,',');
            $ignorelist = str_replace(',',"\n",$ignorelist);

            $sql = 'update publisher_account SET JsWhiteDomain = "'.$ignorelist.'" WHERE ApiKey = "'.addslashes($ApiKey).'"';
            $db->query($sql);
            exit();
        case 'outLogExport':
            $listObj = new Outlog();
            $listFunction = 'get_out_going_log_data';
            $jsonstr = $_POST;
            $obj = new Tools();
            $obj->get_csv_export($listObj,$listFunction,$jsonstr);
            exit();
            break;
        case 'change_jswork':
            $ApiKey = $_POST['apikey']?$_POST['apikey']:$_POST['apikey'];
            $jswork = ($_POST['status'] == 'yes')?'yes':'no';
            $sql = 'update publisher_account SET JsWork = "'.$jswork.'" WHERE ApiKey = "'.addslashes($ApiKey).'"';
            $db->query($sql);
        case 'load_ignorelist':
            $ApiKey = $_POST['apikey']?$_POST['apikey']:$_POST['apikey'];
            $sql = 'SELECT * FROM publisher_account WHERE ApiKey = "'.addslashes($ApiKey).'"';
            $rows = $db->getRows($sql);
            $ignoreList = !empty($rows)?$rows[0]['JsIgnoreDomain']:'';
            echo trim($ignoreList);exit();
        case 'load_whitelist':
            $ApiKey = $_POST['apikey']?$_POST['apikey']:$_POST['apikey'];
            $sql = 'SELECT * FROM publisher_account WHERE ApiKey = "'.addslashes($ApiKey).'"';
            $rows = $db->getRows($sql);
            $whiteList = !empty($rows)?$rows[0]['JsWhiteDomain']:'';
            echo trim($whiteList);exit();
        case 'check_publish_account':
            if (!isset($_POST['account']) || empty($_POST['account'])) {
                echo 1;
                exit();
            }
            if (!preg_match('/^[0-9a-zA-Z@_]+$/', $_POST['account'])) {
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
            $user_profile = $objAccount->get_account_info($USERINFO['ID']);
            $is_form = isset($_POST['is_form']) ? $_POST['is_form'] : 0;
            $countryOption = getDictionary('country');


            $objTpl->assign('user_profile', $user_profile);
            $objTpl->assign('countryOption', $countryOption);
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
        case 'show_publisher_site':
            $site_id = isset($_POST['site_id']) ? $_POST['site_id'] : 0;
            $publisher_id = isset($_POST['publisher_id']) ? $_POST['publisher_id'] : 0;
            $type = isset($_POST['type']) ? $_POST['type'] : 'edit';
            $countryOption = getDictionary('country');
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
                    'n' => 'Virtual currency',
                    'o' => 'File sharing platform',
                    'p' => 'Video sharing platform',
                    'q' => 'Other',
                ),
                '2.MOBILE APP'                 => array(
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
                    'n' => 'File sharing platform',
                    'o' => 'Video sharing platform',
                    'p' => 'Other',
                )
            );
            if ($site_id > 0) {
                $objAccount = new Account;
                $site_info = $objAccount->table('publisher_account')->where('ID = ' . intval($site_id))->findone();
                $objTpl->assign('site_info', $site_info);
            }
            $objTpl->assign('sitetypeOption', $categoryiesOfContent);
            $objTpl->assign('countryOption', $countryOption);
            $objTpl->assign('site_id', $site_id);
            $objTpl->assign('publisher_id', $publisher_id);
            $objTpl->assign('type', $type);
            echo $objTpl->fetch('s_publisher_site.html');
            exit();
            break;
        case 'edit_publisher_site':
            $objAccount = new Account;
            $data = $_POST;
            $rs = $objAccount->edit_publisher_site($data);
            echo json_encode($rs);
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
        case 'tip_advertiser':
            $str = '';
            if (isset($_POST['keywords'])) {
                $_POST['keywords'] = trim($_POST['keywords']);
                if (!empty($_POST['keywords'])) {
                    $objAccount = new Account;
                    $rows = $objAccount->table('store')->where('Name LIKE "' . addslashes($_POST['keywords']) . '%"')->field('Name')->find();

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
            if (isset($_POST['keywords'])) {
                $str = "";
                $_POST['keywords'] = trim($_POST['keywords']);
                if (!empty($_POST['keywords']) && strlen($_POST['keywords']) >= 3) {
                    $objAccount = new Account;

                    $rows = $objAccount->table('domain')->where('Domain NOT LIKE "%/%" AND Domain LIKE "%' . addslashes($_POST['keywords']) . '%"')->field('ID,Domain')->limit(100)->find();
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
                            $tmp[$v['Name']] = $v['ID'];


                        }

                        $str = json_encode($tmp);
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
                if ($v['Site'] == 'uk')
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
        case 'tip_site':
            $str = '';
            if (isset($_POST['keywords'])) {
                $_POST['keywords'] = trim($_POST['keywords']);
                if (!empty($_POST['keywords'])) {
                    $objAccount = new Account;
                    $rows = $objAccount->table('publisher_account')->where('Alias LIKE "' . addslashes($_POST['keywords']) . '%"')->field('ID,Alias')->find();

                    $tmp = array();
                    foreach ($rows as $v) {
                        $tmp[] = $v['Alias'];
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

        case 'change_career':
            if ($_POST['career_now'] != $_POST['career_choosed']) {
                $sql = 'UPDATE publisher SET `Career` = "' . $_POST["career_choosed"] . '" WHERE `ID` = ' . $_POST['pid'];
                $db->query($sql);
            }
            echo $sql;
            exit();
            break;

        case 'tip_publisher_potential':
            $str = '';
            if(!empty($_POST['keyname']) && !empty($_POST['keywords'])){
                $sql = 'SELECT distinct('.$_POST['keyname'].') FROM publisher_potential WHERE '.$_POST['keyname'].' like "%'.$_POST['keywords'].'%"';
                $rows = $db->getRows($sql);
                $tmp = array();
                foreach ($rows as $v) {
                    $tmp[] = $v[$_POST['keyname']];
                }
                $str = join('|', $tmp);
                echo $str;
                exit();
            }
            echo $str;
            exit();
            break;

        case 'tip_payments':
            $str = '';
            if(!empty($_POST['keyname']) && !empty($_POST['keywords'])){
                $sql = 'SELECT distinct('.$_POST['keyname'].') FROM payments WHERE '.$_POST['keyname'].' like "%'.$_POST['keywords'].'%"';
                $rows = $db->getRows($sql);
                $tmp = array();
                foreach ($rows as $v) {
                    $tmp[] = $v[$_POST['keyname']];
                }
                $str = join('|', $tmp);
                echo $str;
                exit();
            }
            echo $str;
            exit();
            break;

        case 'upload_transaction_file':

            break;

        case 'publisher_potential_mail':
            $data = array();
            $data['operator'] = isset($_SERVER['PHP_AUTH_USER'])?trim($_SERVER['PHP_AUTH_USER']):'';
            $data['ppid'] = trim($_POST['ppid']);
            $data['ppid'] = intval($data['ppid']);
            $data['type'] = trim($_POST['mailtype']);
            $time = date('Y-m-d H:i:s');
            $data['time'] = $time;

            $data_key = array_keys($data);
            $data_values = array_values($data);

            $sql = 'UPDATE publisher_potential SET `status` = "'.$data['type'].'",laststatustime = "'.$data['time'].'" WHERE id = '.intval($data['ppid']);
            $db->query($sql);

            $sql = 'INSERT INTO publisher_potential_contact  (`'.join('`,`',$data_key).'`) VALUE ("'.join('","',$data_values).'")';
            $db->query($sql);

            echo $time;exit();
            break;
		case 'update_category':
			if(!empty($_POST['id']) && !empty($_POST['cate'])) {
                $id = $_POST['id'];
                $cate = trim($_POST['cate'], ',');
                $sql = "update category_ext set `IdRelated`='$cate',`ManualCtrl`='YES' where `ID`='$id'";
                echo $db->query($sql);
            }
			else
			{
				echo '';
			}
			exit();
			break;
	    case 'update_support_type':
	    	$time = date('Y-m-d H:i:s');
	        $user = $_SERVER['PHP_AUTH_USER'];
	        $sql = "select SupportType from program_intell where ProgramId='{$_POST['program_id']}'";
	        $oldVal = $db->getFirstRowColumn($sql);
            $sql = "insert into program_manual_change_log (ProgramId, FieldName,FieldValueOld,FieldValueNew,ModifyUser,AddTime,LastUpdateTime) VALUES ('{$_POST['program_id']}','SupportType','{$oldVal}','{$_POST['supportType']}','{$user}','{$time}','{$time}')";
            $db->query($sql);
			$sql = "update program_intell set  SupportType='{$_POST['supportType']}' WHERE ProgramId='{$_POST['program_id']}'";
		    $db->query($sql);
		    $sql = "insert into program_manual (ProgramId, SupportType) VALUES ('{$_POST['program_id']}','{$_POST['supportType']}') on duplicate key update `SupportType` = '{$_POST['supportType']}'";
		    $db->query($sql);
		    $sql = "SELECT c.`SupportType` FROM store a INNER JOIN r_store_program b ON a.`ID` = b.`StoreId` INNER JOIN program_intell c ON c.`ProgramId` = b.`ProgramId` WHERE a.id = '{$_POST['store_id']}';";
		    $data = array_keys($db->getRows($sql,'SupportType'));
		    if(in_array('Content',$data))
		    {
		    	if(count($data) == 1)
			    {
			    	$sql = "update store set SupportType='Content' WHERE ID = '{$_POST['store_id']}'";
			    }
			    else
			    {
					$sql = "update store set SupportType='Mixed' WHERE ID = '{$_POST['store_id']}'";
				}
			}
		    else
		    {
			    $sql = "update store set SupportType='All' WHERE ID = '{$_POST['store_id']}'";
		    }
		    $db->query($sql);
		    echo true;
		    exit();
		    break;
		case 'update_ppc':
		    $time = date('Y-m-d H:i:s');
            $user = $_SERVER['PHP_AUTH_USER'];
            /*
             * Array ( [act] => update_ppc [program_id] => 13819 [ppcValue] => 3 [store_id] => 250 )
             * */
	        $sql = "select PPC from program_intell where ProgramId='{$_POST['program_id']}'";
	        $oldVal = $db->getFirstRowColumn($sql);
	        $sql = "insert into program_manual_change_log (ProgramId, FieldName,FieldValueOld,FieldValueNew,ModifyUser,AddTime,LastUpdateTime) VALUES ('{$_POST['program_id']}','PPC','{$oldVal}','{$_POST['ppcValue']}','{$user}','{$time}','{$time}')";
	        $db->query($sql);
	        $sql = "Update program_intell set  PPC='{$_POST['ppcValue']}' WHERE ProgramId='{$_POST['program_id']}'";
	        $db->query($sql);
	        
	        $sql = "insert into program_manual (ProgramId, PPC) VALUES ('{$_POST['program_id']}','{$_POST['ppcValue']}') on duplicate key update `PPC` = '{$_POST['ppcValue']}'";
	        $db->query($sql);
	        //同步store
	        $sql = "SELECT c.`PPC` FROM store a INNER JOIN r_store_program b ON a.`ID` = b.`StoreId` INNER JOIN program_intell c ON c.`ProgramId` = b.`ProgramId` WHERE a.id = '{$_POST['store_id']}';";
	        $data = array_keys($db->getRows($sql,'PPC'));
	        if(in_array('3',$data))
	        {
	            if(count($data) == 1)
	            {
	                $sql = "update store set PPCStatus='PPCAllowed' WHERE ID = '{$_POST['store_id']}'";
	            }
	            else
	            {
	                $sql = "update store set PPCStatus='Mixed' WHERE ID = '{$_POST['store_id']}'";
	            }
	        }
	        else
	        {
	            $sql = "update store set PPCStatus='NotAllow' WHERE ID = '{$_POST['store_id']}'";
	        }
	        $db->query($sql);
	        echo true;
	        exit();
	        break;
		    
        case 'add_publisher_page':
	    	$url_arr = explode(',',$_POST['url']);
	    	$user = $_SERVER['PHP_AUTH_USER'];
	    	$date = date('Y-m-d H:i:s');
	    	$repeat_num = 0;
	    	$repeat_url = '';
	    	$data = array(
	    		'status' => 'error',
			    'success' => 0,
			    'repeat' => 0,
			    'repeat_url' => ''
		    );
	    	if(count($url_arr) < 1)
	    		$data['status'] = 'empty';
	    	else
		    {
		        foreach ($url_arr as $url)
			    {
			    	$url = addslashes(trim($url,'/'));
			    	$sql = "select * from publisher_page where `Url`='{$url}'";
			    	$tmp = $db->getFirstRow($sql);
			    	$data['status'] = 'success';
			    	if(empty($tmp))
				    {
				    	$data['status'] = 'success';
				    	$sql = 'SELECT MAX(ID) FROM publisher_page';
				    	$id = $db->getFirstRowColumn($sql)+1;
				    	$sql = "insert into publisher_page (`ID`,`Url`,`AddTime`,`AddUser`) values ('$id','{$url}','{$date}','{$user}')";
				    	$db->query($sql);
				    }
				    else
				    {
				    	if($tmp['Status'] == 'error')
					    {
					    	$sql = "update publisher_page set `Status`='pending',`AddTime`='{$date}',`AddUser`='{$user}' where `ID`='{$tmp['ID']}'";
					    	$db->query($sql);
					    }
					    else{
				    		$repeat_num ++;
				    		$repeat_url .= '    ' . $url . "\n";
					    }
				    	
				    }
			    }
			    $data['success'] = count($url_arr) - $repeat_num;
		        $data['repeat'] = $repeat_num;
		        $data['repeat_url'] = $repeat_url;
		    }
		    echo json_encode($data,true);
		    break;
		    
        case 'outbound_check':
            $id = $_POST['id'];
            $check = $_POST['check'];
            $sql = "update check_outbound_log set Correct = '{$check}' where ID='{$id}'";
            if($db->query($sql))
            	echo true;
            else
            	echo false;
            break;
        case 'outbound_program_offline':
        	$time = date('Y-m-d H:i:s');
	        $user = $_SERVER['PHP_AUTH_USER'];
            $pid = $_POST['pid'];
            $sql = "insert into program_manual (ProgramId,StatusInBdg) values ($pid,'Inactive') on duplicate key update StatusInBdg = 'Inactive'";
            if($db->query($sql))
            {
            	$sql = "select IsActive from program_intell where ProgramId='{$pid}'";
	            $oldVal = $db->getFirstRowColumn($sql);
            	$sql = "insert into program_manual_change_log (ProgramId, FieldName,FieldValueOld,FieldValueNew,ModifyUser,AddTime,LastUpdateTime) VALUES ('{$pid}','StatusInBdg','{$oldVal}','Inactive','{$user}','{$time}','{$time}')";
            	$db->query($sql);
            	echo true;
            }
            else
            	echo false;
            break;
	    case 'outbound_replace':
	    	//TODO
	    	break;
        case 'outbound_change':
            $func = $_POST['func'];
            $pid = $_POST['pid'];
            if($func == 'submit')
            {
                $field = $_POST['field'];
            	$url = addslashes($_POST['url']);
            	$dids = trim($_POST['dids'],", \t\n\r\0\x0B");
            	$time = date('Y-m-d H:i:s');
            	if($dids)
	            {
	            	$did_arr = explode(',',$dids);
	            	foreach ($did_arr as $did)
		            {
		            	if($field == 'AffiliateDefaultUrl')
			            {
			            	$sql = "select AffiliateDefaultUrl from domain_outgoing_default_other where PID='{$pid}' and DID='{$did}'";
		            	    $val_old = addslashes($db->getFirstRow($sql)['AffiliateDefaultUrl']);
			            }
			            else
			            {
			            	$sql = "select DeepUrlTemplate from domain_outgoing_default_other where PID='{$pid}' and DID='{$did}'";
		            	    $val_old = addslashes($db->getFirstRow($sql)['DeepUrlTemplate']);
			            }
		            	
		            	$sql = "insert into outbound_change_log (PID,DID,FieldName,ValueOld,ValueNew,UpdateTime) values ('{$pid}','{$did}','{$field}','{$val_old}','{$url}','$time')";
		            	$db->query($sql);
		            	$sql = "update r_domain_program set $field='$url',LastUpdateTime='$time' where DID='$did' and PID='{$pid}'";
		            	if($db->query($sql))
		            		echo true;
		            }
	            }
            }
            else if($func == 'get_did')
            {
            	$sql = "select distinct did from domain_outgoing_default_other where PID='$pid'";
            	$data = array_keys($db->getRows($sql,'did'));
            	echo json_encode($data);
            }
            else
            	echo 'error';
            exit();
		    break;
        case 'homepage_check':
            $id = $_POST['id'];
            $date = date("Y-m-d H:i:s");
			$sql = "select a.`ID`,a.`PID`,a.`Old`,a.`New`,a.`Checked`,b.`IdInAff`,b.`Name`,b.`AffId` from check_homepage_log a inner join program b on a.`PID`=b.`ID` where a.`ID`='{$id}'";
			$program_data = $db->getFirstRow($sql);
			$sql = "update program set LastUpdateTime='{$date}' where `ID`='{$program_data['PID']}'";
			$db->query($sql);
			$sql = "update program_int set HomepageInt='{$program_data['New']}' where `ProgramId`='{$program_data['PID']}'";
			$db->query($sql);
			$sql = "insert into program_change_log (ProgramId,IdInAff,`Name`,AffId,FieldName,FieldValueOld,FieldValueNew,AddTime,`LastUpdateTime`) values ('{$program_data['PID']}','{$program_data['IdInAff']}','{$program_data['Name']}','{$program_data['Affid']}','TrueHomepage','{$program_data['Old']}','{$program_data['New']}','{$date}','{$date}')";
			$db->query($sql);
            $sql = "update check_homepage_log set Checked = 'YES' where ID='{$id}'";
            if($db->query($sql))
            	echo true;
            else
            	echo false;
			echo false;
            break;
        case 'content_check':
            $id = $_POST['id'];
            $check = $_POST['check'];
            if($check == 'NO')
            {
            	$sql = "update content_feed_new set `Status`='InActive' where `ID`={$id}";
            	$db->query($sql);
            }
            $sql = "update check_aff_url set Correct = '{$check}' where ContentFeedId='{$id}'";
            if($db->query($sql))
            	echo true;
            else
            	echo false;
            break;
        default:
            # code...
            break;
    }
}elseif(isset($_GET['act'])) {
    switch ($_GET['act']){
        case 'outLogExport':
            $listObj = new Outlog();
            $listFunction = 'get_out_going_log_data';
            $jsonstr = $_GET;
            $obj = new Tools();
            $obj->get_csv_export($listObj,$listFunction,$jsonstr);
            exit();
            break;
        case 'outLogExport1':
            $listObj = new Outlog();
            $listFunction = 'get_out_going_log_data';
            $jsonstr = $_GET;
            $obj = new Tools();
            $obj->get_csv_export1($listObj,$listFunction,$jsonstr);
            exit();
            break;
        case 'transactionExport':
            $listObj = new Transaction();
            $listFunction = 'getTransactionListPage';
            $jsonstr = $_GET;
            $obj = new Tools();
            $obj->get_transaction_csv_export($listObj,$listFunction,$jsonstr);
            exit();
            break;//transactionExport
        case 'downloadcontent':
            $merchant = new MerchantExt();
            $merchant->GetContentCsvFile($_GET);
            break;
        case 'downloadcontentnew':
            $merchant = new MerchantExt();
            $merchant->GetContentCsvFileNew($_GET);
            break;
        case 'storePerformanceCsv' :
            $jsonstr = $_GET;
            $obj = new Tools();
            $obj->store_performance($jsonstr);
            break;
        case 'PerformanceCsv':
            $listObj = new Transaction();
            $listFunction = 'getTransactionRptSite';
            $jsonstr = $_GET;
            $obj = new Tools();
            $obj->performance_site($listObj,$listFunction,$jsonstr);
            break;
        case 'PerformanceCsv_adv':
            $listObj = new Transaction();
            $listFunction = 'getTransactionRpt';
            $jsonstr = $_GET;
            $obj = new Tools();
            $obj->performance_adv($listObj,$listFunction,$jsonstr);
            break;
        case "PerformanceCsv_aff" :
            $listObj = new Transaction();
            $listFunction = 'getTransactionAffRpt';
            $jsonstr = $_GET;
            $obj = new Tools();
            $obj->performance_aff($listObj,$listFunction,$jsonstr);
            break;
        case 'AdvertiserCsv':
             $merchant = new MerchantExt();
             $merchant->GetAdvertiserCsvCsvFile($_GET);
             break;
        case 'AnalysisCsv':
            $merchant = new MerchantExt();
            $merchant->AnalysisCsv($_GET);
            break;
        case 'AnalysisCsv_page':
            $merchant = new MerchantExt();
            $merchant->AnalysisCsv_Page($_GET);
            break;
        case 'AnalysisCsv2':
            $merchant = new MerchantExt();
            $merchant->AnalysisCsv2($_GET);
            break;
        case 'AnalysisCsv2_Page':
            $merchant = new MerchantExt();
            $merchant->AnalysisCsv2_Page($_GET);
            break;
        case 'PartnershipTempCsv' :
            $merchant = new MerchantExt();
            $merchant->partnershipTempCsv($_GET);
            break;


    }
} else {
    echo 0;
    exit();
}
?>