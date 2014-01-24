#!/bin/sh
#
# Genera las facturas y conceptos del mes.
#

DB_USER="root"
DB_PASSWORD="20Nort12Mysql"
NOW=$(date +"%Y%m%d")
SQL_FILE="$NOW.sql"
LOG_FILE="invoces$NOW.log"
LOG_PATH="../../../logs/"
mysql -u $DB_USER --password=$DB_PASSWORD -D develop_nort --character-sets-dir=utf8 --default-character-set=utf8 -X <./process/invoices/accountsToInvoice.sql>selectInvoice.xml
xsltproc --output $LOG_PATH$SQL_FILE ./process/invoices/toInvoices.xsl ./selectInvoice.xml
mysql -u $DB_USER --password=$DB_PASSWORD --database=develop_nort < $LOG_PATH$SQL_FILE -v -v -v >> $LOG_PATH$LOG_FILE
rm -f ./selectInvoice.xml
