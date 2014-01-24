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
class Mekayotl_tools_utils_Conversion
{
    /**#@+
     * @static
     * @access public
     */

    /**
     * Evalua un string para convertirlo en eun dato
     * @param string $value
     * @return mixed
     */

    public static function evalString($value)
    {
        if (is_string($value)) {
            $value = urldecode($value);
            if ($value != "") {
                $allow = array(
                        "false",
                        "FALSE",
                        "true",
                        "TRUE",
                        "null",
                        "NULL"
                );
                if ($value[0] == '[' || $value[0] == '{'
                        || in_array($value, $allow)
                        || preg_match('/^[0-9\\.]+$/i', $value)) {
                    $value = json_decode($value, TRUE);
                }
            }
        }
        return $value;
    }

    /**
     * Interpreta un string para devolverlo como la variable
     * @param string $data El string a convertir
     * @param string $type El tipo origen
     * (json|int|float|complex:array|array|time|string). Default: string
     * @return mixed El texto convertido
     */

    public static function toPHP($data, $type = 'string')
    {
        $type = strtolower($type);
        $return = $data;
        switch ($type) {
            case 'array':
                $return = explode(',', $data);
                break;
            case 'complex:array':
                $regexp = '/(?P<level>(?P<key>\w+):(?P<value>\w+)\,*)/i';
                preg_match_all($regexp, $data, $matches);
                $return = array_combine($matches['key'], $matches['value']);
                break;
        }
        return self::evalString($return);
    }

    /**
     * Interpreta una variable para regrezarla la codificación indicada
     * @param mixed $data La variable a convertir
     * @param string $type El tipo destino
     * (json|int|float|complex:array|array|time|string). Default: string
     * @return string El texto convertido
     */

    public static function toString($data, $type = 'string')
    {
        $type = strtolower($type);
        switch ($type) {
            case 'json':
                $return = json_encode($data);
                break;
            case 'int':
                $return = intval($data);
                break;
            case 'float':
                $return = floatval($data);
                break;
            case 'complex:array':
                foreach ($data as $key => $value) {
                    $data[$key] = $key . ':' . $value;
                }
            case 'array':
                $return = implode(',', $data);
                break;
            case 'time':
                if (is_int($data))
                    $data = date('Y-m-d H:i:s', intval($data));
            case 'string':
            case 'html':
            default:
                $return = $data;
        }
        if ($data == NULL) {
            $return = 'null';
        }
        return $return;
    }

    /**
     * Transforma un array asociativo en un string de XML
     * @param variant $object El array a convertir
     * @param string $name Nombre del nodo
     * @return string El string con el XML
     */

    public static function toXML($object, $name = 'root', $asString = FALSE,
            &$remove = array())
    {
        $backTrace = debug_backtrace();
        $recursive = (count($backTrace) > 2
                && $backTrace[1]['function'] == 'toXML'
                && $backTrace[1]['class'] == 'Mekayotl_tools_utils_Conversion');
        if (is_string($name)) {
            $xml = new SimpleXMLElement('<' . $name . '/>');
        } elseif ($name instanceof SimpleXMLElement) {
            $xml = $name;
        }
        if (is_array($object)) {
            foreach ($object as $elementName => $vValue) {
                if (substr_count($elementName, '@')) {
                    if (is_bool($vValue)) {
                        $vValue = ($vValue) ? 'TRUE' : 'false';
                    }
                    if ($vValue) {
                        $xml->addAttribute(substr($elementName, 1),
                                        (string) $vValue);
                    }
                } else {
                    if (is_integer($elementName)) {
                        $elementName = $xml->getName();
                        $remove[] = $elementName;
                        $remove = array_unique($remove);
                    }
                    switch (gettype($vValue)) {
                        case 'array':
                        case 'object':
                            $children = $xml->addChild($elementName);
                            self::toXML($vValue, $children, FALSE, $remove);
                            break;
                        case 'boolean':
                            $vValue = ($vValue) ? 'TRUE' : 'false';
                        case 'integer':
                        case 'double':
                        case 'string':
                        default:
                            if (!is_null($vValue)) {
                                $children = $xml->addChild($elementName,
                                                $vValue);
                            }
                    }
                }
            }
        }
        if (!$recursive) {
            if (!$asString) {
                return $xml;
            }
            $stringXML = $xml->asXML();
            $arrayXML = explode("\n", $stringXML);
            $stringXML = $arrayXML[1];
            foreach ($remove as $tag) {
                $stringXML = str_replace(
                        array(
                                '<' . $tag . '>',
                                '</' . $tag . '>'
                        ), '', $stringXML);
            }
            return $stringXML;
        }
    }

    /**
     * Recibe un timestamp o un entero de 1 al 12 y regresa el mes en español
     * y en minusculas
     * @param   integer $int
     * @return  string
     */

    public static function mesEsp($int, $avrev=FALSE)
    {
        if ($int > 12) {
            $int = (int) date('n', $int);
        }
        $meses = array(
                'Mes invalido',
                'enero',
                'febrero',
                'marzo',
                'abril',
                'mayo',
                'junio',
                'julio',
                'agosto',
                'septiembre',
                'octubre',
                'noviembre',
                'diciembre'
        );
        $mesesAv = array(
                'Mes invalido',
                'ene',
                'feb',
                'mar',
                'abr',
                'may',
                'jun',
                'jul',
                'ago',
                'sept',
                'oct',
                'nov',
                'dic'
        );
        if($avrev)
            return (isset($mesesAv[$int])) ? $mesesAv[$int] : 'Mes invalido';
        else
            return (isset($meses[$int])) ? $meses[$int] : 'Mes invalido';
    }
    /**
     * Recibe un timestamp o un entero del 1 al 7 y regresa el dia en español
     * y en minusculas
     * @param   integer $int
     * @return  string
     */

    public static function diaEsp($int)
    {
        if ($int > 7) {
            $int = (int) date('N', $int);
        }
        $dias = array(
                'domingo',
                'lunes',
                'martes',
                'miércoles',
                'jueves',
                'viernes',
                'sábado',
                'domingo'
        );
        return (isset($dias[$int])) ? $dias[$int] : 'Mes invalido';
    }

    /**
     * Busca los campos en un texto y los remplaza por datos de un array
     * @param array $data El elemento que tiene los valores.
     * @param string $text El texto a ser remplazado.
     * @return string El texto con los datos colocados
     */

    public static function substitute(array $data, $text)
    {
        $search = array();
        $replace = array();
        foreach ($data as $field => $value) {
            $search[] = '{' . $field . '}';
            $replace[] = $value;
        }
        return str_replace($search, $replace, $text);
    }

    /**
     * Procesa una linea CSV para cambiarla a un arreglo indexado.
     * @param string $line La linea a ser cambiada.
     * @return array Arreglo indexado.
     */

    public static function processCSVLine(&$line)
    {
        $line = trim($line);
        $line = str_getcsv($line);
        return $line;
    }

    /**#@-*/
}
