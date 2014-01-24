<?php
//namespace core\tools;
/**
 * Herramienta para manipulación de imágenes.
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
 * Esta clase le da un proceso a las imagen original con respecto al URL de
 * salida.
 *
 * @package Mekayotl
 * @subpackage tools
 */
class Mekayotl_tools_Image
{
    /**#@+
     * @access private
     */

    /**
     * El recurso de la imagen original.
     * @var resource
     */

    private $_rsOrginal = NULL;
    /**
     * El recurso de la imagen destino
     * @var resource
     */
    private $_rsOutput = NULL;
    /**
     * Contiene datos dinámicos del objeto
     * @var array
     */
    private $_dinamics = array(
            'originalPath' => '',
            'outputPath' => '',
            'bgColor' => '#FFFFFF'
    );
    /**#@-*/
    /**#@+
     * @access public
     */

    /**
     * Contiene el directorio base para archivos
     * @var string
     */

    public static $directory;
    /**
     * Formato de salida de la imagen
     * @var string jpg, gif, png, bmp
     */
    public $outputType = 'png';
    /**
     * Tamaño predeterminado de la imagen destino
     * @var array Ancho por alto
     */
    public $size = array(
            80,
            60
    );
    /**
     * Tratamiento a la imagen pa su contenido
     * @var string showall, noborder, exactfit, noscale
     */
    public $scale = 'showall';
    /**
     * Alineación de la imagen
     * @var string Verticales: t=top, m=middle, b=bottom. Horizontales: l=left,
     * c=center, r=right
     */
    public $alignment = 'mc';
    /**
     * Tipo de background
     * @var string crop, alpha, color
     */
    public $background = 'color';
    /**
     * Calidad de la imagen
     * @var integer JPG: 0-100, GIF: -, PNG: 0-9, BMP: -
     */
    public $quality = 90;
    /**
     * Modificaciones extras aplicadas
     * @var array
     */
    public $extra = array();
    /**
     * Registro de actividades
     * @var array
     */
    public $log = array();
    /**
     * Información del archivo
     * @var array
     */
    public $fileInfo = array();
    /**
     * Construye el objeto que manipula la imagen.
     * @param string $input Ubicación de a la imagen original
     * @param string $output Ubicación para la imagen procesada
     */

    public function __construct($input = NULL, $output = NULL)
    {
        ini_set('upload_max_filesize', '2000M');
        ini_set('memory_limit', '256M');
        $this->originalPath = $input;
        $this->outputPath = $output;
    }

    /**
     * Destruye los recursos utilizados
     */

    public function __destruct()
    {
        imagedestroy($this->_rsOrginal);
        imagedestroy($this->_rsOutput);
    }

    /**
     * Devuelve las variables dinámicas guardadas
     * @param string $dinamic
     * @return mixed
     */

    public function __get($dinamic)
    {
        if (isset($this->_dinamics[$dinamic])) {
            switch ($dinamic) {
                case 'bgColor':
                    if (!is_array($this->_dinamics[$dinamic])) {
                        $val = str_replace('#', '', $this->_dinamics[$dinamic]);
                        $val = str_split($val, 2);
                        $this->_dinamics[$dinamic] = array(
                                'R' => hexdec($val[0]),
                                'G' => hexdec($val[1]),
                                'B' => hexdec($val[2]),
                                'A' => intval(hexdec($val[3]) / 2)
                        );
                    }
                    break;
            }
            return $this->_dinamics[$dinamic];
        } else
            return NULL;
    }

    /**
     * Establece las variables dinámicas
     * @param string $dinamic
     * @param mixed $val
     */

