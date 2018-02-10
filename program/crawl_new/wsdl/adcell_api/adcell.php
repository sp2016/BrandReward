<?php

// Objekt anlegen
//$api = new AdcellApi();

// Token ermitteln
/*$token = $api->getToken(
    array(
        'userName' => '[userId]',
        'password' => '[apiPasswort]',
    )
);

// Abfrage mit Token starten
$user = $api->whoAmI(
    array(
        'token' => $token,
    )
);

// Ergebnis ausgeben
print_r($user);*/

/**
 * Einfache API-Request - Beispiel-Klasse
 *
 * @author api@adcell.de
 */
class AdcellApi {
    
    /**
     * Url zur API
     *
     * @var string
     */
    protected $_apiUrl = 'https://www.adcell.de/api/';

    /**
     * Version der API
     * 
     * @var string
     */
    protected $_apiVersion = 'v2';

    /**
     * Liefert die BasisUrl aus
     *
     * @return string
     */
    protected function _getApiBaseUrl() {
        return $this->_apiUrl . $this->_apiVersion;
    }

    /**
     * Dekodierung, hier im Beispiel nur von json zu stdclass
     *
     * @param  string $data Daten
     * @param  string $format (Optional) Format
     * @return \stdClass
     */
    protected function _decode($data, $format = 'json'){
        if ($format == 'json') {
            return json_decode($data);
        }
    }

    /**
     * Startet einen Request und liefert Daten als stdClass zur��ck
     *
     * @param  string $service ServiceName
     * @param  string $call MethodenName
     * @param  array $options Optionen
     * @return \stdClass
     */
    protected function _request($service, $call, $options) {
        $url = $this->_getApiBaseUrl() . '/' . $service . '/' . $call . '?';

        foreach ($options as $key => $value) {
            
            if($key == 'programIds[]'){
                foreach ($value as $v){
                    $url .= '&programIds[]=' . $v;
                }
            }else{
                $url .= '&' . $key . '=' . $value;
            }
        }
        //echo $url.PHP_EOL;
        $data = file_get_contents($url);
        //if (strlen($data) == 0) {
        //    throw new \Exception('invalid result received');
        //}

        $data = $this->_decode($data);
        if ($data->status == 200) {
            return $data;
        } else {
            throw new \Exception($data->message);
        }
    }

    /**
     * Ermittlung des Tokens
     * 
     * @param  array $options Optionen
     * @return string
     */
    public function getToken($options) {
        $data = $this->_request(
            'user', 
            'getToken', 
            array(
                'userName' => $options['userName'],
                'password' => $options['password'],
            )
        );

        return $data->data->token;
    }

    /**
     * BeispielRequest f��r Schnittstelle user/whoami
     *
     * @return \stdClass
     */
    public function whoAmI($option){
        return $this->_request(
            'user', 
            'whoami', 
            $option
        );
    }
    
    //https://www.adcell.de/api/v2/affiliate/program/export 
    public function apply($option){
        $data = $this->_request(
            'affiliate/program',
            'export',
            $option
        );
        return $data;
    }
    
    //https://www.adcell.de/api/v2/affiliate/program/getCommissions
    public function commission($option){
        $data = $this->_request(
            'affiliate/program',
            'getCommissions',
            $option
        );
        return $data;
    }
    //https://www.adcell.de/api/v2/program/getCategories 
    public function category($option){
        $data = $this->_request(
            'program',
            'getCategories',
            $option
        );
        return $data;
    }
    
    
    
    
}

?>        