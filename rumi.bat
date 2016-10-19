@ECHO OFF
SETLOCAL EnableDelayedExpansion

REM
REM rumi executor for Windows cmd and PowerShell.
REM

SET CONF=
CALL :UNIX_PATH %USERPROFILE%/.docker/config.json CONF

SET CWD=
CALL :UNIX_PATH %CD% CWD

SET SOCK="/var/run/docker.sock"

docker pull trivago/rumi:stable

docker run^
 --interactive^
 --rm^
 --tty^
 --volume=%CWD%:/workdir^
 --volume=%SOCK%:%SOCK%^
 --volume=%CONF%:/root/.docker/config.json^
 --entrypoint /rumi/bin/entrypoint^
 trivago/rumi:stable %CWD% %*

GOTO :EOF

REM Paths must be in Unix and not in Windows style for docker to work. This
REM function translates a Windows path to a Unix path. Paths must be absolute
REM for this to work.
REM
REM ARGS:
REM  1 - The Windows path to translate.
REM  2 - Name of the variable to store the translated Unix path in.
:UNIX_PATH
SET TMP=%1%

REM Replace back with forward slashes.
SET TMP=%TMP:\=/%

REM Remove the drive colon.
SET TMP=%TMP::=%

REM Lowercase the drive character, Unix file systems are case-sensitive.
SET CHAR_LIST="AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz"
SET FIRST=!CHAR_LIST:*%TMP:~0,1%=!
SET FIRST=!FIRST:~0,1!
SET TMP=%FIRST%%TMP:~1%

REM Add a slash to make it absolute and return.
SET %~2=/%TMP%

GOTO :EOF
