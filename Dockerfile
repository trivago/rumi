FROM    trivago/rumi:php-latest

RUN mkdir /rumi

ADD docker/bin /scripts

RUN apk add --update docker \
        && apk add --allow-untrusted /scripts/glibc-2.21-r2.apk /scripts/glibc-bin-2.21-r2.apk \
        && /usr/glibc/usr/bin/ldconfig /lib /usr/glibc/usr/lib \
        && echo 'hosts: files mdns4_minimal [NOTFOUND=return] dns mdns4' >> /etc/nsswitch.conf \
        && bash /scripts/Miniconda-latest-Linux-x86_64.sh -b -p /usr/local/miniconda \
        && rm /scripts/Miniconda-latest-Linux-x86_64.sh \
        && curl -L https://github.com/docker/compose/releases/download/1.7.0/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose \
        && chmod +x /usr/local/bin/docker-compose \
        && mkdir /workdir \
        && rm -rf /var/cache/apk/*

WORKDIR /rumi

ADD . /rumi

RUN composer install -o --no-dev

RUN echo "RUMI BUILD: $(git rev-parse HEAD)" > /rumi/BUILD_VERSION

WORKDIR /workdir

ENTRYPOINT ["/rumi/bin/entrypoint-ci"]
