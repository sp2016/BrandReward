<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Affiliate_calculation_logic extends Calculation_logic
{
    public function get_cal_logic_name()
    {
        if (!$this->is_empty('query_type'))
        {
            switch ($this->query_type)
            {
                case 'D' :
                    return static::DEAL_DATA_DAILY_LOGIC;
                    break;
                case 'M' :
                    return static::DEAL_DATA_MONTH_LOGIC;
                    break;
            }
        }

        return static::DEAL_DATA_DAILY_LOGIC;
    }
}