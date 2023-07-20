FROM php:7
LABEL maintainer="dlsimple@gmail.com"
LABEL Description="PHP7 wework message audit"
LABEL version=1.0

WORKDIR /data/docker/php/extension

COPY php7-wxwork-finance-sdk /data/docker/php/extension
COPY C_sdk /data/docker/php/extension/C_sdk

RUN apt-get update -y
RUN apt-get install -y zip

## php-ext
RUN docker-php-ext-install pdo_mysql

## composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

RUN phpize
RUN ./configure --with-php-config=/usr/local/bin/php-config --with-wxwork-finance-sdk=/data/docker/php/extension/C_sdk
RUN make && make install
RUN echo "extension=wxwork_finance_sdk.so" >> /usr/local/etc/php/conf.d/wxwork-finance.ini