    public function __set($dinamic, $val)
    {
        $chage = $this->_dinamics[$dinamic] != $val;
        $this->_dinamics[$dinamic] = $val;
        switch ($dinamic) {
            case 'originalPath':
                $this->setOriginal($val);
                break;
            case 'outputPath':
                $this->setOutPut($val);
                break;
            case 'bgColor':
                if (!is_array($this->_dinamics[$dinamic])) {
                    $val = str_replace('#', '', $this->_dinamics[$dinamic]);
                    $val = str_split($val, 2);
                    $this->_dinamics[$dinamic] = array(
                            'R' => hexdec($val[0]),
                            'G' => hexdec($val[1]),
                            'B' => hexdec($val[2]),
                            'A' => intval(hexdec($val[3]) / 2)
                    );
                }
                break;
        }
        if ($chage && !is_null($this->originalPath)
                && !is_null($this->outputPath)) {
            $this->_generateResource();
        }
    }

    /**
     * Comprueba la existencia de una variable dinámica
     * @param string $dinamic El nombre de la variable
     * @return boolean
     */

    public function __isset($dinamic)
    {
        return isset($this->_dinamics[$dinamic]);
    }

    /**
     * Elimina una variable dinámica
     * @param string $dinamic El nombre de la variable
     */

    public function __unset($dinamic)
    {
        unset($this->_dinamics[$dinamic]);
    }

    /**
     * Analisa el nombre para asignar las propiedades a la clase.
     * @param string $name
     */

    private function _analyzeName($name)
    {
        $tmp = explode('_', $name);
        array_shift($tmp);
        foreach ($tmp as $key => $value) {
            if (substr_count($value, 'x') == 1 && $key == 0) {
                $this->size = explode('x', $value);
            } elseif (in_array($value,
                    array(
                            'showall',
                            'noborder',
                            'exactfit',
                            'noscale'
                    )) && $key <= 1) {
                $this->scale = $value;
            } elseif (strlen($value) == 2 && $key <= 2) {
                $this->alignment = str_split($value);
            } elseif (in_array($value,
                    array(
                            'crop',
                            'alpha',
                            'color'
                    )) && $key <= 3) {
                $this->background = $value;
            } elseif (strlen($value) == 8 && $key <= 4) {
                $this->bgColor = $value;
            } elseif (strlen($value) <= 3 && intval($value) <= 255 && $key <= 5) {
                $value = intval($value);
                $this->quality = $value;
            } elseif ($key >= 5) {
                $this->extra[] = $value;
            }
        }
    }

    /**
     * Establece el recurso orginal.
     * @param string $original
     */

    public function setOriginal($original)
    {
        if (is_file($original)) {
            $size = getimagesize($original);
            $this->fileInfo['FileType'] = $size[2];
            $this->fileInfo['COMPUTED']['Width'] = $size[0];
            $this->fileInfo['COMPUTED']['Height'] = $size[1];
            switch ($this->fileInfo['FileType']) {
                case IMAGETYPE_GIF:
                    $this->_rsOrginal = imagecreatefromgif($original);
                    break;
                case IMAGETYPE_JPEG:
                    $this->_rsOrginal = imagecreatefromjpeg($original);
                    break;
                case IMAGETYPE_PNG:
                    $this->_rsOrginal = imagecreatefrompng($original);
                    break;
                case IMAGETYPE_BMP:
                    $this->_rsOrginal = imagecreatefromwbmp($original);
                    break;
            }
        }
    }
    /**
     * Establece las variables de salida
     * @param string $output Un texto de configuración de salida
     */

    public function setOutPut($output)
    {
        if (is_null($output)) {
            return FALSE;
        }
        $outputPathInfo = pathinfo($output);
        if (in_array($outputPathInfo['extension'],
                array(
                        'jpg',
                        'gif',
                        'png',
                        'bmp'
                ))) {
            $this->outputType = $outputPathInfo['extension'];
        }
        $this->_analyzeName($outputPathInfo['filename']);
        return $this;
    }

    /**
     * Calcula los tamaños de escala a la imagen.
     * @param array $sizes
     */

