#!/bin/bash
yesterday=`date -d "-1day" +%Y-%m-%d`
megaFiles="/app/site/mcsky.mgsvr.com/web/app/nodejs/logs/mega_tracking/"${yesterday}"-*.json"
mlinkFiles="/app/site/mcsky.mgsvr.com/web/app/nodejs/logs/mlink_tracking/"${yesterday}"-*.json"
rm -f /app/site/mcsky.mgsvr.com/web/app/nodejs/logs/backup/*
mlinkPath="/app/site/mcsky.mgsvr.com/web/app/nodejs/logs/backup/mlink_tracking-"${yesterday}"bdg03.bwe.io.tar.gz"
megaPath="/app/site/mcsky.mgsvr.com/web/app/nodejs/logs/backup/mega_tracking-"${yesterday}"bdg03.bwe.io.tar.gz"
tar -czvPf ${megaPath} ${mlinkFiles}
tar -czvPf ${mlinkPath} ${megaFiles}
