<?php
//namespace core
/**
 * Clase principal del framework
 *
 * Esta clase se encarga de hacer varios procesos internos.
 * @author $Author$
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since 2011-03-01
 * @subpackage core
 * @version $Id$
 */

/**
 * La clase principal
 *
 * Esta clase realiza procesos internos y es el concentrador de funciones
 * globales del framework.
 * @package Mekayotl
 * @subpackage core
 */
class Mekayotl
{
    /**
     * Version del framework
     * @var string
     */
    const version = '1.0.$Rev$';
    /**#@+
     * @static
     * @access public
     */

    /**
     * Transforma un texto de tiempo de MySQL (ISO 8601) a un tipo tiempo de PHP
     * @param string $data Texto en formato ISP 8601
     * @param integer $days D?as a ser agregados
     * @return time Regresa el valor de tiempo del string
     */

    public static function iso8601ToTime($data, $days = 0)
    {
        $date = explode(' ', $data);
        $time = array(
                0,
                0,
                0
        );
        if (count($date) == 2) {
            $time = explode(':', $date[1]);
        }
        $date = explode('-', $date[0]);
        $return = mktime($time[0], $time[1], $time[2], $date[1],
                $date[2] + $days, intval($date[0]));
        return $return;
    }

    /**
     * Transforma un valor tiempo a un texto
     * @param time $time Tiempo a ser transformado
     * @param string $display Indica el formato de representaci�nde la fecha
     */

    public static function timeToText($time, $display = 'd/m/Y')
    {
        $return = date($display, $time);
        return $return;
    }

    /**
     * Transforma un texto a un URL amigable eliminando caracteres especiales y
     * cambiando caracteres acentuados a vocales
     * @param string $str String a transformar
     * @return string El texto transformado
     */

    public static function getFriendly($str = '', $underScore = TRUE)
    {
        $str = str_replace(
                explode(',', utf8_encode('�,�,�,�,�,�,�,�,�,�,�,�, ')),
                explode(',',
                        'a,e,i,o,u,n,A,E,I,O,U,N,'
                                . (($underScore) ? '_' : '-')), $str);
        $str = preg_replace('/([^a-zA-Z0-9])/i', (($underScore) ? '_' : '-'),
                $str);
        $str = urlencode(strtolower($str));
        return $str;
    }

    /**
     * Carga archivos de la librer?a externa.
     * @param string $file Path al archivo sin extension.
     * @return boolean Si el archivo fue cargado
     */

    public static function callExternal($file)
    {
        $file = EXTERNALS_PATH . '/' . $file . '.php';
        if (is_file($file)) {
            require_once($file);
            return TRUE;
        }
        self::trace('Error: El archivo ' . $file . ' no se encuentra');
        return FALSE;
    }

    /**
     * Comprueba la existencia de un path y si no lo crea
     * @param string $path El path es completo y debe empezar con /
     * @param hexadecimal $chmod Permisos de directorio.
     * @return boolean Si el path existe
     */

    public static function generatePath($path, $chmod = 0775)
    {
        $pathinfo = pathinfo($path);
        if (is_dir($pathinfo['dirname'])) {
            return TRUE;
        }
        $aDirs = explode('/', $pathinfo['dirname']);
        if ($aDirs[0] == '' && Mekayotl_tools_Utils::getOS() == 'Windows') {
            array_shift($aDirs);
        }
        if (!(mkdir(implode('/', $aDirs), $chmod, TRUE))) {
            sleep(10);
            while ($d = array_shift($aDirs)) {
                sleep(1);
                $fulDir .= $d . '/';
                $fulDir;
                if (!is_dir($fulDir)) {
                    mkdir($fulDir, $chmod, TRUE);
                }
            }
        }
        sleep(1);
        chmod($pathinfo['dirname'], $chmod);
        return is_dir($pathinfo['dirname']);
    }

    /**
     * Ejecuta una configuraci?n
     * @param array $config Un array asociativo con la configuraci�nde toda la
     * aplicaci�n.
     */

    public static function run(array &$config)
    {
        $appClassName = 'App_' . $config['Application']['name'];
        $config['class'] = $appClassName;
        $application = new $appClassName($config);
        $application->run($config);
        Mekayotl_Debug::trace($application);
    }

    /**
     * Ejecuta la configuraci�nglobal establecida.
     */

    public static function runConfig()
    {
        self::run(&$GLOBALS['config']);
    }

    /**
     * Crea un adaptador a la base de datos.
     * @param string|array $config Una cadena con el nombre de la secci�n de
     * configuraci�no un URI para la conexi�n. Arreglo asociativo con la
     * configuraci�nde conexi�n.
     * @return Mekayotl_database_SQLAbstract
     */

    public static function adapterFactory($config)
    {
        if (is_string($config)) {
            $parsed = parse_url($config);
            $isDSN = count($parsed) >= 5;
            if ($isDSN) {
                $parsed['path'] = str_replace('/', '', $parsed['path']);
                $config = array(
                        'driver' => $parsed['scheme'],
                        'server' => $parsed['host'],
                        'user' => $parsed['user'],
                        'password' => $parsed['pass'],
                        'dbname' => $parsed['path']
                );
            } else if (isset($GLOBALS['config'][$config])) {
                $config = $GLOBALS['config'][$config];
            }
        }
        $adapterClass = 'Mekayotl_database_' . $config['driver'] . '_Adapter';
        return new $adapterClass($config);
    }

    /**
     * Controla las cargas de clases.
     * @param string $className Nombre de la clase a cargar
     */

    public static function autoload($className)
    {
        $fileName = str_replace(
                array(
                        'Mekayotl_',
                        'App_',
                        '_'
                ), DIRECTORY_SEPARATOR, $className);
        if ($className == 'Mekayotl') {
            $fileName = DIRECTORY_SEPARATOR . $className;
        }
        $mekayotlFile = MEKAYOTL_PATH . $fileName . '.php';
        $applicationFile = APPLICATION_PATH . $fileName . '.php';
        if (is_file($mekayotlFile)) {
            require_once($mekayotlFile);
        } elseif (is_file($applicationFile)) {
            require_once($applicationFile);
        } else {
            //Mekayotl::stop(
            //"Class File of class '" . $className . "' not found ("
            //. $applicationFile . ')',
            //E_COMPILE_WARNING);
            /*trigger_error(
                "Class File of class '" . $className . "' not found",
                E_COMPILE_WARNING);*/
            return "Class File of class '" . $className . "' not found in:\n"
                    . $mekayotlFile . "\n" . $applicationFile;
        }
    }

    public static function end($string = '')
    {
        die($string);
    }

    public static function getAplicationName()
    {
        return $GLOBALS['config']['Application']['name'];
    }
    /**#@-*/

}
