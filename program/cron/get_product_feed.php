<?php
include_once(dirname(dirname(__FILE__)) . "/etc/const.php");
include_once(dirname(dirname(__FILE__)) . "/func/func.php");
$length = 2000;  

$objProgram = New ProgramDb();
$sql_names_set = 'SET NAMES latin1';
$objProgram->objMysql->query($sql_names_set);

$sql_names_set = 'SET NAMES latin1';
$objProgram->objPendingMysql->query($sql_names_set);




$sql = "select ID from wf_aff where IsActive = 'YES'";
$affArr = $objProgram->objMysql->getRows($sql);

$tables = array();
foreach ($affArr as $affv){
    $exist = $objProgram->objPendingMysql->isTableExisting('affiliate_product_'.$affv['ID']);
    if($exist){
        $tables[$affv['ID']] = 'affiliate_product_'.$affv['ID'];
    }
}

$nowDay = date('Y-m-d H:i:s',time());
echo "<< Start @ ".date("Y-m-d H:i:s")." >>\r\n";



foreach ($tables as $affid=>$table){
    
    $crawl_id = array(1,2,6,15,115,58,26,63,418,491,500,152,415,667,429,35,5,12,2032,2021,679,360,10,199,163,240,2024);
    if(!in_array($affid, $crawl_id)){
        continue;
    }
    $j = 0;
    //找出有效的program；
    $sql = "select ID,IdInAff from  program WHERE StatusInAff = 'Active' AND Partnership = 'Active' AND affid = $affid";
    $programArr = $objProgram->objMysql->getRows($sql);
    foreach ($programArr as $programInfo){
    
        $i = 0;
        do{
            $offset = $length*$i- 1 > 0 ? $length*$i- 1 : 0;
            $sql = "select * from `$table` where IsActive = 'YES' AND affid = $affid AND affmerchantid = '{$programInfo['IdInAff']}' AND ProductName != '' AND ProductPrice != '' AND ProductPrice > 0 AND ProductImage != '' AND ProductLocalImage !='' AND ProductUrl != '' group by ProductName limit $offset, $length";
            $data = $objProgram->objPendingMysql->getRows($sql);
        
            $i++;
            foreach ($data as $v){
                 
                /*if(!isset($programInfo[$v['AffMerchantId']])){
                    $programSql = "select a.ID from program a inner join program_intell b on a.id = b.programid where a.`AffId` = {$v['AffId']} AND a.`IdInAff` = '{$v['AffMerchantId']}' and b.isactive = 'active'";
                    $programInfo[$v['AffMerchantId']] = $objProgram->objMysql->getFirstRow($programSql);
                }
                if(empty($programInfo[$v['AffMerchantId']])) {
                    continue;
                }*/
        
        
                //找出这个product对应的store
                if(!isset($storeInfo[$programInfo['ID']])){
                    $storeSql = "select b.StoreId from r_domain_program a left join r_store_domain b on a.did = b.domainid where a.pid = {$programInfo['ID']}";
                    $storeInfo = $objProgram->objMysql->getFirstRow($storeSql);
                    if(!$storeInfo) continue;
                    $storeInfo[$programInfo['ID']] = $storeInfo['StoreId'];
                }
                 
                if(empty($storeInfo[$programInfo['ID']])){
                    continue;
                }
        
                if(!preg_match('/^http/',$v['ProductUrl'],$matches) ){
                    continue;
                }
                if(preg_match('/\d/',$v['ProductCurrency'])){
                    continue;
                }
                
        
                //查询是否有这条记录
                $selProductSql  = "select AffProductId from product_feed where `ProgramId` = {$programInfo['ID']} AND `AffProductId` = '".addslashes($v['AffProductId'])."' ";
                $productFeedInfo = $objProgram->objMysql->getFirstRow($selProductSql);
        
                $tmp_data = array(
                    'AffId' => $affid,
                    'ProgramId' => $programInfo['ID'],
                    'StoreId' => $storeInfo[$programInfo['ID']],
                    'AffProductId' => $v['AffProductId'],
                    'ProductName' => stripslashes($v['ProductName']),
                    'ProductUrl' => $v['ProductUrl'],
                    'ProductDestUrl' => $v['ProductDestUrl'],
                    'ProductDesc' => stripslashes($v['ProductDesc']),
                    'ProductImage' => $v['ProductImage'],
                    'ProductLocalImage' => $v['ProductLocalImage'],
                    'ProductPrice' => $v['ProductPrice'],
                    'ProductOriginalPrice' => $v['ProductOriginalPrice'],
                    'ProductRetailPrice' => $v['ProductRetailPrice'],
                    'ProductCurrency' => $v['ProductCurrency'],
                    'Commission' => $v['CommissionExt'],
                    'LastUpdateTime' => $nowDay,
                    'AddTime' => $nowDay,
                    'LastChangeTime' => $v['LastChangeTime'],
                    '`Status`' => 'Active', //Active InActive
                );
                $column_keys = array("AffId","ProgramId","StoreId","AffProductId", "ProductName", "ProductUrl", "`ProductDestUrl`", "ProductDesc","ProductImage","ProductLocalImage","ProductPrice","ProductOriginalPrice","ProductRetailPrice","ProductCurrency","Commission","`AddTime`","LastChangeTime", "`Status`");
        
                if(!$productFeedInfo){
                    $language = 'en';
                    if($v['Language']){
                        if($v['Language'] == 'be')
                            $language = 'nl';
                        if($v['Language'] == 'br')
                            $language = 'pt';
                        if($v['Language'] == 'uk')
                            $language = 'en';
                    }else{
                        if ($v['AffId'] == 360 || $v['AffId'] == 63 || $v['AffId'] == 65)
                            $language = 'de';
                        elseif($v['AffId'] == 2026){
                            $language = 'it';
                        }else{
                            $language = analyze_language($v['ProductName'].$v['ProductDesc']);
                        }
                    }
        
                    $column_keys[] = 'EncodeId';
                    $tmp_data['EncodeId'] = intval(getEncodeId());
                    $column_keys[] = '`language`';
                    $tmp_data['`language`'] = $language;
                }
        
                foreach ($tmp_data as $tk=>$tv){
                    if($tk != 'LastUpdateTime')
                        $tmp_insert[] = addslashes($tv);
                    if($tk != 'AddTime')
                        $tmp_update[] = "$tk = '".addslashes($tv)."'";
                }
        
                $insertSql = "INSERT INTO product_feed (".implode(",", $column_keys).") VALUES ('".implode("','", $tmp_insert)."') ON DUPLICATE KEY UPDATE " . implode(",", $tmp_update) . ";";
                //echo $insertSql.PHP_EOL;
                $objProgram->objMysql->query($insertSql);
        
                $j++;
                unset($tmp_insert);
                unset($tmp_update);
            }
             
        }while(count($data)>0);
        
    }
    
    echo "$table Total count:".$j.PHP_EOL;
    
}