    private function _calculateScale(&$sizes)
    {
        switch ($this->scale) {
            case 'exactfit':
                $sizes['tranformation']['dst'] = array_merge(
                        array(
                                'x' => 0,
                                'y' => 0
                        ), $sizes['output']);
                break;
            case 'noborder':
                if ($sizes['output']['ratio'] < $sizes['file']['ratio']) {
                    $sizes['tranformation']['dst']['w'] = intval(
                            $sizes['output']['h'] * $sizes['file']['ratio']);
                    $sizes['tranformation']['dst']['h'] = $sizes['output']['h'];
                } else {
                    $sizes['tranformation']['dst']['h'] = intval(
                            $sizes['output']['w'] / $sizes['file']['ratio']);
                    $sizes['tranformation']['dst']['w'] = $sizes['output']['w'];
                }
                break;
            case 'showall':
                if ($sizes['output']['ratio'] < $sizes['file']['ratio']) {
                    $sizes['tranformation']['dst']['h'] = intval(
                            $sizes['output']['w'] / $sizes['file']['ratio']);
                    $sizes['tranformation']['dst']['w'] = $sizes['output']['w'];
                } else {
                    $sizes['tranformation']['dst']['w'] = intval(
                            $sizes['output']['h'] * $sizes['file']['ratio']);
                    $sizes['tranformation']['dst']['h'] = $sizes['output']['h'];
                }
                break;
            case 'noscale':
            default:
        }
        if ($this->background == 'crop') {
            $sizes['output']['w'] = min($sizes['tranformation']['dst']['w'],
                    $sizes['output']['w']);
            $sizes['output']['h'] = min($sizes['tranformation']['dst']['h'],
                    $sizes['output']['h']);
        }
    }

    /**
     * Calcula la posicion del original enla salida.
     * @param array $sizes
     */

    private function _calculatePosition(&$sizes)
    {
        // Posición Vertical
        switch ($this->alignment[0]) {
            case 'b':
                $sizes['tranformation']['dst']['y'] = $sizes['output']['h']
                        - $sizes['tranformation']['dst']['h'];
                break;
            case 'm':
                $sizes['tranformation']['dst']['y'] = intval(
                        ($sizes['output']['h']
                                - $sizes['tranformation']['dst']['h']) / 2);
                break;
            case 't':
            default:
        }
        // Posición Horizontal
        switch ($this->alignment[1]) {
            case 'r':
                $sizes['tranformation']['dst']['x'] = $sizes['output']['w']
                        - $sizes['tranformation']['dst']['w'];
                break;
            case 'c':
                $sizes['tranformation']['dst']['x'] = intval(
                        ($sizes['output']['w']
                                - $sizes['tranformation']['dst']['w']) / 2);
                break;
            case 'l':
            default:
                //$params['destination']['x']=0;
        }
    }

    /**
     * Procesa la información para el tamaño de salida
     * @return string El tamaño de salida
     */

    public function scaleInfo()
    {
        $sizes = array();
        $sizes['output'] = array(
                'w' => $this->size[0],
                'h' => $this->size[1]
        );
        $sizes['file'] = array(
                'w' => $this->fileInfo['COMPUTED']['Width'],
                'h' => $this->fileInfo['COMPUTED']['Height']
        );
        $sizes['tranformation']['src'] = array_merge(
                array(
                        'x' => 0,
                        'y' => 0
                ), $sizes['file']);
        $sizes['tranformation']['dst'] = $sizes['tranformation']['src'];
        $sizes['output']['ratio'] = $sizes['output']['w']
                / $sizes['output']['h'];
        $sizes['file']['ratio'] = $sizes['file']['w'] / $sizes['file']['h'];
        // Escalado
        $this->_calculateScale($sizes);
        // Posicion
        $this->_calculatePosition($sizes);
        return $sizes;
    }

    /**
     * Establece los filtros a aplicar en la imagen
     * @param resource $img El recurso de una imagen
     * @return This
     */

