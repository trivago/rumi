FROM    gliderlabs/alpine:latest

ADD bin/.gitconfig /root/.gitconfig

RUN apk add --update php7 php7-ctype php7-json php7-zlib php7-iconv php7-mbstring php7-phar php7-dom php7-openssl curl bash wget rsync git ca-certificates openssh && rm /var/cache/apk/*

WORKDIR /tmp

ADD bin /scripts

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php -- --install-dir=/usr/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && apk add --update docker \
    && apk add --allow-untrusted /scripts/glibc-2.21-r2.apk /scripts/glibc-bin-2.21-r2.apk \
    && /usr/glibc/usr/bin/ldconfig /lib /usr/glibc/usr/lib \
    && echo 'hosts: files mdns4_minimal [NOTFOUND=return] dns mdns4' >> /etc/nsswitch.conf \
    && bash /scripts/Miniconda-latest-Linux-x86_64.sh -b -p /usr/local/miniconda \
    && rm /scripts/Miniconda-latest-Linux-x86_64.sh \
    && curl -L https://github.com/docker/compose/releases/download/1.10.1/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose \
    && chmod +x /usr/local/bin/docker-compose \
    && rm -rf /var/cache/apk/*
