<?php
namespace App\Http\Logic;

use App\Model\CountryCodes;

class CountryLogic extends BasicLogic
{
    public function getCountryCode()
    {
        $model = new CountryCodes();
        $countryCodes = $model::query()->lists('CountryCode','CountryName');
        
        return $countryCodes;
    }
}