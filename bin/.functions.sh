#!/bin/sh

readonly __DIR__=$(cd -- $(dirname -- "${0}"); pwd)

#
# Login to Docker registry.
#
# Log in is only performed if username and password are set, the server
# is optional and defaults to the registry defined in the daemon.
#
# GLOBALS:
#  - $REGISTRY_USERNAME
#  - $REGISTRY_PASSWORD
#  - $REGISTRY_SERVER
#
login()
{
    if [ "${REGISTRY_USERNAME}" ] && [ "${REGISTRY_PASSWORD}" ]
    then
        printf 'Logging in to %s ...\n' "${REGISTRY_SERVER:-Docker Hub}"
        docker login \
            --username "${REGISTRY_USERNAME}" \
            --password "${REGISTRY_PASSWORD}" \
            "${REGISTRY_SERVER:-https://index.docker.io/v1/}"
    fi
}

#
# Print build version file content if present.
#
print_build_version()
{
    cat "${__DIR__}"/../BUILD_VERSION 2>&- || printf 'Build version unknown\n'
}

#
# Execute rumi with variable amount of arguments.
#
rumi()
{
    php "${__DIR__}"/rumi $*
}
