#! /bin/bash

php artisan config:clear
php artisan queue:restart
php artisan storage:link
php artisan queue:work  --queue=send_link_forgot_pass,send_email --sleep=3 --tries=3 --timeout=9000 --daemon > storage/logs/queue 2>&1 & 