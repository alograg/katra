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
class Mekayotl_Debug
{
    /**#@+
     * @static
     * @access public
     */
    /**
     * Contiene todas las variables a rastrear
     * @var array
     */

    private static $_debug = array();
    /**
     * Variable para rastrear el tiempo de inicio
     * @var integer inicio de un timer
     * @access private
     * @uses Mekayotl::startTimer() Es utilizado para guardar el inicio
     * @uses Mekayotl::stopTimer() Es utilizado para el calculo del tiempo
     * transcurrido
     */
    private static $_starTime = NULL;
    /**
     * Inicia un timer de rastreo
     */

    public static function startTimer()
    {
        self::$_starTime = microtime(TRUE);
    }

    /**
     * Finaliza el timer de rastreo
     * @return integer El micro-tiempo transcurrido desde la llamada de
     * Mekayotl::startTimer()
     */

    public static function stopTimer()
    {
        $start = self::$_starTime;
        $end = microtime(TRUE);
        $return = round(($end - $start), 2);
        self::$_starTime = NULL;
        return $return;
    }

    /**
     * Agrega un string al rastreo de variables
     * @param mixed $v Variable a agregar para rastrear
     */

    public static function trace($v)
    {
        if (MEKAYOTL_DEBUG) {
            self::$_debug[] = $v;
            if (class_exists('NetDebug', FALSE)) {
                //NetDebug::trace($v);
            }
        }
    }

    /**
     * Da salida a las variables agregadas al rastreo
     */

    public static function printTrace()
    {
        if (MEKAYOTL_DEBUG) {
            var_dump(self::$_debug);
        }
    }

    /**
     * Detiene todos los procesos.
     *
     * Si la aplicación esta en modo de debug, genera un rastreo a donde
     * fue llamado.
     * @param mix $dump Variable a ser rastreada.
     * @param string $dieText Texto a mostrar al parar.
     */

    public static function stop($dump = NULL, $dieText = NULL)
    {
        if (MEKAYOTL_DEBUG) {
            $trace = debug_backtrace();
            var_dump($trace[0]);
        }
        Mekayotl::end($dieText);
    }

    /**
     * Agrega un string al rastreo de variables
     * @param string $file Nombre del log. Default: log
     * @param string $line Texto a agregar. Default: none
     * @param string $trackFormat Formato de rastreo. Default: Ymd
     */

    public static function addToLogFile($file = 'log', $line = 'None',
            $trackFormat = "Ymd")
    {
        if (MEKAYOTL_DEBUG && LOGS_PATH) {
            Mekayotl::generatePath(LOGS_PATH);
            if (is_dir(LOGS_PATH)) {
                $filePath = LOGS_PATH . '/' . $file . '.' . date($trackFormat)
                        . '.log';
                $fh = fopen($filePath, 'a');
                fwrite($fh, '[' . date('Y-m-d H:i:s') . '] ' . $line . "\r\n");
                fclose($fh);
            }
            return $filePath;
        }
        return FALSE;
    }

    /**
     * Manejador de errores
     * @param integate $errno
     * @param string $errstr
     * @param string $errfile
     * @param integrate $errline
     * @param array $errcontext
     */

    public static function errorHandler($errno, $errstr, $errfile, $errline,
            $errcontext)
    {
        $l = error_reporting();
        if ($l == 0) {
            return;
        }
        $exit = FALSE;
        $type = 'Unknown Error';
        if ($l & $errno) {
            self::_choiceError($errno, $type, $exit);
        }
        $exception = new ErrorException($type . ': ' . $errstr, 0, $errno,
                $errfile, $errline);
        if ($exit) {
            self::exceptionHandler($exception);
            exit(__FILE__ . ':' . __LINE__);
        }
        return FALSE;
    }

    /**
     * Escoje el tipo de error.
     * @param integate $errno
     * @param string $type
     * @param boolean $exit
     */

    private static function _choiceError($errno, &$type, &$exit)
    {
        switch ($errno) {
            case E_USER_ERROR:
                $type = 'Fatal Error';
                $exit = TRUE;
                break;
            case E_USER_WARNING:
            case E_WARNING:
                $type = 'Warning';
                break;
            case E_USER_NOTICE:
            case E_NOTICE:
            case @E_STRICT:
                $type = 'Notice';
                break;
            case @E_RECOVERABLE_ERROR:
                $type = 'Catchable';
                break;
            default:
                $type = 'Unknown Error';
                $exit = TRUE;
                break;
        }
    }

    /**
     * Manejador de Excepciones
     * @param ErrorException $exception
     */

    public static function exceptionHandler($exception)
    {
        $logString = $exception->getMessage();
        $logString .= LINEBREAK;
        $logString .= $exception->getTraceAsString();
        self::addToLogFile('errors', $logString);
        self::trace($exception);
        self::printTrace();
    }

    /**
     * Rastreo de llamadas
     * @param boolean $params
     * @param boolean $end
     */

    public static function traceCallStack($params = FALSE, $end = FALSE)
    {
        if (MEKAYOTL_DEBUG) {
            $trace = debug_backtrace();
            $size = count($trace);
            for ($i = 1; $i < $size; $i++) {
                $item = $trace[$i];
                print
                        $item['file'] . ':' . $item['line'] . '->'
                                . $item['function'] . '(';
                if ($params) {
                    try {
                        print substr(json_encode($item['args']), 0, 80);
                    } catch (Exception $e) {
                        print gettype($item['args']);
                    }
                }
                print ')' . "\n";
            }
        }
        if ($end) {
            Mekayotl::end();
        }
    }

    /**#@-*/

}
