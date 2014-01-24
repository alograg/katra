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
class Mekayotl_tools_utils_Numbers
{
    /**#@+
     * @static
     * @access public
     */
    /**
     * Convierte un numero en letras.
     * @param number $n Un numero.
     */

    public static function toCharacters($n)
    {
        return ($n-- > 26 ? chr(($n / 26 + 25) % 26 + ord('A')) : '')
                . chr($n % 26 + ord('A'));
    }

    /**
     * Transforma un unidades de un entero en su representación verbal
     * @param integer $int
     * @return string
     */

    private static function unidad($int)
    {
        $arrUnidad[1] = "uno";
        $arrUnidad[2] = "dos";
        $arrUnidad[3] = "tres";
        $arrUnidad[4] = "cuatro";
        $arrUnidad[5] = "cinco";
        $arrUnidad[6] = "seis";
        $arrUnidad[7] = "siete";
        $arrUnidad[8] = "ocho";
        $arrUnidad[9] = "nueve";
        $arrUnidad[10] = "diez";
        $arrUnidad[11] = "once";
        $arrUnidad[12] = "doce";
        $arrUnidad[13] = "trece";
        $arrUnidad[14] = "catorce";
        $arrUnidad[15] = "quince";
        $arrUnidad[16] = "dieciséis";
        $arrUnidad[17] = "diecisiete";
        $arrUnidad[18] = "dieciocho";
        $arrUnidad[19] = "diecinueve";
        return $arrUnidad[$int];
    }

    /**
     * Transforma un decenas de un entero en su representación verbal
     * @param integer $int
     * @return string
     */

    private static function decena($int)
    {
        $decenas = min(floor($int / 10), 9);
        $unidades = $int - ($decenas * 10);
        $textoDecenas = array_filter(
                array(
                        NULL,
                        NULL,
                        NULL,
                        'treinta',
                        'cuarenta',
                        'cincuenta',
                        'sesenta',
                        'setenta',
                        'ochenta',
                        'noventa'
                ));
        if ($textoDecenas[$decenas]) {
            $numLetra = $textoDecenas[$decenas]
                    . (($unidades > 0) ? " y " . self::unidad($unidades) : '');
        } elseif ($decenas == 2) {
            if ($int == 20) {
                $numLetra = "veinte";
            } else {
                $numLetra = "veinti" . self::unidad($int - 20);
            }
        } elseif ($int <= 19) {
            $numLetra = self::unidad($int);
        }
        return $numLetra;
    }

    /**
     * Transforma un centenas de un entero en su representación verbal
     * @param integer $int
     * @return string
     */

    private static function centena($int)
    {
        if ($int < 100) {
            return decena($int);
        }
        $centenas = min(floor($int / 100), 9);
        $decenas = $int - ($centenas * 100);
        $textoCentenas = array_filter(
                array(
                        NULL,
                        NULL,
                        'doscientos',
                        'trescientos',
                        'cuatrocientos',
                        'quinientos',
                        'seiscientos',
                        'setecientos',
                        'ochocientos',
                        'novecientos'
                ));
        if ($textoCentenas[$decenas]) {
            $numLetra = $textoCentenas[$centenas]
                    . (($decenas > 0) ? " " . self::decena($decenas) : '');
        } elseif ($decenas == 1) {
            $numLetra = $decenas == 0 ? 'cien'
                    : 'ciento ' . self::decena($decenas);
        }
        return $numLetra;
    }

    /**
     * Transforma un miles de un entero en su representación verbal
     * @param integer $int
     * @return string
     */

    private static function miles($int)
    {
        if ($int == 1) {
            $numLetra = "mil";
        } else {
            $numLetra = self::centena($int) . " mil";
        }
        return $numLetra;
    }

    /**
     * Transforma un millares de un entero en su representación verbal
     * @param integer $int
     * @return string
     */

    private static function millones($int)
    {
        if ($int == 1) {
            $numLetra = "un millon";
        } else {
            $numLetra = self::centena($int) . " millones";
        }
        return $numLetra;
    }

    /**
     * Transforma un numero en su representación verbal
     * @param integer $int
     * @return string
     */

    public static function toPhrase($number)
    {
        $fltNumero = floatval($strNumero);
        if ($fltNumero < 0) {
            $fltNumero = $fltNumero * -1;
            $signo = "Menos ";
        }
        $strNumero = number_format($fltNumero, 2, '-', ',');
        $arrNumBase = explode("-", $strNumero);
        $arrPartesNumeros = array_reverse(split(",", $arrNumBase));
        $letras = "";
        switch (count($arrPartesNumeros)) {
            case 6:
                $letras .= self::centena(intval($arrPartesNumeros[5])) . ' ';
            case 5:
                $letras .= self::miles(intval($arrPartesNumeros[4])) . ' ';
            case 4:
                $letras .= self::centena(intval($arrPartesNumeros[3])) . ' ';
            case 3:
                $letras .= self::millones(intval($arrPartesNumeros[2])) . ' ';
            case 2:
                $letras .= self::miles(intval($arrPartesNumeros[1])) . ' ';
            case 1:
                $letras .= self::centena(intval($arrPartesNumeros[0])) . ' ';
        }
        $strDec = intval($arrNumBase[1]);
        if (strlen($strDec) < 2) {
            $strDec = "0" . $strDec;
        }
        $numConvertido = ucfirst($signo . $letras . $strDec . "/100");
        return $numConvertido;
    }

    /**#@-*/
}
