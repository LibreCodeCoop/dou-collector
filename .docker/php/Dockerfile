FROM php:7.4-fpm

RUN apt-get update

RUN apt-get install -y \
        git \
        libzip-dev \
        unzip \
        && docker-php-ext-install zip

# Install xdebug
RUN pecl install xdebug-2.9.0 \
    && docker-php-ext-enable xdebug

### BAIXA O COMPOSER 
RUN curl https://getcomposer.org/composer.phar --output /usr/bin/composer
# DÁ A PERMISSÃO PARA TODOS EXECUTAREM PARA TUDO QUE ESTÁ DENTRO DA PASTA COMPOSER
RUN chmod +x /usr/bin/composer

WORKDIR /app

## COPIA ARQUIVO entrypoint.sh PARA DIRETÓRIOS
COPY entrypoint.sh /usr/bin/entrypoint.sh

ENTRYPOINT [ "sh", "/usr/bin/entrypoint.sh" ]