//check not update
$sql = "select count(*) from product_feed where LastUpdateTime < '$nowDay' and status = 'active'";
$cnt = $objProgram->objMysql->getFirstRowColumn($sql);
$sql = "update product_feed set status = 'InActive' where LastUpdateTime < '$nowDay' and status = 'active'";
$objProgram->objMysql->query($sql);
echo "Set $cnt Inactive.\r\n";
 


$i = 0;
$key = substr(strtotime(" - " . date("s") . "days"), -5);
while(1){
	$i++;
	$sql = "select id from product_feed where encodeid = 0 limit 100";
	$tmp_arr = $objProgram->objMysql->getRows($sql);
	if(!count($tmp_arr)) break;
	
	foreach($tmp_arr as $vne){
		$encodeid = intval(getEncodeId());
		if($encodeid){
			$sql = "update product_feed set encodeid = $encodeid where id = {$vne['id']}";
			$objProgram->objMysql->query($sql);
		}
	}
	
	if($i > 10000){
		echo 'warning: 10000 ';
		exit;
	}
}

//检查ProductCurrencySymbol为空的，匹配下CurrencySymbol.
$sql = "SELECT ID,ProductCurrency FROM product_feed WHERE ProductCurrencySymbol = '' AND `status` = 'active'";
$ProductCurrencyInfo = $objProgram->objMysql->getRows($sql);
foreach ($ProductCurrencyInfo as $CurrencyInfoValue){

    $sql = "SELECT Symbol FROM currency_contrast WHERE `code` = '{$CurrencyInfoValue['ProductCurrency']}'";
    $symbol = $objProgram->objMysql->getFirstRow($sql);
    if($symbol){
        $updatesql = "update product_feed set ProductCurrencySymbol = '{$symbol['Symbol']}' where id = {$CurrencyInfoValue['ID']}";
        $objProgram->objMysql->query($updatesql);
    }
}


