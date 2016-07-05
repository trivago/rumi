@ECHO OFF
SETLOCAL EnableDelayedExpansion

REM
REM rumi executor for Windows cmd and PowerShell.
REM

REM Current directory must be in Unix and not Windows style for docker.
SET CWD=%CD%

REM Replace back with forward slashes.
SET CWD=%CWD:\=/%

REM Remove the drive colon.
SET CWD=%CWD::=%

REM Lowercase the drive character, Unix file systems are case-sensitive.
SET CHAR_LIST="AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz"
SET FIRST=!CHAR_LIST:*%CWD:~0,1%=!
SET FIRST=!FIRST:~0,1!
SET CWD=%FIRST%%CWD:~1%

REM Add a slash to make it absolute.
SET CWD=/%CWD%

SET SOCK="/var/run/docker.sock"

docker run^
 --interactive^
 --rm^
 --tty^
 --volume=%CWD%:/workdir^
 --volume=%SOCK%:%SOCK%^
 --entrypoint /rumi/bin/entrypoint^
 trivago/rumi:stable %CWD% %*
