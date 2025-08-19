FROM php:8.4-cli-alpine

RUN apk add --no-cache --update curl git build-base zlib-dev oniguruma-dev autoconf linux-headers bash rabbitmq-c-dev
RUN docker-php-ext-install sockets
RUN pecl install amqp \
    && docker-php-ext-enable amqp

# Если аргумент INSTALL_XDEBUG будет равен true, то устанавливаем xdebug (теперь можно исп. этот файл и для прод. деплоя)
ARG INSTALL_XDEBUG=false
# Подтягиваем зависимости для xdebug и включаем расширение xdebug в php.ini
RUN if [ ${INSTALL_XDEBUG} = true ];  \
    then \
      pecl install xdebug && docker-php-ext-enable xdebug; \
    fi;

# Пробрасываем xdebug.ini в контейнер php
COPY ./.deployment/docker/php-cli/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

## Configure non-root user
ARG PUID
ENV PUID $PUID

ARG PGID
ENV PGID $PGID

RUN apk --no-cache add shadow && \
    groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

# Source project
RUN mkdir /var/www/queue-practice \
    && chown www-data:www-data /var/www/queue-practice
COPY --chown=www-data:www-data ./ /var/www/queue-practice
WORKDIR /var/www/queue-practice

USER www-data

# Composer instalation
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

CMD ["tail", "-f", "/dev/null"]
