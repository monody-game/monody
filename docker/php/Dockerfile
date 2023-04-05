FROM php:fpm-alpine

COPY ./php.ini /usr/local/etc/php/php.ini
COPY ./opcache.ini /usr/local/etc/php/conf.d/opcache.ini

RUN apk update && apk add --no-cache wget imagemagick-dev autoconf build-base
RUN pecl install redis imagick && docker-php-ext-enable redis imagick
RUN docker-php-ext-install pdo pdo_mysql fileinfo exif opcache
RUN apk del autoconf build-base wget

RUN printf '%s%s' ";" "$(cat /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini)" > "/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
RUN printf '%s%s' ";" "$(cat /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini)" > "/usr/local/etc/php/conf.d/docker-php-ext-opcache.ini"

WORKDIR /var/www

EXPOSE 9000