echo "<< End @ ".date("Y-m-d H:i:s")." >>\r\n";
exit;


function analyze_language($str){

    $language = 'en';
    $strUnicode =  utf8_unicode($str);
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $strUnicode, $matches);
    $isEnglish = true;
    if (!empty($matches))
    {
        for ($j = 0; $j < count($matches[0]); $j++)
        {
            //echo   base_convert($matches[0][$j], 16, 10).'<br/>';
            $strUnicodeToten = base_convert($matches[0][$j], 16, 10);
            if($strUnicodeToten<0 || $strUnicodeToten>127){
                $isEnglish = false;
                break;
            }
        }
    }
    if(!$isEnglish){
        $language = analyze_language_byKeywords($str);
        if($language == 'en'){
            //根据域名后缀关键字
            $language = analyze_language_byDomain($str);
        }
    }
    return $language;
}


function analyze_language_byKeywords($str){
    
    $language = 'en';
    $tempLanguage = array();
    $fr_keywords = array("Réduction","Réductions","Privé","Privée","Privées","Jusqu'à","Remise","Remises","Livraison","Gratuit","Gratuite","Gratuites","Expédition","Solde","Soldes","Expédié","Expédiés","Expédiées","Expédier","Dès","Livraison","Livraisons","Moins de","Démarque","Démarques","Frais","Cadeau","Sur votre","Bon plan","Sans frais","votre","vôtre","sur une","sélection","d'articles","d'article","valeur","à partir","commande","Obtenez","Fidé
lité","Fidélités","Récompensée","Récompense","Rabais","Gagnez","Gagner","Supplémentaire","Découvrez","Au lieu","avec","avant","le code","prix","Bénéficiez","Bénéficier","Bénéficié","Profitez","Bienvenue","meilleur","à partir","réservez","réservé","Nouveaux","Nouveau","Nouvelles","Nouvelle","d'achat","Départ","Utilisez","Utilisé","Dernière","commandes","spécial","exceptionnel","cadeau","arrivée","arrivé","économisez","économisé","achetez","acheté","de plus",
        "personnes","personne","première","de remise","de rabais","réduc","prévente","après","le coupon","du coupon","des coupons","de coupons","les coupons","jouets","jouet","modèle","sur les","sur le","valable","Précommande","à petit","à",
        "bon","traiter","offres","pièces justificatives","code de réduction","codes de réduction","remise","coupon de réduction","économie","vente","Ventes","les","livraison gratuite","livraison","seulement","prendre","avoir","vacances","du quotidien",
        "jusqu'à","dépensez","Cadeaux"
    );
    
    $de_keywords = array('Gutschein','Sparen','Rabatt','Aktion','Saleangebot','Angebot','Sortiment','Schnäppchen','Nachlass','jetzt','Frühbucher','Buchen','Buchung','Lieferung','Frühling','Sommer','Herbst','Reduktion','Reduziert','Bestellung','Bestellungen','Versand','Bestellwert','Mindestbestellwert','Anmeldung','kostenlos','Rabattiert','Gutscheincode','Erhalten','Warenkorb','Skonto','gültig','Kunde','Für','Herren','Damen','versandkostenfrei','versandkosten','Artikel','günstig','Prozent','Überweisung','exklusiv','Muttertag','Valentinstag','Attraktiv','weihnachten',
        'Gutschein','Angebote','Gutscheine','Gutscheincode','Gutscheincodes','Rabatte','Rabattcode','Rabattcodes','speichern','Ersparnisse','Verkauf','Der Umsatz','das','Angebote','Kostenloser Versand','Versand','nur','nehmen','haben','Urlaub','Täglich','Geschenkset','KOSTENLOSEN',
        
    );
    

    $ru_keywords = array('Б','б','Г','г','Д','д','Ё','ё','Ж','ж','З','з','И','и','Й','й','Л','л','Ц','ц','Ч','ч','Ш','ш','Щ','щ','Ъ','ъ','Ы','ы','Ь','ь','Э','э','Ю','ю','Я','я',
        'ваучер','купон','по рукам','предложения','купоны','ваучеры','код купона','коды купонов','код ваучера','коды ваучера','скидка','скидки','код скидки','скидочные коды','купон на скидку','экономия','продажа','продажи','бесплатная доставка','Перевозка','только','принимать','иметь','день отдыха','ежедневно',
        'vremeni'
    );

    $it_keywords = array("Riduzione", "sconti", "sconto", "consegna", "Privato","spedizione","Vendite", "inviato", "Spedito", "avanti", "consegna","ribassi","fresco","regalo","sui tuoi","Consigli","tuo","selezione","articoli","voce","valore","ordine","Fidelity","lealtà","Onorato","Agon","sconto","Vincere","invece", "avanti","theCode", "prezzo", "beneficiato", "Benvenuto",
        "migliore","apartir","libro","riservato","Nuov","acquisto","usato","ultimo","comando","speciale","unico","dono","arrivo","vieni","salvare","salvato","acquistare","acquisti","persone", "primo", "deremise", "derabais", "Prevendita", "dopo", "buoni", "giocattoli", "giocattolo", "stile", "valido", "piccolo","tagliando","buono","affare","offerte","tagliandi","codici promozionali","codice promozionale","codici voucher","codice di sconto","Codici Sconto","buono sconto","risparmi","saldi","Speciali","spedizione gratuita","spedizione","prendere","avere","vacanza","quotidiano",
        "Spese di"
    );
    
    $es_keywords = array("Reducción", "descuentos","descuento", "Libre","envío", "Equilibrio", "Ventas", "Enviado", "Adelante", "menos", "rebajas","fresco","regalo","en sus","Consejos","suyo","selección","artículos","elemento","orden","fidelidad","lealtad","Recompensa","Ganar", "en vez",  "adelante", "código", "precio", "beneficio", "disfrutar", "bienvenida", "mejor", "desde", "libro", "reservado", "nuevo",
        "nuevo", "noticias", "nuevo", "comenzar", "usar", "usado","Última", "especial", "único", "regalo", "llegada", "ven", "salvar", "salvado", "comprar", "compra", "más","personas", "descuento","avance","después de","cupón","cupón","cupones","cupones","cupones","juguetes","juguete","estilo","en la el","en el","válido","Pre-orden","pequeño","comprobante","acuerdo","ofertas","comprobantes",
        "Código promocional","códigos de cupones","Código de cupón","descontar","descuentos","código","ahorro","ahorros","venta","el","especiales","solamente","tomar","tener","fiesta","diario",
        
    );
    
    $pt_keywords = array("Redução", "descontos","desconto","Vendas", "Encaminhar", "Encolher", "descontos", "presente", "no seu", "Conselho", "Toll", "seu",  "seleção", "itens", "ordem", "Obter", "lealdade","Recompensar","Ganhar","Descobrir","em vez","adiante","preço","benefício","usar","usado","último","controle","especial","exclusivo","presente","chegada", "salvar","mais", "pessoas", "pessoa", "primeiro", "desconto", "duque", "adiantamento", "após", "cupão", "cupons", "brinquedos", "brinquedo", "estilo", "Pré-encomenda",
        "pequeno","comprovante","cupom","Código","desconto","salvando","poupança","venda","especiais","frete grátis","Remessa","só","levar","ter","feriado","diariamente"
        );
    
    $nl_keywords = array("Korting","Levering", "Privé", "Verzending", "Verkoop", "Verzonden", "Doorsturen", "Levering", "Korting", "vers","op uw","Advies","selectie","artikelen", "waarde","volgorde","Trouw","loyaliteit","korting","in plaats daarvan","vooruit","prijs","voordeel","Welkom","apartir","boek","gereserveerd","Nieuwe","aankoop","Gebruik", "gebruikt", "laatste","opdracht","speciaal","alleen","aankomst","kom","opslaan",
        "opgeslagen","kopen","mensen","eerste", "Voorverkoop", "kortingsbonnen", "speelgoed", "speelgoed", "stijl", "geldig", "aanbiedingen","waardebonnen","korting","besparing","spaargeld","verkoop","het","geen","Verzenden","enkel en alleen","nemen","hebben","vakantie","dagelijks"
    );
    
    $se_keywords = array("Rabatt","Frakt", "Försäljning","Skickat", "Framåt", "Från", "Leverans", "Rabatt","färsk","gåva","på din","Råd","artiklar","objekt","värde","från","i stället","framåt", "sista","kommandot","gåva","ankomst","kom","spara","sparat","köp","människor","först","deremise","derabais", 
        "kuponger", "leksaker", "leksak", "giltiga","handla","erbjudanden","kupongerna","kuponger","kupongskod","kupongkoder","rabattkod","sparande","besparingar","försäljning","specialare","endast","helgdag","dagligen"
        
    );
    
    $ukr_language = array("Зниження", "Знижка", "Знижка", "Доставка", "Приватна", "Доставка", "Продаж", "Відправлені", "Відвантажені", "Вперед", "Доставка", "Знижка", "подарунок","статтях" , "від", "замовлення", "вірність", "лояльність",  "Вибрати","замість","вперед","код","ціну","вигоду","Ласкаво просимо ","найкраще","книга","зарезервована",
        "Нова покупка", "Використовувати","останній","команда","спеціальний","тільки","подарунок","прибуття","прийти","зберегти","зберегти","купити","купити","деплюс","люди","людина","перша" , "доработа", "Авансовий продаж", "після", "купони", "іграшки", "іграшка", "стиль", "вгору", "дійсна", "мала","угода","угоди","ваучери","коди купонів","знижки",
        "код на знижку","коди знижки","дисконтний купон","економія","заощадження","продаж","мати","свято","щодня"
    );

    /*$po_language = array("Redukcja", "tanio","Prywatna","Sprzedaż", "Wysłane", "Wysyłka", "Przekaż", "Dostawa", "świeży","Doradztwo","twój","wybór","artykuły","przedmiot","wartość","zamówienie","Wierność","lojalność","zniżka", "Wygraj","zamiast","do przodu"," Kod "," cena ","korzyści","Witamy","najlepsze","apartir","książka","zarezerwowane","Nowy","zakup", "Użyj",
        "użyte",  "ostatnie","polecenie","specjalne","przyjazd","zapisz","zapisane","ludzie","osoba","pierwszy","deremise" , "derabais","talon","sprawa","oferty","kupony","kody kuponów","kod rabatowy","kupon kody","zniżka","rabaty","zniżka","kody promocyjne","oszczędność","obroty","promocje","Darmowa","Wysyłka","brać","mieć","święto","codziennie"
    );*/
    
    $jp_language = array("割引","配達","プライベート","出荷","販売","送信","転送","出庫","配達","あなたの","選択","記事 ","アイテム","値","忠実","忠誠 ","オノラト","歓迎","顺序 ","予約 ","新規","購入","最後","コマンド","のみ","贈り物","到着する","来る","保存する","デプラス","最初に","脱remise", "デパート","前売り","後","クーポン","おもちゃ","おもちゃ","スタイル","対処","お得","クーポン",
        "ク","ウチ","貯蓄","節約","削減","送料無料","運送","取る","持ってる","休日",
    );
    
    /*$dk_language = array("Rabat", "Levering", "Fragt", "Salg", "Sendt", "Fragt", "Fremad", "Fra", "Levering", "frisk","på din","Rådgivning","Valg","Artikler","Værdi","Loyalitet","i stedet","Velkommen","bedste","apartir","køb", "Brug","brugt","deremise", "derabais", "kuponer", "legetøj", "legetøj","gyldige","rabatkupon","kuponrente","tilbud","kuponer",
        "besparelse","opsparing","gratis fragt","Forsendelse","ferie","daglige"
        
    );*/
    
    
    
    /*if(preg_match('/\$/is',$str)){
        return $language;
    }*/

    foreach($fr_keywords as $v){
        if(preg_match('/\s+'.$v.'\s+/is',$str,$matches)){
            //$language = 'fr';
            //return $language;
            $tempLanguage['fr'][] = $matches[0];
        }
    }

    foreach($de_keywords as $v){
        if(preg_match('/\s+'.$v.'\s+/is',$str,$matches)){
            //$language = 'de';
            //return $language;
            $tempLanguage['de'][] = $matches[0];
        }
    }

    foreach($ru_keywords as $v){
    
        if(preg_match('/'.$v.'/is',$str,$matches) || preg_match('/^'.$v.'/is',$str,$matches) || preg_match('/'.$v.'$/is',$str,$matches)){
            //$language = 'ru';
            //return $language;
            $tempLanguage['ru'][] = $matches[0];
        }
    }
    
    foreach($it_keywords as $v){
        if(preg_match('/\s+'.$v.'\s+/is',$str,$matches)){
            //$language = 'it';
            //return $language;
            $tempLanguage['it'][] = $matches[0];
        }
    }
    
    foreach($es_keywords as $v){
        if(preg_match('/\s+'.$v.'\s+/is',$str,$matches)){
            //$language = 'es';
            //return $language;
            $tempLanguage['es'][] = $matches[0];
        }
    }
    
    foreach($pt_keywords as $v){
        if(preg_match('/\s+'.$v.'\s+/is',$str,$matches)){
            //$language = 'pt';
            //return $language;
            $tempLanguage['pt'][] = $matches[0];
        }
    }
    
    foreach($nl_keywords as $v){
        if(preg_match('/\s+'.$v.'\s+/is',$str,$matches)){
            //$language = 'nl';
            //return $language;
            $tempLanguage['nl'][] = $matches[0];
        }
    }
    
    foreach($se_keywords as $v){
        if(preg_match('/\s+'.$v.'\s+/is',$str,$matches)){
            //$language = 'se';
            //return $language;
            $tempLanguage['se'][] = $matches[0];
        }
    }
    
    foreach($ukr_language as $v){
        if(preg_match('/\s+'.$v.'\s+/is',$str,$matches)){
            //$language = 'ukr';
            //return $language;
            $tempLanguage['ukr'][] = $matches[0];
        }
    }
    
    
   /* foreach($po_language as $v){
        if(preg_match('/\b'.$v.'\b/is',$str,$matches)){
            $language = 'po';
            return $language;
        }
    }*/
    
    foreach($jp_language as $v){
        if(preg_match('/\s+'.$v.'\s+/is',$str,$matches)){
            //$language = 'jp';
            //return $language;
            $tempLanguage['jp'][] = $matches[0];
        }
    }
    
   /* foreach($dk_language as $v){
        if(preg_match('/\b'.$v.'\b/is',$str,$matches)){
            $language = 'dk';               
            return $language;
        }
    }
    */
    if(count($tempLanguage) > 0){
        $tempArrCount = array_map('count',$tempLanguage);
        arsort($tempArrCount);
        $tempArrCountKey = array_keys($tempArrCount);
        $language = $tempArrCountKey[0];
    }
    
    return $language;
}

