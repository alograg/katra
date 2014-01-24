<?php
die('Template Orbet');
/**
 * Achivo que es llamado por el browser.
 *
 * Este archivo es el que se procesa antes que cualquiera
 * Solicitado principalmente por el WebServer (Apache|IIS)
 * @author    Henry I. Galvez T. <alograg@alograg.me>
 * @copyright    Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua Interactive}
 * @package    Mekayotl.public
 * @since    $Date$
 * @subpackage    core
 * @version    $Id$
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
defined('PRODUCTION_SITE') || define('PRODUCTION_SITE', TRUE);
/**#@-*/
if (isset($_SERVER['APPLICATION_PATH'])) {
    defined('APPLICATION_PATH')
            || define('APPLICATION_PATH',
                    realpath($_SERVER['APPLICATION_PATH']));
}

/**
 * Ubicacion del core de Mekayotl
 * @var string
 */
$mekayotlCore = $_SERVER['DOCUMENT_ROOT']
        . '/www.nortcloud.com/lib/php/mekayotl/core.php';
/**
 * Comprueba la existencia del framework
 */
if (!is_file($mekayotlCore)) {
    die('We have troubles');
}
/**
 * Pone a dispocion el Framework Mekayotl
 */
require_once $mekayotlCore;

/**
 * La configuracion que se esta ejecutando
 * @name    $config
 * @global    array    $GLOBALS['config']
 */
$GLOBALS['config'] = parse_ini_string(
        <<<EOF
[Application]
name = "Floyd"

[Database]
driver = "mysql"
server = "localhost"
user = "{user}"
password = "{password}"
dbname = "{user}_orbet"
pager = 20

[SaaS]
api = "http://{api}/dashboard/"
subscription = {subscription}
account = {account}

[sendgrid]
server = "smtp.sendgrid.net"
user = "rmondragon"
password = "mosr7578"
name = "Orbet"
email = "notification@nortcloud.com"

[defaultTimers]
task=1:00
member=40:00
log=00:30

EOF
        , TRUE);

Mekayotl::runConfig();
