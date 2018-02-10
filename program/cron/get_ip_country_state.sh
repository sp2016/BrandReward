#!/bin/bash
echo "start load csv to database!"

mysql -u'bdg_go' -p'shY12Nbd8J' <<EOF
        use bdg_go_base
        delete from ip_country_state_v4;

        LOAD DATA LOCAL
        	INFILE '/home/bdg/logs/country_state_ip4/IP2LOCATION-LITE-DB3.CSV'
        INTO TABLE ip_country_state_v4
        FIELDS TERMINATED BY ','
        ENCLOSED BY '"'
        LINES TERMINATED BY '\r\n'
        IGNORE 0 LINES;

        delete from ip_country_state_v6;

        LOAD DATA LOCAL
        	INFILE '/home/bdg/logs/country_state_ip6/IP2LOCATION-LITE-DB5.CSV'
        INTO TABLE ip_country_state_v6
        FIELDS TERMINATED BY ','
        ENCLOSED BY '"'
        LINES TERMINATED BY '\r\n'
        IGNORE 0 LINES;

EOF
#php /home/bdg/program/cron/ip_2_redis.php
echo OK.