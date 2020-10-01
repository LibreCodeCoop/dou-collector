FROM php:7.4-fpm-alpine

RUN apk update

RUN apk add \
        autoconf \
        g++ \
        git \
        libzip-dev \
        make \
        unzip \
        && docker-php-ext-install zip

# Install xdebug
RUN pecl install xdebug-2.9.8 \
    && docker-php-ext-enable xdebug

RUN git clone https://github.com/nikic/php-ast.git \
    && cd php-ast \
    && phpize \
    && ./configure \
    && make install \
    && echo 'extension=ast.so' > /usr/local/etc/php/php.ini \
    && cd .. && rm -rf php-ast

RUN curl https://getcomposer.org/composer.phar --output /usr/bin/composer
RUN chmod +x /usr/bin/composer

WORKDIR /app

COPY entrypoint.sh /usr/bin/entrypoint.sh

ENTRYPOINT [ "sh", "/usr/bin/entrypoint.sh" ]