<?php
//namespace core\tools;
/**
 * Utilerias variadas.
 *
 * @author Henry I. Galvez T. <alograg@alograg.me>
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since 2012-04-03
 * @subpackage tools
 * @version $Id$
 */

/**
 * Esta clase proporciona metodos varios.
 *
 * @package Mekayotl
 * @subpackage tools
 */
class Mekayotl_tools_Utils
{
    /**#@+
     * @static
     * @access public
     */
    /**
     * Regresa el tipo sistema operativo del servidor
     * @return string El nombre del Sistema operativo
     */

    public static function getOS()
    {
        exec('cat /etc/issue', $versionLinux);
        exec('ver', $versionWindows);
        $versionLinux = substr_count(implode("\n", $versionLinux), 'Linux');
        $versionWindows = substr_count(implode("\n", $versionWindows),
                'Windows');
        $os = 'unknow';
        $os = ($versionLinux > 0) ? 'Linux' : $os;
        $os = ($versionWindows > 0) ? 'Windows' : $os;
        return $os;
    }

    /**
     * Regresa los lenguajes del navegador
     * @return array Los lenguajes del browser
     */

    public static function getBrowserLang()
    {
        $aLang = split(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($aLang as $key => $sLang) {
            $sLang[$key] = str_replace('-', '_', $sLang);
        }
        return $aLang;
    }

    /**
     * Obtiene la información del disco donde se esta trabajando
     * @param string $sUnidad Unidad de medida de los bytes, Default mb
     * @return object Un objeto con las propiedades; free, total y used
     */

    public static function getDiskSpace($sUnidad = 'mb')
    {
        $tmp['free'] = disk_free_space(SITE_BASE_PATH);
        $tmp['total'] = disk_total_space(SITE_BASE_PATH);
        $tmp['used'] = $this->total - $this->free;
        switch ($sUnidad) {
            case 'tb':
                $tmp['free'] /= 1024;
                $tmp['total'] /= 1024;
                $tmp['used'] /= 1024;
            case 'gb':
                $tmp['free'] /= 1024;
                $tmp['total'] /= 1024;
                $tmp['used'] /= 1024;
            case 'mb':
                $tmp['free'] /= 1024;
                $tmp['total'] /= 1024;
                $tmp['used'] /= 1024;
            case 'kb':
                $tmp['free'] /= 1024;
                $tmp['total'] /= 1024;
                $tmp['used'] /= 1024;
            default:
                $tmp['free'] = round($tmp['free'], 2);
                $tmp['total'] = round($tmp['total'], 2);
                $tmp['used'] = round($tmp['used'], 2);
        }
        return (object) $tmp['used'];
    }

    /**
     * Agrega un elemento asociativo antes o después de elemento indicado
     * @param array $target Arreglo objetivo
     * @param array $data Datos a agregar
     * @param string $location Objetivo, ej.; top, buttom, after:key, before:key
     * @return array
     * @access public
     */

    public static function grab($target, $data, $location = 'top')
    {
        $regexp = '/^(?P<position>top|after|before|(ap|pre)pend|buttom):'
                . '(?P<key>\w+)$/';
        if (!preg_match($regexp, $location, $matches)) {
            $regexp = '/^(?P<position>top|after|before|(ap|pre)pend|buttom)$/';
            preg_match($regexp, $location, $matches);
        }
        if (!isset($matches['key'])) {
            $matches['position'] .= '0';
        }
        switch ($matches['position']) {
            case 'prepend0':
            case 'top':
                array_unshift($target, $data);
                break;
            case 'append0':
            case 'buttom':
                array_push($target, $data);
                break;
            default:
                $result = $target;
                if (count($target) >= 1) {
                    $result = array();
                    foreach ($target as $key => $value) {
                        if ($key == $matches['key']
                                && ($matches['position'] == 'before'
                                        || $matches['position'] == 'prepend')) {
                            $result = $result + $data;
                        }
                        $result[$key] = $value;
                        if ($key == $matches['key']
                                && $matches['position'] == 'after'
                                || $matches['position'] == 'append') {
                            $result = $result + $data;
                        }
                    }
                    $target = $result;
                } else {
                    $target = $result + $data;
                }
        }
        return $target;
    }

    /**
     * Valida que in texto sea un correo electronico valido.
     * @param string $string
     * @return boolean
     */

    public static function validMail($string)
    {
        $regexp = '/^[\w\.=-]+@[\w\.-]+\.[\w]{2,3}$/i';
        return (boolean) preg_match($regexp, $string);
    }

    /**
     *
     * @param string $pattern
     * @param integer $flags
     * @return array
     */
    public static function globRecursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files,
                    self::globRecursive($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }

    /**#@-*/
}
