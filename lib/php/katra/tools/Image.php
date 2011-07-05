<?php
namespace core\tools;
/**
 * Herramienta para manipulacion de imagenes.
 *
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://alograg.me}
 * @package	Katra
 * @since	2011-03-01
 * @subpackage	tools
 * @version	$Id$
 */

/**
 * Esta clace le da un proceso a las imagen original con respecto al URL de salida.
 *
 * @package	Katra
 * @subpackage	tools
 */
class Image {
	/**#@+
	 * @access	private
	 */
	/**
	 * El recurso de la imagen original.
	 * @var	resource
	 */
	private $rsOrginal = NULL;
	/**
	 * El recurso de la imagen destino
	 * @var	resource
	 */
	private $rsOutput = NULL;
	/**
	 * Contiene datos dinamicos del objeto
	 * @var	array
	 */
	private $dinamics = array(
							'original_path' => '',
							'output_path' => '',
							'bgColor' => '#FFFFFF'
						);
	/**#@-*/
	/**#@+
	 * @access	public
	 */
	/**
	 * Formato de salida de la imagen
	 * @var	string	jpg, gif, png, bmp
	 */
	public $output_type = 'png';
	/**
	 * Tamaño predeterminado de la imagen destino
	 * @var	array	Ancho por alto
	 */
	public $size = array(80,60);
	/**
	 * Tratamiento a la imagen pa su contenido
	 * @var	string	showall, noborder, exactfit, noscale
	 */
	public $scale = 'showall';
	/**
	 * Aliniacion de la imagen
	 * @var	string	Verticales: t=top, m=middle, b=bottom. Horizontales: l=left, c=center, r=right
	 */
	public $alignment = 'mc';
	/**
	 * Tipo de background
	 * @var	string	crop, alpha, color
	 */
	public $background = 'color';
	/**
	 * Calidad de la imagen
	 * @var	integer	JPG: 0-100, GIF: -, PNG: 0-9, BMP: -
	 */
	public $quality = 90;
	/**
	 * Modificaciones extras aplicadas
	 * @var	array
	 */
	public $extra = array();
	/**
	 * Registro de actividades
	 * @var	array
	 */
	public $log = array();
	/**
	 * Informacion del archivo
	 * @var	array
	 */
	public $fileInfo = array();
	/**
	 * Contrulle el objeto que manipula la imagen.
	 * @param	string	$input	Ubicacion de a la imagen original
	 * @param	string	$output	Ubicacion para la imagen procesada
	 */
	public function __construct($input=NULL, $output=NULL) {
		ini_set('upload_max_filesize','2000M');
		ini_set('memory_limit','256M');
		$this->original_path = $input;
		$this->output_path = $output;
		if($this->original_path && $this->output_path)
			$this->createOutput();
		$this->outputFile();
	}
	/**
	 *	Destrulle los recursos utilizados
	 */
	public function __destruct(){
		imagedestroy($this->rsOrginal);
		imagedestroy($this->rsOutput);
	}
	/**
	 * Debuelve las variables dinamicas guardadas
	 * @param	string	$dinamic
	 * @return	mixede
	 */
	public function __get($dinamic){
		if (isset($this->dinamics[$dinamic])){
			switch($dinamic){
				case 'bgColor':
					if(!is_array($this->dinamics[$dinamic])){
						$val=str_replace('#','',$this->dinamics[$dinamic]);
						$val=str_split($val,2);
						$this->dinamics[$dinamic]=array(
							'R'=>hexdec($val[0]),
							'G'=>hexdec($val[1]),
							'B'=>hexdec($val[2]),
							'A'=>intval(hexdec($val[3])/2)
						);
					}
					break;
			}
			return $this->dinamics[$dinamic];
		}else
			return NULL;
	}
	/**
	 * Establece las variables dinamicas
	 * @param	string	$dinamic
	 * @param	mixed	$val
	 */
	public function __set($dinamic, $val){
		$this->dinamics[$dinamic] = $val;
		switch($dinamic){
			case 'original_path':
				if($val){
					if(function_exists('exif_read_data'))
						$this->fileInfo = exif_read_data($val);
					if(!$this->fileInfo){
						$size=getimagesize($val);
						$this->fileInfo['FileType'] = $size[2];
						$this->fileInfo['COMPUTED']['Width'] = $size[0];
						$this->fileInfo['COMPUTED']['Height'] = $size[1];
					}
					switch($this->fileInfo['FileType']){
						case IMAGETYPE_GIF:
							$this->rsOrginal = imagecreatefromgif($val);
							break;
						case IMAGETYPE_JPEG:
							$this->rsOrginal = imagecreatefromjpeg($val);
							break;
						case IMAGETYPE_PNG:
							$this->rsOrginal = imagecreatefrompng($val);
							break;
						case IMAGETYPE_BMP:
							$this->rsOrginal = imagecreatefromwbmp($val);
							break;
					}
				}
				break;
			case 'output_path':
				$this->setOutPut($val);
				break;
			case 'bgColor':
				if(!is_array($this->dinamics[$dinamic])){
					$val = str_replace('#', '', $this->dinamics[$dinamic]);
					$val = str_split($val,2);
					$this->dinamics[$dinamic] = array(
												'R' => hexdec($val[0]),
												'G' => hexdec($val[1]),
												'B' => hexdec($val[2]),
												'A' => intval(hexdec($val[3])/2)
											);
				}
				break;
		}
	}
	/**
	 * Comprueba la existencia de una variable dinamica
	 * @param	string	$dinamic	El nombre de la variable
	 * @return	boolean
	 */
	public function __isset($dinamic){
		return isset($this->dinamics[$dinamic]);
	}
	/**
	 * Elimina una variable dinamica
	 * @param	string	$dinamic	El nombre de la variable
	 */
	public function __unset($dinamic){
		unset($this->dinamics[$dinamic]);
	}
	/**
	 * Establece las variables de salida
	 * @param	string	$output	Un texto de configuracion de salida
	 */
	public function setOutPut($output){
		$tmp = split('\.',$output);
		if(in_array($tmp[1], array('jpg','gif','png','bmp')))
			$this->output_type = $tmp[1];
		$tmp = split('_', array_pop(split('/', $tmp[0])));
		array_splice($tmp, 0, 3);
		foreach($tmp as $key => $value){
			if(substr_count($value,'x') == 1 && $key == 0)
				$this->size = split('x',$value);
			elseif(in_array($value,array('showall','noborder','exactfit','noscale'))&&$key<=1)
				$this->scale = $value;
			elseif(strlen($value) == 2 && $key <= 2)
				$this->alignment = str_split($value);
			elseif(in_array($value, array('crop', 'alpha', 'color')) && $key <= 3)
				$this->background = $value;
			elseif(strlen($value) == 8 && $key <= 4){
				$this->bgColor = $value;
			}elseif(strlen($value) <= 3 && intval($value) <= 255 && $key <=5){
				$value = intval($value);
				$this->quality = $value;
			}elseif($key >= 5)
				$this->extra[] = $value;
		}
		return $this;
	}
	/**
	 * Procesa la informacion para el tamaño de salida
	 * @return	string	El tamaño de salida
	 */
	public function scaleInfo(){
		$sizes = array();
		$sizes['output'] = array(
							'w' => $this->size[0],
							'h' => $this->size[1]
						);
		$sizes['file'] = array(
							'w' => $this->fileInfo['COMPUTED']['Width'],
							'h' => $this->fileInfo['COMPUTED']['Height']
						);
		$sizes['tranformation']['src'] = array_merge(array(
														'x' => 0,
														'y' => 0
												), $sizes['file']);
		$sizes['tranformation']['dst'] = $sizes['tranformation']['src'];
		$sizes['output']['ratio'] = $sizes['output']['w'] / $sizes['output']['h'];
		$sizes['file']['ratio'] = $sizes['file']['w'] / $sizes['file']['h'];
		// Escalado
		switch($this->scale){
			case 'exactfit':
				$sizes['tranformation']['dst'] = array_merge(array(
														'x'=>0,
														'y'=>0
													), $sizes['output']);
				break;
			case 'noborder':
				if($sizes['output']['ratio'] < $sizes['file']['ratio']){
					$sizes['tranformation']['dst']['w'] = intval($sizes['output']['h'] * $sizes['file']['ratio']);
					$sizes['tranformation']['dst']['h'] = $sizes['output']['h'];
				}else{
					$sizes['tranformation']['dst']['h'] = intval($sizes['output']['w'] / $sizes['file']['ratio']);
					$sizes['tranformation']['dst']['w'] = $sizes['output']['w'];
				}
				break;
			case 'showall':
				if($sizes['output']['ratio'] < $sizes['file']['ratio']){
					$sizes['tranformation']['dst']['h'] = intval($sizes['output']['w'] / $sizes['file']['ratio']);
					$sizes['tranformation']['dst']['w'] = $sizes['output']['w'];
				}else{
					$sizes['tranformation']['dst']['w'] = intval($sizes['output']['h'] * $sizes['file']['ratio']);
					$sizes['tranformation']['dst']['h'] = $sizes['output']['h'];
				}
				break;
			case 'noscale':
			default:
		}
		if($this->background == 'crop'){
			$sizes['output']['w'] = min($sizes['tranformation']['dst']['w'], $sizes['output']['w']);
			$sizes['output']['h'] = min($sizes['tranformation']['dst']['h'], $sizes['output']['h']);
		}
		// Possicion Vertical
		switch($this->alignment[0]){
			case 'b':
				$sizes['tranformation']['dst']['y'] = $sizes['output']['h'] - $sizes['tranformation']['dst']['h'];
				break;
			case 'm':
				$sizes['tranformation']['dst']['y'] = intval(($sizes['output']['h'] - $sizes['tranformation']['dst']['h'])/2);
				break;
			case 't':
			default:
		}
		// Possicion Horisontal
		switch($this->alignment[1]){
			case 'r':
				$sizes['tranformation']['dst']['x'] = $sizes['output']['w'] - $sizes['tranformation']['dst']['w'];
				break;
			case 'c':
				$sizes['tranformation']['dst']['x'] = intval(($sizes['output']['w'] - $sizes['tranformation']['dst']['w']) / 2);
				break;
			case 'l':
			default:
				//$params['destination']['x']=0;
		}
		if(Katra_DEBUG)
			header("Katra-Image-Debug: ".  var_export($sizes, true));
		return $sizes;
	}
	/**
	 * Establece los filtros a aplicar en la imagen
	 * @param	resource	$img	El recurso de una imagen
	 * @return	This
	 */
	public function setFilter($img){
		switch($this->background){
			case 'alpha':
				//imagefill($this->rsOutput,0,0,IMG_COLOR_TRANSPARENT);
				imagecolortransparent($img,imagecolorat($img,0,0));
				if(function_exists('imagefilter'))
					imagefilter($img,
						IMG_FILTER_COLORIZE,
						$this->bgColor['R'],
						$this->bgColor['G'],
						$this->bgColor['B'],
						$this->bgColor['A']);
				break;
			case 'color':
			default:
				imagefill($img,0,0,imagecolorallocate($img,
					$this->bgColor['R'],
					$this->bgColor['G'],
					$this->bgColor['B'])
				);
		}
		return $this;
	}
	/**
	 * Agrega extras a la manipulacion de la imagen
	 * @param	resource	$source	El recurso de una imagen
	 * @param	string	$ext	Los extras a implementar en la imagen; sepia, colorize, blackwhite, grayscale, newgrayscale
	 * @return	This
	 */
	public function addExtra($source, $ext = ''){
		$totalColors = imagecolorstotal($source);
		if(imageistruecolor($source)){
			$this->log[] = 'Es true color, total de colores : ' . $totalColors;
			$this->log[] = imagetruecolortopalette($source, true, 300);
			$totalColors = imagecolorstotal($source);
			$this->log[] = 'Paleta, total de colores : ' . $totalColors;
		}
		if(in_array($ext, array('sepia','colorize','blackwhite','grayscale','newgrayscale'))){
			for($i=0;$i<$totalColors;$i++){
				$index = imagecolorsforindex($source,$i);
				switch($ext){
					case 'sepia':
						$red = ($index["red"] * 0.393 + $index["green"] * 0.769 + $index["blue"] * 0.189) / 1.351;
						$green = ($index["red"] * 0.349 + $index["green"] * 0.686 + $index["blue"] * 0.168) / 1.203;
						$blue = ($index["red"] * 0.272 + $index["green"] * 0.534 + $index["blue"] * 0.131) / 2.140;
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
						$red = round((0.299 * $index['red']) + (0.587 * $index['green']) + (0.114 * $index['blue']));
						$green = $red;
						$blue = $red;
						break;
					case 'newgrayscale':
						$red = round((0.2125 * $index['red']) + (0.7154 * $index['green']) + (0.0721 * $index['blue']));
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
	 * @return	This
	 */
	public function createOutput(){
		$scale = $this->scaleInfo();
		$this->rsOutput = imagecreatetruecolor($scale['output']['w'],$scale['output']['h']);
		$this->setFilter($this->rsOutput);
		\core\Katra::generatePath($this->output_path, true);
		$this->log[] = imagecopyresampled($this->rsOutput,
							$this->rsOrginal,
							$scale['tranformation']['dst']['x'],
							$scale['tranformation']['dst']['y'],
							$scale['tranformation']['src']['x'],
							$scale['tranformation']['src']['y'],
							$scale['tranformation']['dst']['w'],
							$scale['tranformation']['dst']['h'],
							$scale['tranformation']['src']['w'],
							$scale['tranformation']['src']['h']
						);
		foreach($this->extra as $sExtras)
			$this->addExtra($this->rsOutput,$sExtras);
		return $this;
	}
	/**
	 * Procesa el archivo para su salida al navegador
	 * @return	This
	 */
	public function outputFile(){
		header("Katra-Image: Generate");
		// Coloca el archivo
		switch($this->output_type){
			case 'bmp':
				header("Content-type: image/bmp");
				//$this->log[] = imagewbmp($this->rsOutput);
				$this->log[] = imagewbmp($this->rsOutput, '.' . $this->output_path);
				if(!isset($_GET['debug']))
					$this->log[] = imagewbmp(imagecreatefromwbmp('.' . $this->output_path));
				break;
			case 'gif':
				header("Content-type: image/gif");
				//$this->log[]=imagegif($this->rsOutput);
				imagetruecolortopalette($this->rsOutput, false, intval($this->quality));
				$this->log[]=imagegif($this->rsOutput, '.' . $this->output_path);
				if(!isset($_GET['debug']))
					$this->log[] = imagegif(imagecreatefromgif('.' . $this->output_path));
				break;
			case 'jpg':
				header("Content-type: image/jpeg");
				//$this->log[] = imagejpeg($this->rsOutput);
				if($this->quality>100)
					$this->quality = intval(($this->quality / 255) * 100);
				$this->log[] = 'Calidad actual: ' . $this->quality;
				$this->log[] = imagejpeg($this->rsOutput, '.' . $this->output_path, $this->quality);
				if(!isset($_GET['debug']))
					$this->log[] = imagejpeg(imagecreatefromjpeg('.' . $this->output_path));
				break;
			case 'png':
			default:
				header("Content-type: image/png");
				//$this->log[] = imagepng($this->rsOutput);
				$this->log[] = 'Calidad actual: ' . $this->quality;
				if($this->quality > 100)
					$this->quality = intval(($this->quality / 255) * 100);
				$this->log[] = 'Calidad actual: ' . $this->quality;
				if($this->quality > 9)
					$this->quality = max(intval($this->quality / 10) - 1 , 0);
				$this->log[] = 'Calidad actual: ' . $this->quality;
				$this->log[] = imagepng($this->rsOutput, '.' . $this->output_path, $this->quality);
				if(!isset($_GET['debug']))
					$this->log[] = imagepng( imagecreatefrompng('.' . $this->output_path));
		}
		return $this;
	}
	/**
	 * Genera un archivo temporal para evitar duplicidades de generaciones.
	 * @return	This
	 */
	public function tmpImage(){
		\core\Katra::generatePath($this->output_path, true);
		// Coloca el archivo temporal
		$tmpImage = imagecreatetruecolor($this->size[0], $this->size[1]);
		switch($this->output_type){
			case 'bmp':
				$this->log[] = imagewbmp($tmpImage, '.' . $this->output_path);
				break;
			case 'gif':
				$this->log[] = imagegif($tmpImage, '.' . $this->output_path);
				break;
			case 'jpg':
				$this->log[] = imagejpeg($tmpImage, '.' . $this->output_path, $this->quality);
				break;
			case 'png':
			default:
				$this->log[] = imagepng($tmpImage, '.' . $this->output_path, $this->quality);
		}
		return $this;
	}
	/**#@-*/
}