@ECHO off
CLS
IF "%1"=="" goto:help
MKDIR docs\APIs
MKDIR docs\CSS
MKDIR docs\Databases
MKDIR docs\JS
MKDIR docs\PHP
MKDIR install\database\mysql\alter
MKDIR install\database\mysql\creation
MKDIR install\database\mysql\data\default
MKDIR install\database\sqlite\alter
MKDIR install\database\sqlite\creation
MKDIR install\database\sqlite\data\default
MKDIR install\installer\parts
MKDIR testing
MKDIR web\content
COPY lib\shell\files\build.xml build.xml
COPY lib\shell\files\index_template.php web\content\index_template.php
IF "%2"=="MVC" GOTO:do_mvc
:do_dal
MKDIR app\%1\tables
MKDIR app\%1\vo
MKDIR app\%1\implementatior\defautl
GOTO:end
:do_mvc
MKDIR app\%1\model
MKDIR app\%1\view
MKDIR app\%1\controller
GOTO:end
:help
ECHO.
ECHO Mekayotl Shell Tool
ECHO ___________________
ECHO.
ECHO Esta herramienta genera la estructura de archivos base para una nueva
ECHO aplicacion.
ECHO.
ECHO Uso:
ECHO mekayotl nombre [tipo]
ECHO.
ECHO Opciones:
ECHO     tipo    El tipo de aplicacion de que sera DAL o MVC. Default: DAL
:end
