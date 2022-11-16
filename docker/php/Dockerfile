FROM php:fpm

ENV PHP_SECURITY_CHECHER_VERSION=1.0.0

RUN apt-get update && apt-get install -y wget git
RUN apt-get update && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev libzip-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
 	&& docker-php-ext-install -j$(nproc) gd
RUN pecl install redis && docker-php-ext-enable redis
RUN docker-php-ext-install pdo opcache pdo_mysql fileinfo zip intl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
RUN curl -L https://github.com/fabpot/local-php-security-checker/releases/download/v${PHP_SECURITY_CHECHER_VERSION}/local-php-security-checker_${PHP_SECURITY_CHECHER_VERSION}_linux_$(dpkg --print-architecture) --output /usr/local/bin/local-php-security-checker &&   chmod +x /usr/local/bin/local-php-security-checker

RUN printf '%s%s' ";" "$(cat /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini)" > "/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"

WORKDIR /var/www

EXPOSE 9000
