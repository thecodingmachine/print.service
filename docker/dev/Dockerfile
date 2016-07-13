FROM php:7-apache

# installs missing php dependencies
RUN pecl install apcu-5.1.5 && \
    pecl install xdebug-2.4.0 # (dev or test environment only!)

RUN apt-get update && \
    apt-get install -y zlib1g-dev # dependency for php zip extension

RUN docker-php-ext-install zip

# enables Apache's rewrite module
RUN a2enmod rewrite

# installs pdf libraries
RUN apt-get install -y pdftk wkhtmltopdf

# installs nodejs
RUN apt-get install -y nodejs
