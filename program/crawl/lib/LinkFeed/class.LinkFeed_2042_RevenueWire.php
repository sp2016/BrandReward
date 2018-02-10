<?php
class LinkFeed_2042_RevenueWire
{
    function __construct($aff_id,$oLinkFeed)
    {
        $this->oLinkFeed = $oLinkFeed;
        $this->info = $oLinkFeed->getAffById($aff_id);
        $this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
    }

    function LoginIntoAffService($retry)
    {
        $request = array(
            "AffId" => $this->info["AffId"],
            "method" => "get",
            "postdata" => "",
            'addheader' => array(
                ':authority:affiliate.revenuewire.com',
                ':method:GET',
                //':path:/auth/login',
                ':scheme:https',
                'accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'accept-encoding:gzip, deflate, br',
//                'cookie:rw-host-id=php-app3-1prodwestcdebluevlive63; visid_incap_187201=FZ+mFKGNQEmF4VTIjz8XePU/LloAAAAAQkIPAAAAAACAy8WAAXa4BX9oNfdcfVda6YRcbBNSP96s; PHPSESSID=apnu2pgpvrgog1r6eujhkpgn36; RWSERVERID=php-app3; incap_ses_256_187201=a7MpM4tt2iFkKpLHq36NA0U3L1oAAAAAnNMbuUB16Ji8/N0KRfcTPw==',
                'cache-control:max-age=0',
                'accept-language:zh-CN,zh;q=0.8',
                'upgrade-insecure-requests:1',
                'user-agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36'
            ),
            'header' => 1
        );
        $r = $this->oLinkFeed->GetHttpResult("https://affiliate.revenuewire.com/auth/login", $request);
        $result = $r["content"];
        $token = trim($this->oLinkFeed->ParseStringBy2Tag($result, array("name='token'","value='"), "'"));
        print_r($r);exit;
        $request = array("AffId" => $this->info["AffId"],
            "method" => "post",
            "postdata" => "csrf={$token}&username={$this->info['Account']}&password={$this->info['Password']}",
        );
        $r = $this->oLinkFeed->GetHttpResult("https://affiliate.revenuewire.com/auth/login", $request);
        print_r($r);exit;
        if (stripos($r['content'], $this->info['AffLoginVerifyString']) !== false) {
            echo "\tlogin success : wellcome {$this->info['AffLoginVerifyString']}\r\n";
        } else {
            if ($retry > 0) {
                echo "\tlogin failed ,will be retry after 30 second!\r\n";
                sleep(30);
                $this->LoginIntoAffService(--$retry);
            } else {
                mydie("Login failed!");
            }
        }
    }

    function GetProgramFromAff()
    {
        $check_date = date("Y-m-d H:i:s");
        echo "Craw Program start @ {$check_date}\r\n";

        $this->GetProgramByPage();
        $this->checkProgramOffline($this->info["AffId"], $check_date);

        echo "Craw Program end @ ".date("Y-m-d H:i:s")."\r\n";
    }

    function GetProgramByPage()
    {


    }


    function checkProgramOffline($AffId, $check_date){
        $objProgram = new ProgramDb();
        $prgm = array();
        $prgm = $objProgram->getNotUpdateProgram($this->info["AffId"], $check_date);
        if(count($prgm) > 30){
            mydie("die: too many offline program (".count($prgm).").\n");
        }else{
            $objProgram->setProgramOffline($this->info["AffId"], $prgm);
            echo "\tSet (".count($prgm).") offline program.\r\n";
        }
    }
}