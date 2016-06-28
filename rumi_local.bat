@echo off
docker run -ti -v %CD%:/workdir -v /var/run/docker.sock:/var/run/docker.sock --entrypoint /rumi/entrypoint_local rumi:dev %CD%
