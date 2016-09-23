FROM php:7-apache

# installs missing php dependencies
RUN pecl install apcu-5.1.5 && \
    pecl install xdebug-2.4.0 # (dev environment only!)

ADD ext-xdebug.ini /usr/local/etc/php/conf.d/ext-xdebug.ini

RUN apt-get update && \
    apt-get install -y zlib1g-dev # dependency for php zip extension

RUN docker-php-ext-install zip

# enables Apache's rewrite module
RUN a2enmod rewrite

# s6-overlay
ADD https://github.com/just-containers/s6-overlay/releases/download/v1.11.0.1/s6-overlay-amd64.tar.gz /tmp/
RUN tar xzf /tmp/s6-overlay-amd64.tar.gz -C /

# installs nodejs and npm
RUN curl -sL https://deb.nodesource.com/setup_4.x | bash - && \
    apt-get update && \
    apt-get install -y nodejs && \
    ln -s /usr/bin/nodejs /usr/local/bin/node && \
    ln -s /usr/bin/npm /usr/local/bin/npm

# installs wkhtmltopdf
RUN apt-get update && \
    apt-get install -y wkhtmltopdf && \
    ln -s /usr/bin/wkhtmltopdf /usr/local/bin/wkhtmltopdf

# installs  pdftk
RUN apt-get update && \
    apt-get install -y pdftk && \
    ln -s /usr/bin/pdftk /usr/local/bin/pdftk

# installs libreoffice
RUN apt-get update && \
    apt-get install -y libreoffice && \
    ln -s /usr/bin/soffice /usr/local/bin/soffice

ENTRYPOINT ["/init"]
