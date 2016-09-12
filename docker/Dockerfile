FROM php:7-apache

# installs missing php dependencies
RUN pecl install apcu-5.1.5 && \
    pecl install xdebug-2.4.0 # (dev environment only!)

ADD ext-xdebug.ini /usr/local/etc/php/conf.d/ext-xdebug.ini

RUN apt-get update && \
    apt-get install -y zlib1g-dev # dependency for php zip extension

RUN docker-php-ext-install pdo_mysql && \
    docker-php-ext-install zip

# enables Apache's rewrite module
RUN a2enmod rewrite

# s6-overlay
ADD https://github.com/just-containers/s6-overlay/releases/download/v1.11.0.1/s6-overlay-amd64.tar.gz /tmp/
RUN tar xzf /tmp/s6-overlay-amd64.tar.gz -C /

ENTRYPOINT ["/init"]
