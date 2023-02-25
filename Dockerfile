FROM php:8.1.16-fpm-alpine

WORKDIR /var/www/html
RUN addgroup -g 1000 -S appuser && adduser -u 1000 -S appuser -G appuser

RUN apk add --no-cache \
	git \
	curl \
	libmcrypt-dev \
	libzip-dev \
	libpng-dev \
	libjpeg-turbo-dev \
	freetype-dev \
	tzdata

RUN docker-php-ext-install pdo_mysql zip gd
COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --no-scripts
RUN composer install --prefer-dist --no-interaction --no-scripts

RUN chown -R appuser:appuser storage bootstrap/cache
USER appuser

CMD ["sh", "-c", "php-fpm && nginx -g 'daemon off;'"]
EXPOSE 9000
