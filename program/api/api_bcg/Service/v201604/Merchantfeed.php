<?php

Class Merchantfeed {
    
    private $api;
    private $para;
    private $opts = array('lt', 'gt', 'le', 'ge', 'sw', 'ne', 'eq');
    private $start = 1;
    private $num   = 500;

    public function __construct(BcgApi $api) {
        $this->api = $api;    
    }

    public function Select($col="", $val="", $opt="") {
        if ($col == "")
            throw new Exception ("Unknown failed");

        if (in_array($opt, $this->opts))
            $col = strtolower($col."-".$opt);
        else
            $col = strtolower($col);

        $this->para[$col] = $val;
    }

    public function Page($start=1, $num=500) {
        if ($start > 0)
            $this->start = $start;
        if ($num > 0)
            $this->num = $num;        
    }

    
    public function get($mkt="") {
        $this->para['start'] = $this->start;
        $this->para['num']   = $this->num;

        return $this->api->Sent($this->para,'GET',$mkt);
    }
}
