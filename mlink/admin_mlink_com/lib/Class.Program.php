<?php

class Program extends LibFactory
{
    function get_program_list($data, $page_size = 50)
    {
        $return_d = array();
        $where = array();
        $where_str = '';
        $cate = '';
        if (isset($data['affiliate']) && !empty($data['affiliate'])) {
            $aff_row = $this->table('wf_aff')->where('(`Name` = "' . addslashes($data['affiliate']) . '" OR `ShortName` = "' . addslashes($data['affiliate']) . '") AND `IsActive` = "YES"')->field('ID')->findone();
            if ($aff_row)
                $where[] = 'p.AffId = ' . intval($aff_row['ID']);
        }

        if (isset($data['program']) && !empty($data['program'])) {
            $where[] = '(p.Name like "' . addslashes($data['program']) . '%" OR p.IdInAff = "' . $data['program'] . '")';
        }
        if(isset($data['categories']) && !empty($data['categories'])){
            $categoryArr = explode(',',trim($data['categories'],','));
            if(!empty($categoryArr))
            {
                $cate .= " (";
                foreach($categoryArr as $cateid)
                {
                    $cate .= " FIND_IN_SET('$cateid',p.CategoryExt) OR";
                }
                $where[]  = rtrim($cate,'OR').")";
            }
        }
        if (isset($data['partnership']) && !empty($data['partnership'])) {
            $where[] = 'p.Partnership = "' . addslashes($data['partnership']) . '"';
        }
        if (isset($data['country']) && !empty($data['country'])) {
            $country = $data['country'];
            $where[] = " FIND_IN_SET('$country',pi.ShippingCountry)";
        }
        if (isset($data['statusinaff']) && !empty($data['statusinaff'])) {
            $where[] = 'p.StatusInAff = "' . addslashes($data['statusinaff']) . '"';
        }

        if (isset($data['statusinbdg']) && !empty($data['statusinbdg'])) {
            $where[] = 'pm.StatusInBdg = "' . addslashes($data['statusinbdg']) . '"';
        }

        if (isset($data['domain']) && !empty($data['domain'])) {
            $domain_row = $this->table('domain')->where('Domain = "' . addslashes($data['domain']) . '"')->field('ID')->findone();
            if ($domain_row) {
                $rdp_rows = $this->table('r_domain_program')->where('Status = "Active" AND DID = ' . intval($domain_row['ID']))->find();
                if ($rdp_rows) {
                    $pids = array();
                    foreach ($rdp_rows as $rpd) {
                        $pids[] = intval($rpd['PID']);
                    }
                    $where[] = 'p.ID IN (' . join(',', $pids) . ')';
                } else {
                    $where[] = 'p.ID IN ("")';
                }
            }
        }
        $where_str = empty($where) ? ' WHERE w.IsActive = "YES"' : ' WHERE w.IsActive = "YES" AND ' . join(' AND ', $where);
        $page = isset($data['p']) ? $data['p'] : 1;
        $sql = 'SELECT COUNT(*) as c FROM program AS p LEFT JOIN program_intell AS `pi` ON p.ID = pi.ProgramId LEFT JOIN program_manual as pm ON p.ID = pm.ProgramId LEFT JOIN wf_aff as w ON p.AffId = w.ID' . $where_str;
        $row = $this->getRow($sql);
        $return_d['page_size'] = $page_size;
        $return_d['page_count'] = $row['c'];
        $return_d['page_total'] = ceil($return_d['page_count'] / $return_d['page_size']);
        $return_d['page_now'] = $page;

        $sql = 'SELECT p.ID,p.Name,p.AffId,p.IdInAff,p.StatusInAff,p.CategoryExt,p.Partnership,p.CommissionExt,p.Homepage,pi.CommissionUsed,pi.CommissionCurrency,pi.CommissionType,pi.ShippingCountry,pi.Domain,pi.IsActive FROM program AS p LEFT JOIN program_intell AS `pi` ON p.ID = pi.ProgramId  LEFT JOIN program_manual as pm ON p.ID = pm.ProgramId LEFT JOIN wf_aff as w ON p.AffId = w.ID ' . $where_str . ' ORDER BY p.ID' . ' LIMIT ' . ($page - 1) * $page_size . ',' . $page_size;
        $rows = $this->getRows($sql);

        foreach($rows as $k=>$v){
            if(strstr($v['ShippingCountry'],',')){
              $str = preg_replace('/,/',"','",$v['ShippingCountry']);
                $code =  "'".$str."'";
                $sql = "select countryname from country_codes WHERE CountryCode in($code) ORDER BY countryname";
                $res = $this->getRows($sql);

            }

        }

// 		echo "<pre>";
// 		print_r($rows);
        $pids = array();
        $affid = array();
        foreach ($rows as $k => $v) {
            $pids[] = $v['ID'];
            $affid[] = $v['AffId'];
        }

        $aff_rows = array();
        if (count($affid) > 0) {
            $sql = 'SELECT ID,Name FROM wf_aff WHERE ID IN (' . join(',', $affid) . ')';
            $aff_tmp = $this->getRows($sql);

            foreach ($aff_tmp as $k => $v) {
                $aff_rows[$v['ID']] = $v;
            }
        }

        $pm_rows = array();
        if (count($pids) > 0) {
            $sql = 'SELECT * FROM program_manual WHERE ProgramId IN (' . join(',', $pids) . ')';
            $pm_tmp = $this->getRows($sql);

            foreach ($pm_tmp as $k => $v) {
                $pm_rows[$v['ProgramId']] = $v;
            }
        }


        $return_d['data'] = $rows;
        $return_d['aff'] = $aff_rows;
        $return_d['pm'] = $pm_rows;
        return $return_d;
    }

