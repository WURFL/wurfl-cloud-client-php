FROM ubuntu:14.04

ENV TERM xterm
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get install -y \
        curl \
        unzip \
        memcached \
        php5-cli \
        php5-curl \
        php5-apcu \
        php5-memcache \
        php5-memcached \
    && echo "apc.enable_cli=1" >> /etc/php5/mods-available/apcu.ini \
    && php5enmod php5-apcu \
    && php5enmod php5-memcache \
    && php5enmod php5-memcached \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
    && apt-get -qy autoremove \
    && apt-get clean \
    && rm -r /var/lib/apt/lists/*

COPY docker-init.sh /
VOLUME /code
WORKDIR /code
CMD ["/docker-init.sh"]
