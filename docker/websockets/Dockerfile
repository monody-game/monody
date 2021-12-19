FROM grafikart/php:1.0.4

# On désactive xdebug
RUN printf '%s%s' ";" "$(cat /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini)" > "/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"

WORKDIR /var/www

EXPOSE 6001

CMD ["php", "artisan", "websockets:serve"]
