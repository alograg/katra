@ECHO OFF
ECHO Buscando tablas para %1...
DIR ..\sqlite\%1\*.sql /b >Tables.txt
ECHO Generadno tablas y datos...
ECHO ON
@FOR /F "delims=. tokens=1" %%f in (Tables.txt) DO sqlite %1.sq2 < ..\sqlite\%1\%%f.sql
@ECHO OFF