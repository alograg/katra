#!/bin/sh
#
# Copia los archivos y realiza los procesos necesarios para nueva instalacion
#
#
# Parametros;
# $1 = URL del API a donde se conecta. Ej. develop.nortcloud.com
# $2 = Nombre de la aplicacion
# $3 = SQL de creacion de la aplicacion
# $4 = Identificador de la suscription
# $5 = Nombre/Usuario de la cuenta
# $6 = Contraseña de la cuenta
# $7 = Identificador de la cuenta
#

clear

if [ $# -ne 7 ]
then
    echo $PWD
    echo "Usage - $0 api_url app_name db_version suscription_id account_name account_password"
    echo "  Where:"
    echo ""
    echo "      api_url             Nombre de HOST donde se encuentra el API de Nort"
    echo "      app_name            Nombre de la aplicacion a instalar"
    echo "      db_version          Nombre del archivos de creacion de tablas"
    echo "      suscription_id      Identificador de la suscripcion"
    echo "      account_name        Nombre de la cuenta"
    echo "      account_password    Contraseña de la cuenta"
    echo "      account_id          Identificador de la cuenta"
    echo ""
    echo "  Ej."
    echo "      $0 develop.nortcloud.com orbet create_2.0.0 4 demo Demo1"
    exit 1
fi

# Establece las constantes del script
SOURCE_DIR="/home/nortftp/www/develop.nortcloud.com/app/saas/instalations"
DB_USER="root"
DB_PASSWORD="20Nort12Mysql"
WWW_DIR="/home/nortftp/www"
HOST_NAME=".nortcloud.com"
BASE_CLIENT_DIR="$WWW_DIR/$5$HOST_NAME"
# Genera el sql de creacion de la base de datos
sed 's/{app}/'$2'/g
s/{user}/'$5'/g
' <$SOURCE_DIR/databases/create.sql >$SOURCE_DIR/databases/create_$5.sql
echo "Creacion debase de datos"
# Crea la base de datos
mysql -u $DB_USER --password=$DB_PASSWORD < $SOURCE_DIR/databases/create_$5.sql
# Elimina el script de la creacion de la base de datos
rm -f $SOURCE_DIR/databases/create_$5.sql
# Crea las tablas en la base de datos creada
echo "Creacion de tablas"
mysql -u $DB_USER --password=$DB_PASSWORD --database=$5_$2 < $SOURCE_DIR/databases/$2/mysql/creation_$3.sql
# Genera el script de asignacion de permisos
sed '
s/{app}/'$2'/g
s/{user}/'$5'/g
s/{password}/'$6'/g
' <$SOURCE_DIR/databases/privileges.sql >$SOURCE_DIR/databases/privileges_$4.sql
echo "Asignacion de perimsos"
# Asigna permisos al usuario
mysql -u $DB_USER --password=$DB_PASSWORD < $SOURCE_DIR/databases/privileges_$4.sql
# Elimina el script de asignacion de permisos
rm -f $SOURCE_DIR/databases/privileges_$4.sql
echo "Creando archivos y directorios"
# Crea el directorio la instalacion
mkdir -m 775 $BASE_CLIENT_DIR
BASE_CLIENT_DIR="$BASE_CLIENT_DIR/web"
mkdir -m 775 $BASE_CLIENT_DIR
BASE_CLIENT_DIR="$BASE_CLIENT_DIR/content"
mkdir -m 775 $BASE_CLIENT_DIR
INSTALATION_DIR="$BASE_CLIENT_DIR/$2"
mkdir -m 775 $INSTALATION_DIR
# Copia los archivos base
cp -pf $SOURCE_DIR/template/web/content/* $BASE_CLIENT_DIR
cp -pf $SOURCE_DIR/template/web/content/.* $BASE_CLIENT_DIR
# Copia los archivos de la aplicacion
yes | cp -rpf $SOURCE_DIR/template/web/content/$2/* $INSTALATION_DIR
yes | cp -pf $SOURCE_DIR/template/web/content/$2/.* $INSTALATION_DIR
echo "Configurando archivos"
# Configura el index base
sed '
s/{api}/'$1'/g
s/{app}/'$2'/g
s/{subscription}/'$4'/g
s/{account}/'$7'/g
s/{user}/'$5'/g
s/{password}/'$6'/g
s/die.*;//g
' -i $BASE_CLIENT_DIR/index.php
# Establece la configuracion de la aplicacion
sed '
s/{api}/'$1'/g
s/{app}/'$2'/g
s/{subscription}/'$4'/g
s/{account}/'$7'/g
s/{user}/'$5'/g
s/{password}/'$6'/g
s/die.*;//g
' -i $INSTALATION_DIR/index.php
chmod -R 775 $BASE_CLIENT_DIR
#chown -R 500:48 $BASE_CLIENT_DIR
exit 0
