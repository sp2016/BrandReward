<?php 

Class BcgApi {
    const MAX_RTRY = 3;
    private $user = '';
    private $path_svr = '';

    public function __construct($env='PRODUCT') {
        $path = dirname(__FILE__).'/../../';
        $file_acct = $path.'account.ini';
        if (!file_exists($file_acct))
            throw new Exception ("No account setting");

        $conf = parse_ini_file($file_acct, true);
        $this->user = $conf[$env];
        
        $this->path_svr = $path.'Service/'.$this->user['version'].'/';
    }        


    public function GetService($svr="") {
        $file_c = $this->path_svr.ucwords($svr).'.php';   
        if ($svr == "" || !file_exists($file_c))
            throw new Exception ("Service not exitsts");            
        $this->service = $svr;
        include_once $file_c;
        return new $svr($this);
    }

	public function GetQuery($para='') {
		$arr = array();
		foreach ($para as $k => $v) {
			if (is_array($v)) {
                foreach ($v as $_v) {
                    array_push($arr, "{$k}[]=".urlencode($_v));
                }
            }
            else {
                array_push($arr, "{$k}=".urlencode($v));
		    }
        }
		return implode('&', $arr);	
	}



    public function Sent($para, $method='GET', $uri = '') {
        $timestamp = gmdate(DATE_RFC1123);           
        $headers = array("BCG-DATE: {$timestamp}", "BCG-AUTHTOKEN: ".$this->CreateAuthzToken($timestamp), "BCG-USER: {$this->user['user_name']}");
        $curl_opts = array(CURLOPT_NOBODY => false,
                           CURLOPT_RETURNTRANSFER => true,
                           CURLOPT_USERAGENT => 'BCG Client v1.0',
                           CURLOPT_HTTPHEADER => $headers,
                           CURLOPT_SSL_VERIFYPEER => false,
                           CURLOPT_CUSTOMREQUEST => $method, 
                   );

		$query = '';
		if ($method == 'POST') {
			$curl_opts[CURLOPT_POSTFIELDS] = $para;

		}
		else {
			$query = '?'.$this->GetQuery($para);			
		}

        $ch = curl_init($this->user['apihost'].'/'.$this->service.($uri? "/{$uri}" : "").'/'.$query);
        curl_setopt_array($ch, $curl_opts);
        $r = 0;
        do {
            $rs = curl_exec($ch);
            if ($rs)
                break;

            if ($r++ > self::MAX_RTRY)
                throw new Exception ("Request failed!");            
        }while (true);
    
        curl_close($ch);
    
		
        //var_dump($rs);exit;
		$rs = json_decode($rs);
        //var_dump($rs);
        if (!isset($rs->succ))
            throw new Exception ("TrackingId: {$rs->trackingId} \n Err: Request failed!");

        if ($rs->succ == 0)
           throw new Exception ($rs->msg);
        
        return $rs;
    }

    public function CreateAuthzToken($timestamp) {
        return hash("sha256", $this->service .":". $timestamp . ':'. $this->user['auth_token']);
    }

	   


}
