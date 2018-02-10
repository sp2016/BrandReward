<?php

Class Promo {
    
    private $api;
    private $para;
    private $opts = array('lt', 'gt', 'le', 'ge', 'sw', 'ne', 'eq', 'li');
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

        if ($opt == 'li') {
            if (isset($this->para[$col]) && is_array($this->para[$col]))
                array_push($this->para[$col], $val);
            else
                $this->para[$col] = array($val);
        }
        else
            $this->para[$col] = $val;
    }

    public function Page($start=1, $num=500) {
        if ($start > 0)
            $this->start = $start;
        if ($num > 0)
            $this->num = $num;        
    }

    
    public function get() {
        $this->para['start'] = $this->start;
        $this->para['rows']   = $this->num;

        return $this->api->Sent($this->para);
    }
}
