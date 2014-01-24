<?php
/**
 * Achivo que es llamado por el browser.
 *
 * Este archivo es el que se procesa antes que cualquiera
 * Solicitado principalmente por el WebServer (Apache|IIS)
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua Interactive}
 * @package	Mekayotl.public
 * @since	$Date$
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
defined('PUBLIC_PATH') || define('PUBLIC_PATH', realpath(dirname(__FILE__)));
/**
 * Define el estado de la aplicacion
 * @name	PRODUCTION_SITE
 */
defined('PRODUCTION_SITE') || define('PRODUCTION_SITE', false);
/**#@-*/
/**
 * Pone a dispocion el Framework Mekayotl
 */
require_once '../../../lib/php/mekayotl/core.php';
/**
 * La configuracion que se esta ejecutando
 * @name	$config
 * @global	array	$GLOBALS['config']
 */
$GLOBALS['config'] = parse_ini_string(
    <<<EOF
[Application]
name = "Saas"

[Database]
driver = ""
server = ""
user = ""
password = ""
dbname = ""
pager = 20

[Display]
model = "default"
style = "main"

[email]
server = ""
user = ""
password = ""
name = ""

EOF
    ,
    true);

Mekayotl::runConfig();
