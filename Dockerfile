FROM    trivago/rumi:php-latest

RUN mkdir /rumi && mkdir /workdir

WORKDIR /rumi

ADD . /rumi

RUN composer install -o --no-dev

RUN echo "RUMI BUILD: $(git rev-parse HEAD)" > /rumi/BUILD_VERSION

WORKDIR /workdir

ENTRYPOINT ["/rumi/bin/entrypoint-ci"]
