#!/bin/bash
echo "start"
curl http://download.ip2location.com/lite/IP2LOCATION-LITE-DB1.CSV.ZIP -o /home/bdg/logs/ip4.zip
curl http://download.ip2location.com/lite/IP2LOCATION-LITE-DB1.IPV6.CSV.ZIP -o /home/bdg/logs/ip6.zip
echo "download succ"
unzip -o -d /home/bdg/logs/ip4 /home/bdg/logs/ip4.zip
unzip -o -d /home/bdg/logs/ip6 /home/bdg/logs/ip6.zip
echo "unzip succ"
mysql -ubdg_go -pshY12Nbd8J <<EOF
        use bdg_go_base
        delete from ip_country_v4;
        
        LOAD DATA LOCAL
                INFILE '/home/bdg/logs/ip4/IP2LOCATION-LITE-DB1.CSV'
        INTO TABLE
        	ip_country_v4
        FIELDS TERMINATED BY ','
        ENCLOSED BY '"'
        LINES TERMINATED BY '\r\n'
        IGNORE 0 LINES;  
        
        delete from ip_country_v6;
        
        LOAD DATA LOCAL
        	INFILE '/home/bdg/logs/ip6/IP2LOCATION-LITE-DB1.IPV6.CSV'
        INTO TABLE
        	ip_country_v6
        FIELDS TERMINATED BY ','
        ENCLOSED BY '"'
        LINES TERMINATED BY '\r\n'
        IGNORE 0 LINES;
EOF
php /home/bdg/program/cron/ip_2_redis.php
echo OK.