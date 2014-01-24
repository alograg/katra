<?php
/**
 * Archivo que es llamado por el browser.
 *
 * Este archivo es el que se procesa antes que cualquiera
 * Solicitado principalmente por el WebServer (Apache|IIS)
 * @author $Author$
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since $Date$
 * @subpackage core
 * @version $Id$
 */

/**
 * Analiza la version de PHP para óptimo funcionamiento
 */
if (version_compare("5.2.9-2", PHP_VERSION) > 0)
    die(
            'Mekayotl requiere PHP version 5.2.9-2 o superior.
            Version de este servidor: PHP ' . PHP_VERSION);

/**
 * Establece las variables locales a español
 */
setlocale(LC_ALL, array(
        'esn',
        'es',
        'es-ES'
));
date_default_timezone_set('America/Mexico_City');
/**
 * Inicializa la sesión
 */
if (!isset($_SESSION)) {
    @session_start();
}
/**#@+
 * Constants
 */
/**
 * Define el path del framework
 * @name MEKAYOTL_PATH
 * @uses EXTERNALS_PATH lo usa para definir el path
 */
defined('MEKAYOTL_PATH')
        || define('MEKAYOTL_PATH', realpath(dirname(__FILE__)));
/**
 * Define el path a las aplicaciones
 * @name APPLICATION_PATH
 */
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH',
                realpath(MEKAYOTL_PATH . '/../../../app/'));
/**
 * Define el path del framework
 * @name MEKAYOTL_PATH
 * @uses EXTERNALS_PATH lo usa para definir el path
 */
defined('LIBS_PATH')
        || define('LIBS_PATH', realpath(MEKAYOTL_PATH . '/../../'));
/**
 * Define el path a las librerías externas
 * @name EXTERNALS_PATH
 */
defined('EXTERNALS_PATH')
        || define('EXTERNALS_PATH', realpath(MEKAYOTL_PATH . '/../externals/'));
/**
 * Define el path a las librerías externas
 * @name EXTERNALS_PATH
 */
defined('LOGS_PATH')
        || define('LOGS_PATH',
                realpath(MEKAYOTL_PATH . '/../../../logs/Mekayotl/'));
/**
 * Define el destino de predeterminado de los archivos cargados
 * @name MEDIA_PATH
 */
defined('MEDIA_PATH')
        || define('MEDIA_PATH', realpath(PUBLIC_PATH . '/medias/'));
/**
 * Define el estado de debug
 * @name MEKAYOTL_DEBUG
 * @uses PRODUCTION_SITE lo usa para definir el estado
 */
defined('MEKAYOTL_DEBUG') || define('MEKAYOTL_DEBUG', TRUE);
//!PRODUCTION_SITE);
/**
 * Define un salto de linea
 * @name LINEBREAK
 */
define('LINEBREAK', "\r\n");
/**#@-*/

/**
 * Define los reportes de errores según el estado de debug
 */
if (MEKAYOTL_DEBUG) {
    error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
} else {
    error_reporting(0);
}
/**
 * Define el manejador de errores a core/Mekayotl::errorHandler
 */
//$errorHandler = set_error_handler('Mekayotl::errorHandler');
/**
 * Define el manejador de errores a core/Mekayotl::errorHandler
 */
//$exceptionHandler = set_exception_handler('Mekayotl::exceptionHandler');
/**
 * Registra la el metodo de carga de clases.
 */
require_once(MEKAYOTL_PATH . '/Mekayotl.php');
if (function_exists('spl_autoload_register')) {
    spl_autoload_register('Mekayotl::autoload');
} else {

    /**
     * Controla las cargas de clases.
     * @param string $class_name Nombre de la clase a cargar
     */
    function __autoload($className)
    {
        Mekayotl::autoload($className);
    }

}

/**
 * Proporciona compatibilidad con verciones menores a la 5.3.
 */
if (!function_exists('parse_ini_string')) {

    /**
     * Analiza una cadena de configuración
     * @param string $ini El contenido del archivo ini que va a
     * ser analizado.
     * @param boolean $processSections Al establecer el parámetro
     * processSections a TRUE, se obtiene una matriz multidimensional, con los
     * nombres de las secciones y las configuraciones incluidas. El valor por
     * defecto de process_sections es FALSE
     * @param integer $scannerMode Puede ser o INI_SCANNER_NORMAL (por defecto)
     * o INI_SCANNER_RAW. Si INI_SCANNER_RAW es proporcionado, los valores de
     * las opciones no serán analizadas.
     * @return Las configuraciones son devueltas como un array asociativo si
     * se tiene éxito, y FALSE si falla.
     * @see {@link http://mx.php.net/manual/es/function.parse-ini-string.php}
     */
    function parse_ini_string($ini, $processSections = FALSE,
            $scannerMode = INI_SCANNER_NORMAL)
    {
        //Crea un archivo temporal y lo analiza como ini
        $tempname = tempnam(sys_get_temp_dir(), 'ini');
        $fp = fopen($tempname, 'w');
        fwrite($fp, $ini);
        $ini = parse_ini_file($tempname, !empty($processSections));
        fclose($fp);
        @unlink($tempname);
        return $ini;
    }

}
if (!function_exists('str_getcsv')) {

    /**
     * Analisa una cadena de texto csv para convertila en un arreglo
     * @param string $input La cadena de texto a interpretar.
     * @param string $delimiter Establece el delimitador de campo
     * (sólo un caracter).
     * @param string $enclosure Establece el caracter de encerrado del campo
     * (sólo un caracter).
     * @param string $escape Establece el caracter de escape
     * (sólo un caracter). Por defecto es una barra invertida. (\)
     * @return array Devuelve un arreglo indexado que contiene los campos
     * leídos.
     * @see {@link http://mx.php.net/manual/es/function.str-getcsv.php}
     */
    function str_getcsv($input, $delimiter = ",", $enclosure = '"',
            $escape = "\\")
    {
        $fiveMBs = 5 * 1024 * 1024;
        $fp = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
        fputs($fp, $input);
        rewind($fp);
        //  $escape only got added in 5.3.0
        $data = fgetcsv($fp, 1000, $delimiter, $enclosure);
        fclose($fp);
        return $data;
    }

}
