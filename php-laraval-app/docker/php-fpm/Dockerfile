FROM php:8.1.18-fpm-alpine3.17 AS build

ARG PHP_VERSION=81
ENV DOCKER_BUILDKIT=1

RUN apk update \
    && apk upgrade \
    && apk add --no-cache autoconf=2.71-r1 build-base=0.5-r3 zlib-dev=1.2.13-r0 \
        libpng-dev=1.6.44-r0 libzip-dev=1.9.2-r2 oniguruma-dev=6.9.8-r0 \
        curl-dev=8.9.0-r0 libxml2-dev=2.10.4-r0 icu-dev=72.1-r1 composer=2.4.4-r0	\
        git=2.39.5-r0 php${PHP_VERSION}-gd=8.1.22-r0 php${PHP_VERSION}-tokenizer=8.1.22-r0 \
        php${PHP_VERSION}-xml=8.1.22-r0 php${PHP_VERSION}-xmlwriter=8.1.22-r0 \
        php${PHP_VERSION}-simplexml=8.1.22-r0 php${PHP_VERSION}-dom=8.1.22-r0 \
        php${PHP_VERSION}-fileinfo=8.1.22-r0 php${PHP_VERSION}-session=8.1.22-r0 \
        supervisor=4.2.4-r0 \
    # Install PHP-Redis 6.0.2 because 6.1.0 is broken
    && pecl install redis-6.0.2 \
    && docker-php-ext-install mysqli pdo pdo_mysql zip gd \
          mbstring curl xml bcmath intl fileinfo session \
    && docker-php-ext-enable redis

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .

RUN mv docker/php-fpm/entrypoint.sh entrypoint.sh \
    && mv docker/php-fpm/crontab crontab \
    && mv docker/php-fpm/sshd_config sshd_config \
    && mv docker/php-fpm/supervisord.conf supervisord.conf \
    && rm -rf docker

    # Remove it because it's not work with current laravel version
RUN composer remove roave/security-advisories --dev \ 
    && composer install --no-dev --optimize-autoloader

FROM php:8.1.18-fpm-alpine3.17 AS production

ARG PHP_VERSION=81
ENV APP_TYPE=app

RUN apk update \
    && apk upgrade \
    && apk add --no-cache autoconf=2.71-r1 build-base=0.5-r3 zlib-dev=1.2.13-r0 \
        libpng-dev=1.6.44-r0 libzip-dev=1.9.2-r2 oniguruma-dev=6.9.8-r0 \
        curl-dev=8.9.0-r0 libxml2-dev=2.10.4-r0 icu-dev=72.1-r1 composer=2.4.4-r0	\
        git=2.39.5-r0 php${PHP_VERSION}-gd=8.1.22-r0 php${PHP_VERSION}-tokenizer=8.1.22-r0 \
        php${PHP_VERSION}-xml=8.1.22-r0 php${PHP_VERSION}-xmlwriter=8.1.22-r0 \
        php${PHP_VERSION}-simplexml=8.1.22-r0 php${PHP_VERSION}-dom=8.1.22-r0 \
        php${PHP_VERSION}-fileinfo=8.1.22-r0 php${PHP_VERSION}-session=8.1.22-r0 \
        supervisor=4.2.4-r0 \
    # Install PHP-Redis 6.0.2 because 6.1.0 is broken
    && pecl install redis-6.0.2 \
    && docker-php-ext-install mysqli pdo pdo_mysql zip gd \
          mbstring curl xml bcmath intl fileinfo session \
    && docker-php-ext-enable redis \
    && apk del autoconf build-base

WORKDIR /var/www/html
COPY --from=build --chown=www-data:www-data /var/www/html .

RUN cat crontab >> /var/spool/cron/crontabs/root \
    && rm -f crontab

# Update max_execution_time to 300 seconds in php.ini
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
  && sed -i 's/^max_execution_time = .*/max_execution_time = 600/' "$PHP_INI_DIR/php.ini" \
  && sed -i 's/^memory_limit = .*/memory_limit = 512M/' "$PHP_INI_DIR/php.ini"

# Setup SSH
RUN apk add --no-cache openssh=9.1_p1-r6 \
  && mv sshd_config /etc/ssh/sshd_config \
  && echo 'root:Docker!' | chpasswd \
  && cd /etc/ssh/ \
  && ssh-keygen -A

# Setup Supervisor
RUN mkdir -p /etc/supervisor \
  && mv supervisord.conf /etc/supervisor/supervisord.conf

CMD [ "/bin/ash", "entrypoint.sh" ]

EXPOSE 9000 2222
