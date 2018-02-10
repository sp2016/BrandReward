<?php
/**
 * User: rzou
 * Date: 2017/9/12
 * Time: 10:02
 */
try {
    echo "\tGet ip start \n\r";
    define('USERNAME', 'ryanzou@brandreward.com');
    define('PASSWORD', 'Kko9fLdo9LCs');
    define('USERAGENT', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2');

    $dirName = dirname(__FILE__);
    $ip4_zip_temp = $dirName . '/country_state_ip4.zip';
    $ip6_zip_temp = $dirName . '/country_state_ip6.zip';
    $file_cook = $dirName . '/temp_cook_file.cook';

    $url = 'https://lite.ip2location.com/login';
    $post_arr = array(
        'emailAddress' => USERNAME,
        'password' => PASSWORD,
    );
    $loginOptions = array(
        CURLOPT_URL => $url,
        CURLOPT_POST => TRUE,
        CURLOPT_POSTFIELDS => http_build_query($post_arr),
        CURLOPT_FRESH_CONNECT => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_FORBID_REUSE => TRUE,
        CURLOPT_TIMEOUT => 100,
        CURLOPT_USERAGENT => USERAGENT,
        CURLOPT_COOKIEJAR=>$file_cook,
        CURLOPT_COOKIEFILE=>$file_cook,
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_SSL_VERIFYHOST=>false,
    );
    $ch = curl_init();
    curl_setopt_array($ch, $loginOptions);
    if(!$result = curl_exec($ch)) {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);

    if (strpos($result, USERNAME) === false) {
        die("Login to 'lite.ip2location.com' failed !");
    } else {
        echo "\tLogin success : " . USERNAME . "\r\n";
    }

    //get ipv4 zip
    if (file_exists($ip4_zip_temp))
        unlink($ip4_zip_temp);

    $fw = fopen($ip4_zip_temp, 'w');

    if (!$fw)
        throw new Exception ("File open failed {$ip4_zip_temp}");

    $download_url = 'https://lite.ip2location.com/download?id=3';
    $downloadOptions = array(
        CURLOPT_URL => $download_url,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_TIMEOUT => 1000,
        CURLOPT_USERAGENT => USERAGENT,
        CURLOPT_COOKIEJAR => $file_cook,
        CURLOPT_COOKIEFILE => $file_cook,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FILE => $fw,
    );
    $ch = curl_init();
    curl_setopt_array($ch, $downloadOptions);
    if (!$result = curl_exec($ch)) {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    fclose($fw);

    //get ipv6 zip
    if (file_exists($ip6_zip_temp))
        unlink($ip6_zip_temp);

    $fw = fopen($ip6_zip_temp, 'w');

    if (!$fw)
        throw new Exception ("File open failed {$ip6_zip_temp}");

    $download_url = 'https://lite.ip2location.com/download?id=5';
    $downloadOptions = array(
        CURLOPT_URL => $download_url,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_TIMEOUT => 1000,
        CURLOPT_USERAGENT => USERAGENT,
        CURLOPT_COOKIEJAR => $file_cook,
        CURLOPT_COOKIEFILE => $file_cook,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FILE => $fw,
    );
    $ch = curl_init();
    curl_setopt_array($ch, $downloadOptions);
    if (!$result = curl_exec($ch)) {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    fclose($fw);

    echo "\tdownload zip success ! \r\n";

    if (!is_dir('/home/bdg/logs/country_state_ip4')) {
        mkdir('/home/bdg/logs/country_state_ip4');
    }
    if (!is_dir('/home/bdg/logs/country_state_ip6')) {
        mkdir('/home/bdg/logs/country_state_ip6');
    }

    echo "\tunzip -o -d /home/ryan/country_state_ip4 $ip4_zip_temp\r\n";
    exec("unzip -o -d /home/bdg/logs/country_state_ip4 $ip4_zip_temp");

    echo "\tunzip -o -d /home/ryan/country_state_ip6 $ip6_zip_temp\r\n";
    exec("unzip -o -d /home/bdg/logs/country_state_ip6 $ip6_zip_temp");

    echo "\tunzip succ\r\n";

    if (!file_exists('get_ip_country_state.sh')) {
        die("Can't find mysql load shell file!");
    }else {
        system("chmod 777 get_ip_country_state.sh");
        system('sed -i "s/\r//" get_ip_country_state.sh');
    }

    system('./get_ip_country_state.sh');

    unlink($ip4_zip_temp);
    unlink($ip6_zip_temp);
    unlink($file_cook);

}catch (Exception $e){
    die($e->getMessage());
}


