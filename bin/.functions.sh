#!/bin/sh

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
        printf 'Logging in to %s ...\n' "${REGISTRY_SERVER:-Docker}"
        docker login \
            --username "${REGISTRY_USERNAME}" \
            --password "${REGISTRY_PASSWORD}" \
            "${REGISTRY_SERVER-}"
    fi
}

#
# Print release file content if present.
#
release()
{
    cat "${CWD}"/../RELEASE 2>&- || printf 'No Release\n'
}

#
# Execute rumi with variable amount of arguments.
#
rumi()
{
    php "${CWD}"/rumi $*
}
