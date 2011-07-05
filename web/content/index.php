<?php
/**
 * Achivo que es llamado por el browser.
 *
 * Este archivo es el que se procesa antes que cualquiera
 * Solicitado principalmente por el WebServer (Apache|IIS)
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua Interactive}
 * @package	Mekayotl.public
 * @since	2011-03-01
 * @subpackage	core
 * @version	$Id$
 */

/**#@+
 * Constants
 */
/**
 * Define el path del directorio publico.
 * @name	PUBLIC_PATH
 */
defined('PUBLIC_PATH') ||
  define('PUBLIC_PATH', realpath(dirname(__FILE__)));
/**
 * Define el estado de la aplicacion
 * @name	PRODUCTION_SITE
 */
defined('PRODUCTION_SITE') ||
  define('PRODUCTION_SITE', false);
/**#@-*/
/**
 * Pone a dispocion el Framework Mekayotl
 */
require_once '../../lib/php/mekayotl/core.php';
/**
 * La configuracion que se esta ejecutando
 * @name	$config
 * @global	array	$GLOBALS['config']
 */
$GLOBALS['config'] = parse_ini_string(<<<EOF
[Application]
implement = "Waakun"

[Database]
driver = "mysql"
server = "192.168.2.155"
user = "mysqlUser"
password = "mysqlPassword"
dbname = "test"
pager = 20

[Display]
model = "default"
style = "main"

EOF
, true);

//core\Mekayotl::trace($GLOBALS['config']);

//core\Mekayotl::trace(new core\tools\Mailer());

/*$rs = new core\database\sqlite\Adapter(array('dbname' => 'general'));
$tmps = $rs->pdo;
$tmp = $tmps->query("CREATE TABLE countries (
	code char(2) CONSTRAINT country_code PRIMARY KEY ASC,
	country varchar(50) CONSTRAINT country_name UNIQUE,
	language varchar(32),
	charset varchar(32),
	enabled int(1),
	lang_code varchar(5)
);");
$tmp = $tmps->query(utf8_encode("INSERT INTO countries VALUES ('MX','México','Español','iso-8859-1','1','es-mx');"));
$stmt = $rs->setFetchMode(4)->select('*', 'countries');
core\Mekayotl::trace($rs->fetchAll($stmt));*/

$tmp = new core\tools\CountryData('mx');
core\Mekayotl::trace($tmp);



core\Mekayotl::printTrace();