    public function setFilter($img)
    {
        switch ($this->background) {
            case 'alpha':
            //imagefill($this->_rsOutput,0,0,IMG_COLOR_TRANSPARENT);
                imagecolortransparent($img, imagecolorat($img, 0, 0));
                if (function_exists('imagefilter')) {
                    imagefilter($img, IMG_FILTER_COLORIZE, $this->bgColor['R'],
                            $this->bgColor['G'], $this->bgColor['B'],
                            $this->bgColor['A']);
                }
                break;
            case 'color':
            default:
                imagefill($img, 0, 0,
                        imagecolorallocate($img, $this->bgColor['R'],
                                $this->bgColor['G'], $this->bgColor['B']));
        }
        return $this;
    }

    /**
     * Agrega extras a la manipulación de la imagen
     * @param resource $source El recurso de una imagen
     * @param string $ext Los extras a implementar en la imagen; sepia, colorize,
     * blackwhite, grayscale, newgrayscale
     * @return This
     */

    public function addExtra($source, $ext = '')
    {
        $totalColors = imagecolorstotal($source);
        if (imageisTRUEcolor($source)) {
            $this->log[] = 'Es TRUE color, total de colores : ' . $totalColors;
            $this->log[] = imageTRUEcolortopalette($source, TRUE, 300);
            $totalColors = imagecolorstotal($source);
            $this->log[] = 'Paleta, total de colores : ' . $totalColors;
        }
        if (in_array($ext,
                array(
                        'sepia',
                        'colorize',
                        'blackwhite',
                        'grayscale',
                        'newgrayscale'
                ))) {
            for ($i = 0; $i < $totalColors; $i++) {
                $index = imagecolorsforindex($source, $i);
                switch ($ext) {
                    case 'sepia':
                        $red = ($index["red"] * 0.393 + $index["green"] * 0.769
                                + $index["blue"] * 0.189) / 1.351;
                        $green = ($index["red"] * 0.349
                                + $index["green"] * 0.686
                                + $index["blue"] * 0.168) / 1.203;
                        $blue = ($index["red"] * 0.272
                                + $index["green"] * 0.534
                                + $index["blue"] * 0.131) / 2.140;
                        break;
                    case 'colorize':
                        $red = $this->bgColor['R'] * $index['red'] / 256;
                        $green = $this->bgColor['G'] * $index['green'] / 256;
                        $blue = $this->bgColor['B'] * $index['blue'] / 256;
                        break;
                    case 'blackwhite':
                        array_pop($index);
                        $red = (array_sum($index) > 382) ? 255 : 0;
                        $green = $red;
                        $blue = $red;
                        break;
                    case 'grayscale':
                        $red = round(
                                (0.299 * $index['red'])
                                        + (0.587 * $index['green'])
                                        + (0.114 * $index['blue']));
                        $green = $red;
                        $blue = $red;
                        break;
                    case 'newgrayscale':
                        $red = round(
                                (0.2125 * $index['red'])
                                        + (0.7154 * $index['green'])
                                        + (0.0721 * $index['blue']));
                        $green = $red;
                        $blue = $red;
                        break;
                }
            }
            imagecolorset($source, $i, $red, $green, $blue);
        }
        //$this->setFilter($source);
        return $this;
    }

    /**
     * Genera el archivo de salida
     * @return This
     */

    private function _generateResource()
    {
        $scale = $this->scaleInfo();
        $this->_rsOutput = imagecreateTRUEcolor($scale['output']['w'],
                $scale['output']['h']);
        $this->setFilter($this->_rsOutput);
        $this->log[] = imagecopyresampled($this->_rsOutput, $this->_rsOrginal,
                $scale['tranformation']['dst']['x'],
                $scale['tranformation']['dst']['y'],
                $scale['tranformation']['src']['x'],
                $scale['tranformation']['src']['y'],
                $scale['tranformation']['dst']['w'],
                $scale['tranformation']['dst']['h'],
                $scale['tranformation']['src']['w'],
                $scale['tranformation']['src']['h']);
        foreach ($this->extra as $sExtras) {
            $this->addExtra($this->_rsOutput, $sExtras);
        }
        return $this;
    }

