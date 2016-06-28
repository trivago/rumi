@ECHO off
SETLOCAL EnableDelayedExpansion

REM Current directory must be in Unix style and not Windows style for docker.
SET PWD=%CD%

REM Replace back slashes with slashes.
SET PWD=%PWD:\=/%

REM Remove the drive colon.
SET PWD=%PWD::=%

REM Lowercase the drive character, Unix file systems are case-sensitive.
SET CHAR_LIST="AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz"
SET FIRST=!CHAR_LIST:*%PWD:~0,1%=!
SET FIRST=!FIRST:~0,1!
SET PWD=%FIRST%%PWD:~1%

REM Add a slash to make it absolute.
SET PWD=/%PWD%

docker run -ti --rm -v %PWD%:/workdir -v /var/run/docker.sock:/var/run/docker.sock --entrypoint /rumi/entrypoint_local trivago/rumi:stable %PWD%
