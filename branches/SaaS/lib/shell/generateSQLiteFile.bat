@ECHO OFF
ECHO Buscando tablas para %1...
DIR ..\..\install\database\sqlite\%1\*.sql /b >Tables.txt
ECHO Generadno tablas y datos...
ECHO ON
@FOR /F "delims=. tokens=1" %%f in (Tables.txt) DO .\sqlite\sqlite %1.sq2 < ..\..\install\database\sqlite\%1\%%f.sql
REM @ECHO OFF