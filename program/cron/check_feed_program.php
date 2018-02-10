<?php
//检查content_feed_new 里面的program是否有效, 同时添加country。
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
$length = 100; //每次取100条

echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";


$objProgram = New ProgramDb();
$nowDay = date('Y-m-d H:i:s',time());
$startTime = date('Y-m-d 00:00:00',time());
$i = 0;
$j = 0;
$k = 0;
$new_content = 0;
do{
    $offset = $length*$i- 1 > 0 ? $length*$i- 1 : 0;
    $sql = "select ID,ProgramId,StoreId from content_feed_new where  status = 'Active' limit $offset, $length";
      
    $data = $objProgram->objMysql->getRows($sql);
    $i++;
    foreach ($data as $value){
        
       if($value['ProgramId']){ //check program and update country
           $programInfo = array();
           $programSql = "select a.ID,b.ShippingCountry from program a inner join program_intell b on a.id = b.programid where a.StatusInAff = 'Active' and a.Partnership = 'Active' and b.isactive = 'active' and a.id = {$value['ProgramId']}";
           $programInfo = $objProgram->objMysql->getFirstRow($programSql);
           if(empty($programInfo)){
               $updateFeed = "update content_feed_new set status = 'InActive',LastUpdateTime = '$nowDay' where id = {$value['ID']}";
               $objProgram->objMysql->query($updateFeed);
               $j++;
           }else{
               $updateFeed = "update content_feed_new set country = '{$programInfo['ShippingCountry']}' where id = {$value['ID']}";
               $objProgram->objMysql->query($updateFeed);
               $k++;
           }
       }elseif($value['StoreId']){
           $storeInfo = array();
           $sql = "select CountryCode from store where id = {$value['StoreId']}";
           $storeInfo = $objProgram->objMysql->getFirstRow($sql);
           if(!empty($storeInfo)){
               $updateFeed = "update content_feed_new set country = '{$storeInfo['CountryCode']}' where id = {$value['ID']}";
               $objProgram->objMysql->query($updateFeed);
           }
           $k++;
       }
       
    }
    
    
}while(count($data)>0);

echo "Set $j content feed Inactive.\r\n";
echo "Set $k content feed country. ".date('Y-m-d H:i:s',time())." \r\n";


//product feed, 无效的program inactive & add product country.
echo "Start Check Product Feed Program.\r\n";
$i = 0;
$j = 0;
$k = 0;
do{
    $offset = $length*$i- 1 > 0 ? $length*$i- 1 : 0;
    $sql = "select ID,ProgramId,StoreId,Country from product_feed where  status = 'Active' AND Country IS NULL limit $offset, $length";

    $data = $objProgram->objMysql->getRows($sql);
    $i++;
    foreach ($data as $Pvalue){

        if($Pvalue['ProgramId']){ //check program and update country
            $programInfo = array();
            $programSql = "select a.ID,b.ShippingCountry from program a inner join program_intell b on a.id = b.programid where a.StatusInAff = 'Active' and a.Partnership = 'Active' and b.isactive = 'active' and a.id = {$Pvalue['ProgramId']}";
            $programInfo = $objProgram->objMysql->getFirstRow($programSql);
            //if(empty($programInfo)){
            //    $updateFeed = "update product_feed set status = 'InActive',LastUpdateTime = '$nowDay' where id = {$Pvalue['ID']}";
            //    $objProgram->objMysql->query($updateFeed);
            //    $j++;
            //}else{
            //    if(!$Pvalue['Country']){
                    $updateFeed = "update product_feed set Country = '{$programInfo['ShippingCountry']}' where id = {$Pvalue['ID']}";
                    //echo $updateFeed.PHP_EOL;
                    $objProgram->objMysql->query($updateFeed);
                    $k++;
           //     }
               
           //}
        }
    }


}while(count($data)>0);
echo "Set $j product feed Inactive.\r\n";
echo "Set $k product feed country.\r\n";

echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;

?>