    /**
     * Procesa el archivo para su salida al navegador
     * @return This
     */

    public function createFile($sendFile = FALSE)
    {
        if ($sendFile) {
            header("Mekayotl-Image: Generate");
        }
        Mekayotl::generatePath($this->outputPath);
        if (!in_array($this->outputType,
                array(
                        'jpg',
                        'gif',
                        'png',
                        'bmp'
                ))) {
            $this->outputType = 'png';
        }
        // Coloca el archivo
        $outputType = $this->outputType;
        $functionSuffix = str_replace(
                array(
                        'bmp',
                        'jpg'
                ),
                array(
                        'wbmp',
                        'jpeg'
                ), $outputType);
        $this->log[] = 'image' . $functionSuffix;
        $params = array(
                $this->_rsOutput,
                $this->outputPath
        );
        switch ($outputType) {
            case '´gif';
                $params[] = intval($this->quality);
                break;
            case 'jpg':
                if ($this->quality > 100) {
                    $this->quality = intval(($this->quality / 255) * 100);
                }
                $params[] = $this->quality;
                break;
            case 'png':
            default:
                $this->log[] = 'Calidad actual: ' . $this->quality;
                if ($this->quality > 100) {
                    $this->quality = intval(($this->quality / 255) * 100);
                }
                $this->log[] = 'Calidad actual: ' . $this->quality;
                if ($this->quality > 9) {
                    $this->quality = max(intval($this->quality / 10) - 1, 0);
                }
                $this->log[] = 'Calidad actual: ' . $this->quality;
                $params[] = $this->quality;
        }
        $this->log[] = call_user_func_array('image' . $functionSuffix, $params);
        if ($sendFile) {
            $outputType = $outputType == 'jpg' ? 'jpeg' : $outputType;
            header("Content-type: image/" . $outputType);
            $fromFile = call_user_func('imagecreatefrom' . $functionSuffix,
                    $this->outputPath);
            $this->log[] = call_user_func('image' . $functionSuffix, $fromFile);
        }
        return $this;
    }

    /**
     * Genera un archivo temporal para evitar duplicidades de generaciones.
     * @return This
     */

    public function createTempImage()
    {
        Mekayotl::generatePath($this->outputPath);
        // Coloca el archivo temporal
        $tmpImage = imagecreateTRUEcolor($this->size[0], $this->size[1]);
        switch ($this->outputType) {
            case 'bmp':
                $this->log[] = imagewbmp($tmpImage, '.' . $this->outputPath);
                break;
            case 'gif':
                $this->log[] = imagegif($tmpImage, '.' . $this->outputPath);
                break;
            case 'jpg':
                $this->log[] = imagejpeg($tmpImage, '.' . $this->outputPath,
                        $this->quality);
                break;
            case 'png':
            default:
                $this->log[] = imagepng($tmpImage, '.' . $this->outputPath,
                        $this->quality);
        }
        return $this;
    }

    /**
     * Transforma un achivo de medios utilizando cache.
     * @param string $origin
     * @param string $destination
     */

    public static function mediaTransform($origin, $destination)
    {
        $origin = self::$directory . $origin;
        $destination = self::$directory . $destination;
        $return = new Mekayotl_tools_Image($origin, $destination);
        if (is_file($origin) && is_file($destination)
                && filemtime($origin) < filemtime($destination)) {
            return $return;
        }
        return $return->createFile();
    }

    /**#@-*/
}
if (!MEDIA_PATH) {
    Mekayotl_tools_Image::$directory = PUBLIC_PATH . '/medias';
    Mekayotl::generatePath(Mekayotl_tools_Image::$directory);
} else {
    Mekayotl_tools_Image::$directory = MEDIA_PATH;
}
