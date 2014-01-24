<?php
/**
 * Achivo que es llamado por el browser.
 *
 * Este archivo es el que se procesa antes que cualquiera
 * Solicitado principalmente por el WebServer (Apache|IIS)
 * @author  Henry I. Galvez T. <alograg@alograg.me>
 * @copyright   Copyright (c) 2012, {@link http://www.aquainteractive.com
 * Aqua Interactive}
 * @package Floyd.public
 * @since   $Date$
 * @subpackage  core
 * @version $Id$
 */

/**#@+
 * Constants
 */
/**
 * Define el path del directorio publico.
 * @name    PUBLIC_PATH
 */
defined('PUBLIC_PATH') || define('PUBLIC_PATH', realpath(dirname(__FILE__)));
/**
 * Define el estado de la aplicacion
 * @name    PRODUCTION_SITE
 */
defined('PRODUCTION_SITE') || define('PRODUCTION_SITE', FALSE);
/**#@-*/
/**
 * La configuracion que se esta ejecutando
 * @name    $config
 * @global  array   $GLOBALS['config']
 */
$GLOBALS['config'] = parse_ini_string(
        <<<EOF
[Application]
name = "Floyd"
implement = ""

[Database]
driver = "mysql"
server = "localhost"
user = "root"
password = ""
dbname = "floyd"
pager = 20

[Display]
model = "default"
style = "main"

[SaaS]
api = "http://saas.aquainteractive.com/panel/"
subscription = 0

[email]
server = ""
user = ""
password = ""
name = ""

[sendgrid]
server = "smtp.sendgrid.net"
user = "rmondragon"
password = "mosr7578"
name = "Floyd"
email = "notification@floyd.com"

EOF
        , TRUE);

/**
 * Ubicacion del core de Mekayotl
 * @var string
 */
$mekayotlCore = '../../lib/php/mekayotl/core.php';
/**
 * Comprueba la existencia del framework
 */
if(!is_file($mekayotlCore)){
    die('No Mekayotl');
}
/**
 * Pone a dispocion el Framework Mekayotl
 */
require_once $mekayotlCore;

Mekayotl::runConfig();
