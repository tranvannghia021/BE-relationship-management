# Setup 
- Clone run shell "git clone -b dev curl"
- Coppy environment run shell "cp .env.dev .env"
- Enable docker end start with shell under
- Install package laravel run shell "composer i"
- Init table in command laravel run shell "php artisan migrate:refresh"
# Docker
- Run shell "docker-compose up -d && docker exec -it workplace bash || docker exec -it workplace /bin/sh"
- Restart run shell "docker-compose restart"
- Stop run shell "docker-compose down"
# Postgres gui
- Http://localhost:82/browser/
- Username:root@admin.com
- Password:12345678
# Mongodb gui
- Http://localhost:8081
