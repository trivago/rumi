FROM    trivago/rumi-php:latest

ADD     .   /rumi

ADD docker /scripts

RUN ls -l scripts && apk add --update docker && \
        apk add --allow-untrusted /scripts/glibc-2.21-r2.apk /scripts/glibc-bin-2.21-r2.apk && \
        /usr/glibc/usr/bin/ldconfig /lib /usr/glibc/usr/lib && \
        echo 'hosts: files mdns4_minimal [NOTFOUND=return] dns mdns4' >> /etc/nsswitch.conf && \
        bash /scripts/Miniconda-latest-Linux-x86_64.sh -b -p /usr/local/miniconda && \
        rm /scripts/Miniconda-latest-Linux-x86_64.sh \
        && curl -L https://github.com/docker/compose/releases/download/1.5.2/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose \
        && chmod +x /usr/local/bin/docker-compose \
        && mkdir /workdir \
        && cd /rumi && curl -sS http://getcomposer.org/installer | php -- --filename=composer && php composer install -o --no-dev && rm composer \
        && apk del wget ca-certificates curl php-openssl php-phar \
        && rm /var/cache/apk/*


ARG CONT_IMG_BUILD
RUN echo "CI RUNNER BUILD: $CONT_IMG_BUILD" > /rumi/RELEASE

WORKDIR /workdir

ENTRYPOINT ["/rumi/entrypoint"]