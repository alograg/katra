@ECHO OFF
CLS
SET CREATION_SQL=install\database\mysql\creation\
REM ECHO SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;>creation.sql
REM ECHO SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;>>creation.sql
REM ECHO SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';>>creation.sql
FOR %%J IN (%CREATION_SQL%*.sql) DO TYPE %%J >>creation.sql
REM ECHO SET SQL_MODE=@OLD_SQL_MODE; >>creation.sql
REM ECHO SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS; >>creation.sql
REM ECHO SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS; >>creation.sql
FINDSTR 'v[1234567890] creation.sql > versiones.txt
ECHO 0.0.0 >solo_numero_version.txt
FOR /F "tokens=4,5,6 delims=v. " %%i IN (versiones.txt) DO ECHO %%i.%%j.%%k >>solo_numero_version.txt
SORT solo_numero_version.txt > maxima.txt
FOR /F %%i IN (maxima.txt) DO SET MAX_SQL_VERSION=%%i
ECHO Database v%MAX_SQL_VERSION%
MOVE creation.sql %CREATION_SQL%..\creation_%MAX_SQL_VERSION%.sql
DEL /Q *.txt