    function get_program_one($id)
    {
        $sql = 'SELECT p.ID,p.Name,p.AffId,p.IdInAff,p.StatusInAff,p.Partnership,p.Homepage,p.CommissionExt,pi.CommissionUsed,pi.CommissionCurrency,pi.CommissionType,pi.ShippingCountry,pi.Domain FROM program AS p LEFT JOIN program_intell AS `pi` ON p.ID = pi.ProgramId WHERE p.ID =  ' . intval($id);


        $row = $this->getRow($sql);

        $aff = $this->table('wf_aff')->where('ID = ' . intval($row['AffId']))->findone();

        $pm = $this->table('program_manual')->where('ProgramId = ' . intval($id))->findone();

        $return_d = array();
        $return_d['p'] = $row;
        $return_d['aff'] = $aff;
        $return_d['pm'] = $pm;

        return $return_d;
    }

    function get_program_rpt($data, $page_size = 20)
    {
        $return_d = array();

        $page = isset($data['p']) ? $data['p'] : 1;


        $where_a_str = '';
        $where_b_str = '';
        $where_a = array();
        $where_b = array();

        if (isset($data['aff']) && !empty($data['aff'])) {
            $row = $this->table('wf_aff')->where('Name = "' . addslashes($data['aff']) . '"')->field('ID')->findone();
            if ($row)
                $where_b[] = 'b.AffId = ' . intval($row['ID']);
        }

        if (!isset($data['tran_from']) || empty($data['tran_from']) || !isset($data['tran_to']) || empty($data['tran_to'])) {
            return $return_d;
        }

        if (isset($data['tran_from']) && $data['tran_from']) {
            $where_a[] = 'createddate >= "' . $data['tran_from'] . '"';
        }
        if (isset($data['tran_to']) && $data['tran_to']) {
            $where_a[] = 'createddate <= "' . $data['tran_to'] . '"';
        }

        if (isset($data['pidinaff']) && $data['pidinaff'] > 0) {
            $row = $this->table('program')->where('IdInAff = "' . intval($data['pidinaff']) . '"')->findone();
            if ($row) {
                $where_a[] = 'programId = ' . intval($row['ID']);
            } else {
                $where_a[] = 'programId > 0';
            }
        } else {
            $where_a[] = 'programId > 0';
        }

        // if(isset($data['af']) && $data['af']){
        // 	$where_a[] = 'af = "'.addslashes($data['af']).'"';
        // }
        if (!empty($where_a))
            $where_a_str = ' WHERE ' . join(' AND ', $where_a);
        if (!empty($where_b))
            $where_b_str = ' WHERE ' . join(' AND ', $where_b);

        $sql = 'SELECT COUNT(*) AS c FROM (SELECT a.* FROM (SELECT programId,SUM(clicks) AS clicks FROM `statis_program_br` ' . $where_a_str . ' GROUP BY programId ) AS a LEFT JOIN program AS b ON a.programId = b.ID ' . $where_b_str . ') AS cc';

        $row = $this->getRow($sql);
        $return_d['page_size'] = $page_size;
        $return_d['page_count'] = $row['c'];
        $return_d['page_total'] = ceil($return_d['page_count'] / $return_d['page_size']);
        $return_d['page_now'] = $page;


        $sql = 'SELECT a.*,b.Name as p_name,b.AffId as aid,b.IdInAff as pidinaff FROM (SELECT programId,SUM(clicks) as clicks,SUM(orders) as orders,SUM(revenues) as revenues,SUM(sales) as sales FROM `statis_program_br` ' . $where_a_str . ' GROUP BY programId ) AS a LEFT JOIN program AS b ON a.programId = b.ID ' . $where_b_str . ' ORDER BY a.revenues DESC LIMIT ' . ($page - 1) * $page_size . ',' . $page_size;

        $rows = $this->getRows($sql);

        if (empty($rows)) {
            return array();
        }

        $aids = array();
        foreach ($rows as $k => $v) {
            if ($v['aid'])
                $aids[] = $v['aid'];
        }

        $aff_tmp = $this->table('wf_aff')->where('ID IN (' . join(',', $aids) . ')')->field('ID,Name')->find();
        $aff_row = array();
        foreach ($aff_tmp as $k => $v) {
            $aff_row[$v['ID']] = $v['Name'];
        }
        foreach ($rows as $k => $v) {
            if ($v['aid'] || isset($aff_row[$v['aid']]))
                $rows[$k]['a_name'] = $aff_row[$v['aid']];
            else
                $rows[$k]['a_name'] = '';
        }

        $return_d['data'] = $rows;
        return $return_d;
    }

    function save_program($data)
    {
        if (!isset($data['id'])) {
            return;
        }
        $currency = empty($data['CommissionCurrency']) ? '' : ',CommissionCurrency="' . addslashes($data['CommissionCurrency']) . '"';

        $row = $this->table('program_manual')->where('ProgramId = ' . intval($data['id']))->findone();
        if ($row) {
            $sql = 'UPDATE program_manual SET RealDomain = "' . addslashes($data['RealDomain']) . '",CommissionUsed = ' . floatval($data['CommissionUsed']) . ',CommissionType= "' . addslashes($data['CommissionType']) . '",StatusInBdg = "' . addslashes($data['StatusInBdg']) . '"' . $currency . ' WHERE ProgramId = ' . intval($data['id']);
        } else {
            $sql = 'INSERT INTO program_manual SET RealDomain = "' . addslashes($data['RealDomain']) . '",CommissionUsed = ' . floatval($data['CommissionUsed']) . ',CommissionType= "' . addslashes($data['CommissionType']) . '",StatusInBdg = "' . addslashes($data['StatusInBdg']) . '",ProgramId = ' . intval($data['id']) . '' . $currency;
        }

        $this->query($sql);
    }
}