function analyze_language_byDomain($str){
    $language = 'en';
    $keywords = array(
        'fr'=>array('\.fr','fr\.')
    );
    $keywords += array(
        'de'=>array('\.de'.'de\.')
    );
    $keywords += array(
        'ru'=>array('\.ru','ru\.')
    );
    $keywords += array(
        'it'=>array('\.it')
    );
    $keywords += array(
        'es'=>array('\.es','es\.')
    );
    $keywords += array(
        'pt'=>array('\.pt','pt\.')
    );
    $keywords += array(
        'nl'=>array('\.nl','nl\.')
    );
    $keywords += array(
        'se'=>array('\.se','se\.')
    );
    $keywords += array(
        'ukr'=>array('\.ua','ua\.')
    );
    $keywords += array(
        'jp'=>array('\.jp','jp\.')
    );

    foreach($keywords as $contry=>$domainSuffix){
        foreach ($domainSuffix as $domainCountry)
        {
            if(preg_match('/\b'.$domainCountry.'\b/',$str,$matches)){
                $language = $contry;
                return $language;
            }
        }
    }

    return $language;
}


function utf8_unicode($name){
    $name = @iconv('UTF-8', 'UCS-4', $name);
    //print_r($name);
    $len  = strlen($name);
    $str  = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2){
        $c  = $name[$i];
        $c2 = $name[$i + 1];
        //echo '<br/>'.$c.'--'.$c2.'<br/>';
        if (ord($c) > 0){   //两个字节的文字
            $str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
            //$str .= base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
        } else {
            $str .= '\u'.str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
            //$str .= str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
        }
    }
    $str = strtoupper($str);//转换为大写
    return $str;
}


?>
