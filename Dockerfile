FROM php:7.3-fpm-alpine

WORKDIR /var/www/html
RUN addgroup -g 1000 -S appuser && adduser -u 1000 -S appuser -G appuser

RUN apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
	git \
	curl \
	libmcrypt-dev \
	libzip-dev \
	libpng-dev \
	libjpeg-turbo-dev \
	freetype-dev \
	tzdata \
    postgresql-dev

RUN pecl install mongodb && docker-php-ext-enable mongodb
RUN echo "extension=mongodb.so" >> /usr/local/etc/php/php.ini
RUN docker-php-ext-install pdo pdo_pgsql

COPY . .
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --no-scripts
RUN composer install --prefer-dist --no-interaction --no-scripts

RUN chown -R appuser:appuser storage bootstrap/cache
USER appuser

CMD ["sh", "-c", "php-fpm && nginx -g 'daemon off;'"]
EXPOSE 9000
