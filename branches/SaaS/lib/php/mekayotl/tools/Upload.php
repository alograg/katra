<?php
//namespace core\tools;
/**
 * Herramienta para manipulacion de imagenes.
 *
 * @author Henry I. Galvez T. <alograg@alograg.me>
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since 2011-03-01
 * @subpackage tools
 * @version $Id$
 */
/**
 * Cambia el tamaño máximo para carga de archivos
 */
ini_set('upload_max_filesize', '2000M');

/**
 * Esta clase le da un proceso a las imagen original con respecto al URL de
 * salida.
 *
 * @package Mekayotl
 * @subpackage tools
 */
class Mekayotl_tools_Upload
{
    /**#@+
     * @access private
     * @static
     */

    /**
     * Contiene el directorio base para archivos
     * @var string
     */

    public static $directory;
    /**
     * Contiene la ultima manipulación de archivos
     * @var array Un arreglo asociativo
     */
    public static $last = array();
    /**
     * Contiene un historial de los movimientos de archivos
     * @var array
     */
    public static $history = array();
    /**
     * Manipula el destino de un archivo cargado
     * @param array $oFile El arreglo asociativo con información del archivo
     * cargado
     * @param string $sDestination El destino del archivo
     * @param boolean|string $originalName Si mantiene el nombre original o
     * string con el nuevo nombre
     * @param boolean $move Si el archivo tiene que ser movido o copiado
     * @param boolean $originalExtension Aqui se puede especifiar si se quiere
     * una extension especial TRUE si es la original, FALSE si es en blanco y
     * cualquier extension para cambiarla
     * @return array El arreglo asociativo con la ultima información de
     * movimiento
     */

    public static function processFile($oFile, $sDestination = NULL,
            $originalName = TRUE, $move = TRUE, $originalExtension = TRUE)
    {
        if (!Mekayotl_tools_Security::hasSession()) {
            return 'Error: No login';
        }
        $sDestination = self::$directory . $sDestination;
        $aName = pathinfo($oFile['name']);
        if ($originalExtension === TRUE) {
            $sExtention = strtolower($aName['extension']);
        } else {
            $sExtention = $originalExtension;
        }
        self::$last['original'] = $oFile;
        if ($originalName === TRUE) {
            self::$last['path'] = $sDestination . '/' . $oFile['name'];
        } else {
            if ($originalExtension===FALSE) {
                self::$last['path'] = $sDestination . '/'
                        . strtolower($originalName);
            } else {
                self::$last['path'] = $sDestination . '/'
                    . strtolower($originalName . '.' . $sExtention);
            }
        }
        if (is_file(self::$last['path'])) {
            @unlink(self::$last['path']);
        }
        if (!is_dir(dirname(self::$last['path']))) {
            Mekayotl::generatePath(self::$last['path']);
        }
        if ($move) {
            self::$last['upload'] = move_uploaded_file(
                $oFile['tmp_name'],
                self::$last['path']);
        } else {
            self::$last['upload'] = copy(
                $oFile['tmp_name'],
                self::$last['path']);
        }
        self::$history[] = self::$last;
        $logUpload = json_encode(self::$last['upload']) . ' '
                . json_encode(self::$last['original']) . " "
                . (($move) ? 'move' : 'copy') . " to " . self::$last['path'];
        return self::$last;
    }

    /**#@-*/
    /**
     * Crea un objeto independiente para automatizar el movimiento de archivos
     * con parámetros
     * @param boolean $bAuto Si debe procesar automáticamente los archivos
     * @access public
     */

    public function __construct($bAuto)
    {
        if (isset($_FILES) && $bAuto) {
            foreach ($_FILES as $field => $value) {
                self::processFile($value, FALSE, $_REQUEST['name'], $_REQUEST);
            }
        }
    }

}

if (!MEDIA_PATH) {
    Mekayotl_tools_Upload::$directory = PUBLIC_PATH . '/medias';
    Mekayotl::generatePath(Mekayotl_tools_Upload::$directory);
} else {
    Mekayotl_tools_Upload::$directory = MEDIA_PATH;
}
