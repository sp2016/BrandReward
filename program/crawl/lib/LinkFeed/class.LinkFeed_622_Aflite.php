<?php
include_once('simple_html_dom.php');//解析html类


class LinkFeed_622_Aflite
{

    function __construct($aff_id, $oLinkFeed)
    {
       // $this->oLinkFeed = $oLinkFeed;
        $this->oLinkFeed = new LinkFeed();
        $this->info =  $this->oLinkFeed->getAffById($aff_id);
        if (!isset($this->info) || empty($this->info)) {
            $this->info =  $this->oLinkFeed->getAffById($aff_id);
        }
        $this->debug = isset( $this->oLinkFeed->debug) ?  $this->oLinkFeed->debug : false;
    }

    public function GetProgramFromAff()
    {
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => "post",
            "postdata" => $this->info['AffLoginPostString']
        );

        $this->oLinkFeed->LoginIntoAffService($this->info["AffId"],$this->info);//登录成功，返回true
        $url = 'https://aflite.co.uk/affiliates/account/merchants/yours.php';
        $data = $this->oLinkFeed->GetHttpResult($url,$request);
        /*
           获取table里面的连接
        */
        $html = new simple_html_dom();
        $html->load($data['content']);
        $url = $html->find('table',0);
        $dom = new simple_html_dom();
        $dom->load($url);
        foreach($dom->find('a') as $element){
            $urlarr[] = 'https://aflite.co.uk'.$element->href;
        }
        $dom->clear();
        $html->clear();
        /*
                  获取table里面的连接
        */
        $request = array(
            "AffId" => $this->info["AffId"],
        );

        foreach($urlarr as $k => $v){
            //  echo $k.'----';
              $urldata = $this->oLinkFeed->GetHttpResult($v,$request);
              $htmlone = new simple_html_dom();
              $htmlone->load($urldata['content']);
              $data = $htmlone->find('.content',0)->innertext;
              $htmlone->clear();
              $html = new simple_html_dom();
              $html->load($data);
              $dataarr = array();
              $static= $html->find('span',0)->innertext;
              $reqstaric = $html->find('.requestStatus',0)->innertext;
              $reqstaric = explode(':',$reqstaric);

              $Homepage = $html->find('a',0)->href;
              $AffDefaultUrl = $html->find('#links a',0)->href;
              $Commission = '';
              foreach($html->find('#definitions .value') as $element){

                  if(!preg_match('/\s+/',$element->innertext))
                  {
                      $Commission.= $element->innertext.',';
                  }
              }
              $Description = $html->find('p',1)->innertext;
              $html->find('.comparision',0)->innertext;
              $namearr= $html->find('h1',0)->innertext;
              $namearr = explode('<small>',$namearr);
              $html->clear();

              $idinaff = preg_replace('/\D/','',$namearr[1]);
              $datarr['Name'] = addslashes($namearr[0]);
              $dataarr['Description'] =addslashes(preg_replace('{</?[^>]+>}','',$Description));
              $datarr['Homepage'] = addslashes($Homepage);
              $datarr['AffDefaultUrl'] = addslashes($AffDefaultUrl);
              $datarr['CommissionExt'] = substr($Commission,0,-1);
              $datarr['StatusInAff'] = $static;
              $dataarr['StatusInAffRemark'] = preg_match('/Declined/',$reqstaric[1])?'Declined':$reqstaric[1];
              $datarr['IdInAff'] =  $idinaff;
              $datarr['AffId'] =  $this->info['AffId'];
              $newarr[$idinaff] = $datarr;

          }

        $db = new ProgramDb();
        $res = $db->updateProgram($this->info['AffId'], $newarr);


    }

    function checkProgramOffline($AffId, $check_date)
    {
        $prgm = array();
        $DB = new ProgramDb();
        $prgm = $DB->getNotUpdateProgram($AffId, $check_date);
        if (count($prgm) > 30) {
            mydie("die: too many offline program (" . count($prgm) . ").\n");
        } else {
            $DB->setProgramOffline($AffId, $prgm);
            echo "\tSet (" . count($prgm) . ") offline program.\r\n";
        }
    }

}
?>
