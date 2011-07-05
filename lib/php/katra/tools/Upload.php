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
 * Cambia el tamaño maximo para carga de archivos
 */
ini_set('upload_max_filesize','2000M');
/**
 * Esta clace le da un proceso a las imagen original con respecto al URL de salida.
 *
 * @package	Katra
 * @subpackage	tools
 */
class Upload {
	/**#@+
	 * @access	private
	 * @static
	 */
	/**
	 * Contiene el directrio base para archivos
	 * @var	string
	 */
	public static $directory;
	/**
	 * Contiene la ultima manipulacion de archivos
	 * @var	array	Un arraasociativo
	 */
	public static $last = array();
	/**
	 * Contiene un historial de los movimientos de archivos
	 * @var	array
	 */
	public static $history = array();
	/**
	 * Manipula el destino de un archivo cargado
	 * @param	array	$oFile	El array associativo con informacion del archivo cargado
	 * @param	string	$sDestination	El destino del archivo
	 * @param	boolean|string	$originalName	Si mantiene el nombre original o string con el nuevo nombre
	 * @param	boolean	$move	Si el archivo tiene que ser movido o copiado
	 * @return	array	El array asociativo con la ultima informacion de movimiento
	 */
	public static function processFile($oFile, $sDestination, $originalName = true, $move = true){
		if(!\core\tools\Security::hasSession())
			return 'Error: No login';
		$aName = split("\.", $oFile['name']);
		$sExtention = strtolower(array_pop($aName));
		self::$last['original'] = $oFile;
		\core\Katra::generatePath($sDestination);
		if($originalName === true)
			self::$last['path'] = $sDestination . $oFile['name'];
		else
			self::$last['path'] = $sDestination . strtolower($originalName . '.' . $sExtention);
		self::$last['path'] = strtolower(self::$last['path']);
		if($move)
			self::$last['upload'] = move_uploaded_file($oFile['tmp_name'], self::$last['path']);
		else
			self::$last['upload'] = copy($oFile['tmp_name'], self::$last['path']);
		self::$history[] = self::$last;
		$logUpload = json_encode(self::$last['upload'])
						. ' '
						. json_encode(self::$last['original'])
						. " "
						. (($move)?'move':'copy')
						. " to " . self::$last['path'];
		\core\Katra::addToLogFile('upload', $logUpload);
		return self::$history[count(self::$history)-1];
	}
	/**#@-*/
	/**
	 * Crea un objeto independiente para automatizar el movimiento de archivos con paramatros
	 * @param	boolean	$bAuto	Si debe procesar automaticmane te los archivos
	 * @access	public
	 */
	public function __construct($bAuto){
		if(isset($_FILES) && $bAuto){
			foreach($_FILES as $field => $value)
				self::processFile($value, false, $_REQUEST['name'], $_REQUEST);
		}
	}
}
if(!MEDIA_PATH){
	Upload::$directory = PUBLIC_PATH . '/medias';
	\core\Katra::generatePath(Upload::$directory);
}else
	Upload::$directory = MEDIA_PATH;
