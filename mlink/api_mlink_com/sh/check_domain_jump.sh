#/bin/bash

#to make program check file in path : /app/site/mgsvc.com/api/data/jumpchk
php /app/site/mgsvc.com/api/index.php -act=jump.make_program_aff_url_file > /app/site/mgsvc.com/api/data/jumpchk/task.log

# 10 process to do the job
php /app/site/mgsvc.com/api/index.php -act=jump.check_program_aff_url -cpu=0 > /app/site/mgsvc.com/api/data/jumpchk/cpu.0.log  2>&1 &
php /app/site/mgsvc.com/api/index.php -act=jump.check_program_aff_url -cpu=1 > /app/site/mgsvc.com/api/data/jumpchk/cpu.1.log  2>&1 &
php /app/site/mgsvc.com/api/index.php -act=jump.check_program_aff_url -cpu=2 > /app/site/mgsvc.com/api/data/jumpchk/cpu.2.log  2>&1 &
php /app/site/mgsvc.com/api/index.php -act=jump.check_program_aff_url -cpu=3 > /app/site/mgsvc.com/api/data/jumpchk/cpu.3.log  2>&1 &
php /app/site/mgsvc.com/api/index.php -act=jump.check_program_aff_url -cpu=4 > /app/site/mgsvc.com/api/data/jumpchk/cpu.4.log  2>&1 &
php /app/site/mgsvc.com/api/index.php -act=jump.check_program_aff_url -cpu=5 > /app/site/mgsvc.com/api/data/jumpchk/cpu.5.log  2>&1 &
php /app/site/mgsvc.com/api/index.php -act=jump.check_program_aff_url -cpu=6 > /app/site/mgsvc.com/api/data/jumpchk/cpu.6.log  2>&1 &
php /app/site/mgsvc.com/api/index.php -act=jump.check_program_aff_url -cpu=7 > /app/site/mgsvc.com/api/data/jumpchk/cpu.7.log  2>&1 &
php /app/site/mgsvc.com/api/index.php -act=jump.check_program_aff_url -cpu=8 > /app/site/mgsvc.com/api/data/jumpchk/cpu.8.log  2>&1 &
php /app/site/mgsvc.com/api/index.php -act=jump.check_program_aff_url -cpu=9 > /app/site/mgsvc.com/api/data/jumpchk/cpu.9.log  2>&1 &
