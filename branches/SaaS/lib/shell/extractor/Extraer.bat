@ECHO OFF
TITLE MySQLExtractor
CLS

IF NOT "%1"=="" (
    IF NOT "%2"=="" (
        GOTO makeDirs
    ) ELSE (
        GOTO errorNoDB
    )
) ELSE (
    GOTO errorNoServidor
)
GOTO END

:makeDirs
ECHO Creando directorios.
MKDIR temp
MKDIR %1\%2

ECHO Conectando a base de datos
IF NOT "%3"=="" (
    IF "%4"=="" (
        GOTO errorNoPass
    )
) ELSE (
    GOTO errorNoUsuario
)
ECHO Extrayendo tablas
.\bin\mysql -h %1 -D %2 -u %3 --password=%4 --character-sets-dir=utf8 --default-character-set=utf8 -X -e "SHOW TABLE STATUS" > .\%1\%2\talbas.xml

ECHO Extrayendo campos
IF EXIST .\%1\%2\talbas.xml (
    .\bin\msxsl .\%1\%2\talbas.xml .\translates\tablesToExtract.xsl -o .\temp\extractFields.bat -xw
) ELSE (
    GOTO END
)
call .\temp\extractFields.bat %1 %2 %3 %4

ECHO Tranformando campos
FOR /F "eol=; skip=1 tokens=1-2" %%f in (.\translates\translateTypes.txt) DO CALL transformar.bat %%f %%g .\%1\%2\
GOTO MOVEFILES

:errorNoServidor
ECHO Uso: Extraer SERVER DATABASE USER PASWORD
ECHO Error; no indico Host
GOTO errorNoDB

:errorNoDB
ECHO Error; no indico Base de Datos
GOTO errorNoUsuario

:errorNoUsuario
ECHO Error; no indico Usuario
GOTO errorNoPass

:errorNoPass
ECHO Error; no indico Password
GOTO END

:MOVEFILES
XCOPY %1\%2\*.* ..\..\..\testing\%1\%2\*.* /S/Y
RMDIR %1\ /S /Q
RMDIR temp /S /Q

:END
