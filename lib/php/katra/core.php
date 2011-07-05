<?php
/**
 * Achivo que es llamado por el browser.
 *
 * Este archivo es el que se procesa antes que cualquiera
 * Solicitado principalmente por el WebServer (Apache|IIS)
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://alograg.me}
 * @package	Katra
 * @since	2011-03-01
 * @subpackage	core
 * @version	$Id$
 */

/**
 * Analisa la version de PHP para optimo funcionamiento
 */
if(version_compare("5.3.0", PHP_VERSION)>0)
	die('Katra requiere PHP version 5.3.0 o superior. Version de este servidor: PHP '.PHP_VERSION);

/**
 * Establece las variables locales a español
 */
setlocale(LC_ALL, array('esn','es','es-ES'));
date_default_timezone_set('America/Mexico_City');
/**
 * Inicializa la sesion
 */
if(!isset($_SESSION))
	@session_start();
/**#@+
 * Constants
 */
/**
 * Define el path del freamework
 * @name	KATRA_PATH
 * @uses	EXTERNALS_PATH lo usa para definir el path
 */
defined('KATRA_PATH') ||
	define('KATRA_PATH', realpath(dirname(__FILE__)));
/**
 * Define el path a las librerias externas
 * @name	EXTERNALS_PATH
 */
defined('EXTERNALS_PATH') ||
	define('EXTERNALS_PATH', realpath(KATRA_PATH . '/../externals/'));
/**
 * Define el path a las librerias externas
 * @name	EXTERNALS_PATH
 */
defined('LOGS_PATH') ||
	define('LOGS_PATH', realpath(KATRA_PATH . '/../../../logs/Katra/'));
/**
 * Define el destino de predeterminado de los archivos cargados
 * @name	MEDIA_PATH
 */
defined('MEDIA_PATH') ||
	define('MEDIA_PATH', realpath(PUBLIC_PATH . '/medias/'));
/**
 * Define el estado de debug
 * @name	KATRA_DEBUG
 * @uses	PRODUCTION_SITE lo usa para definir el estado
 */
defined('KATRA_DEBUG') ||
	define('KATRA_DEBUG', !PRODUCTION_SITE);
/**#@-*/

/**
 * Define los reportes de errores segun el estado de debug
 */
if(KATRA_DEBUG)
	error_reporting(E_ALL ^E_WARNING ^E_NOTICE);
else
	error_reporting(0);

/**
 * Controla las cargas de claces.
 * @param	string	$class_name Nombre de la clace a cargar
 */
function __autoload($class_name) {
	$class_name = str_replace(array('core', '\\'), array('','/'), $class_name);
	$file = KATRA_PATH . $class_name . '.php';
	if(is_file($file))
		require_once($file);
	else
		core\Katra::trace('Error: la clace ' . $class_name . ' no se encuentra');
}

