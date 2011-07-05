<?php
namespace core;
/**
 * Clace principal del framework
 *
 * Esta clace se encaga de hacer varios procesos internos.
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://alograg.me}
 * @package	Katra
 * @since	2011-03-01
 * @subpackage	core
 * @version	$Id$
 */

/**
 * La clace principal
 *
 * Esta clace realiza procesos internos y es el concentrador de funciones globales del framework.
 * @package	Katra
 * @subpackage	core
 */
class Katra {
	/**#@+
	 * @static
	 * @access	public
	 */
	/**
	 * Contiene todas las variables a rastrear
	 * @var array
	 */
	private static $_debug = array();
	/**
	 * Variable para rastrear el tiempo de inicio
	 * @var integer inicio de un timer
	 * @access	private
	 * uses	Katra::startTimer() Es utilizado para guardar el inicio
	 * uses	Katra::stopTimer() Es utilizado para el calculo del tiempo transcurrido
	 */
	private static $_starTime = null;
	/**
	 * Inicia un timer de rastreo
	 */
	public static function startTimer(){
		self::$_starTime = microtime(true);
	}
	/**
	 * Finalisa el timer de rastreo
	 * @return	integer	El micro-tiempo transcurrido desde la llamada de Katra::startTimer()
	 */
	public static function stopTimer() {
		$start = self::$_starTime;
		$end = microtime(true);
		$return = round(($end-$start), 2);
		self::$_starTime = null;
		return $return;
	}
	/**
	 * Agrega un string al rastreo de variables
	 * @param	mixed	$v	Variable a agregar para rastrear
	 */
	public static function trace($v){
		if(KATRA_DEBUG){
			self::$_debug[] = $v;
			if(class_exists('NetDebug', false))
				NetDebug::trace($v);
		}
	}
	/**
	 * Da salida a las variables agregadas al rastreo
	 */
	public static function printTrace(){
		if(KATRA_DEBUG)
			var_dump(self::$_debug);
	}
	/**
	 * Agrega un string al rastreo de variables
	 * @param	string	$file	Nombre del log. Default: log
	 * @param	string	$line	Texto a agregar. Default: none
	 * @param	string	$trackFormat	Formato de rastreo. Default: Ymd
	 */
	public static function addToLogFile($file = 'log', $line = 'None', $trackFormat = "Ymd"){
		if(KATRA_DEBUG){
			self::generatePath(LOGS_PATH);
			if(is_dir(LOGS_PATH)){
				$filePath = LOGS_PATH . $file . '.' . date($trackFormat) . '.log';
				$fh = fopen($filePath, 'a');
				fwrite($fh, '['.date('Y-m-d H:i:s').'] ' . $line . "\r\n");
				fclose($fh);
			}
		};
	}
	/**
	 * Regreza el tipo sistema operativo del servidor
	 * @return	string	El nombre del Sistema operativo
	 */
	public static function getOS(){
		exec('cat /etc/issue', $versionLinux);
		exec('ver', $versionWindows);
		$versionLinux = substr_count(implode("\n", $versionLinux), 'Linux');
		$versionWindows = substr_count(implode("\n", $versionWindows), 'Windows');
		$OS = 'unknow';
		$OS = ($versionLinux>0) ? 'Linux' : $OS;
		$OS = ($versionWindows>0) ? 'Windows' : $OS;
		return $OS;
	}
	/**
	 * Regreza los lenguajes del navegador
	 * @return	array	Los lenguajes del browser
	 */
	public static function getBrowserLang(){
		$aLang = split(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		foreach($aLang as $key => $sLang){
			$sLang[$key] = str_replace('-', '_', $sLang);
		}
		return $aLang;
	}
	/**
	 * Interpreta un string para devolvelo como la variable
	 * @param	string	$data	El string a convertir
	 * @param	string	$type	El tipo origen (json|int|float|complex:array|array|time|string). Default: string
	 * @return	mixed	El texto convertido
	 */
	public static function convertToPHP($data, $type = 'string'){
		$type = strtolower($type);
		switch($type){
			case 'json':
				$return = json_decode($data, true);
				break;
			case 'array':
				$return = split(',',$data);
				break;
			case 'int':
				$return = intval($data);
				break;
			case 'float':
				$return = floatval($data);
				break;
			case 'complex:array':
				$aTmp1 = split(',',$data);
				foreach($aTmp1 as $value){
					$aTmp2 = split(':',$value);
					$return[$aTmp2[1]] = $aTmp2[0];
				}
				break;
			case 'time':
			case 'string':
			case 'html':
			default:
				$return = $data;
		}
		if($data == 'null')
			$return = NULL;
		return $return;
	}
	/**
	 * Interpreta una variable para regrezarla la codificacion indicada
	 * @param	mixed	$data	La variable a convertir
	 * @param	string	$type	El tipo destino (json|int|float|complex:array|array|time|string). Default: string
	 * @return	string	El texto convertido
	 */
	public static function convertToString($data, $type = 'string'){
		$type = strtolower($type);
		switch($type){
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
				foreach($data as $key => $value)
					$data[$key] = $key . ':' . $value;
			case 'array':
				$return = implode(',', $data);
				break;
			case 'time':
				if(is_int($data))
					$data = date('Y-m-d H:i:s', intval($data));
			case 'string':
			case 'html':
			default:
				$return = $data;
		}
		if($data == NULL)
			$return = 'null';
		return $return;
	}
	/**
	 * Transforma un array asociativo en un string de XML
	 * @param	array	$Object	El array a convertis
	 * @param	boolean	$likeNodes	Indica si los arrays internos son tratados como nodos o propiedades
	 * @return	string	El string con el XML
	 */
	public static function arrayToXML($Object, $likeNodes=true){
		$aReturn = array();
		if(is_array($Object)){
			foreach($Object as $sTag => $vValue){
				$childs = array();
				$att = array();
				if(is_int($sTag))
					$sTag = 'i'.$sTag;
				if(substr_count($sTag, 'password') == 0){
					if(is_array($vValue)&&$likeNodes === true)
						$childs[] = self::objectToXML($vValue, $likeNodes);
					elseif(is_resource($vValue)){
						$childs = array();
						while($row = mysql_fetch_assoc($vValue)){
							if($likeNodes){
								$tmpAttr = array();
								foreach($row as $attr => $sAttr)
									$tmpAttr[] = $attr
												. '="'
												. str_replace(array('"',"'"),
																"",
																strip_tags(
																	html_entity_decode($sAttr,
																						ENT_NOQUOTES,
																						'UTF-8'
																	)))
												. '"';
								$childs[] = '<' . $sTag . ' ' . implode(' ', $tmpAttr) . "/>";
							}else
								$childs[] = '<' . $sTag . '>'
											. self::objectToXML($row, $likeNodes)
											. '</' . $sTag . ">";
						}
						$sTag .= 's';
					}elseif(is_string($vValue) && $vValue != '')
						$childs[] = "<![CDATA[\n"
									. strip_tags(
										html_entity_decode(((
											substr($vValue,0,1) == '/') ? "\\"
											. $vValue : $vValue), ENT_NOQUOTES, 'UTF-8')) . "\n ]]>";
					else{
						foreach($vValue as $attr => $sAttr){
							if(substr_count($attr, 'password') == 0){
								if(is_array($sAttr)&&$likeNodes === 'arrayToNodes')
									$childs[] = '<' . $attr . '>' . self::objectToXML($sAttr, $likeNodes)
												. '</' . $attr . ">";
								else
									$att[] = $attr . '="' . str_replace(array('"',"'"), "", $sAttr)
											. '"';
							}
						}
					}
					$aReturn[] = '<' . $sTag . ((count($att)>0) ? ' ' . implode(' ', $att) : '')
									. ((count($childs)>0) ? ">" : "/>");
					$aReturn[] = ((count($childs)>0) ? implode("\n", $childs) . '</' . $sTag . '>' : '');
				}
			}
		}
		return implode("\n", $aReturn);
	}
	/**
	 * Transforma un texto de tiempo de MySQL (ISO 8601) a un tipo tiempo de PHP
	 * @param	string	$sData	Texto en formato ISP 8601
	 * @param	integer	$dias	Dias a ser agregados
	 * @return	time	Regresa el valor de tiempo del string
	 */
	public static function iso8601ToTime($sData, $dias = 0){
		$aData = split(' ',$sData);
		$aTime = split(':',$aData[1]);
		$aDate = split('-',$aData[0]);
		$return = mktime($aTime[0],$aTime[1],$aTime[2],$aDate[1],$aDate[2]+$dias,$aDate[0]);
		return $return;
	}
	/**
	 * Transforma un valor timepo a un texto
	 * @param	time	$sData	Timepo a ser transformado
	 * @param	integer	$dias	Dias a ser agregados
	 * @param	boolean	$display	Indica se quiere desplegar el mes
	 */
	public static function timeToText($time = '', $dias = 0, $display = true){
		$return = date('d/m/Y',$date);
		if($display)
			$return = date('d - F - Y',$date);
		return $return;
	}
	/**
	 * Transforma un texto a un frendly URL eliminando caracteres especiales y cambiando caracteres acentuados a vocales
	 * @param	string	$str	String a tranformar
	 * @return	string	El texto transformado
	 */
	public static function getFriendly($str = '') {
		$str = urlencode(
				strtolower(
					ereg_replace('[^a-zA-Z0-9]+', '_',
						str_replace(
							split(',', utf8_encode('á,é,í,ó,ú,ñ,Á,É,Í,Ó,Ú,Ñ, ')),
							split(',', 'a,e,i,o,u,n,a,e,i,o,u,n,_'),
							$str
						)
					)
				)
			);
		return $str;
	}
	/**
	 * Convierte un numero en letras.
	 * @param number $n Un numero.
	 */
	public static function int2str($n) {
		return ($n-->26?chr(($n/26+25)%26+ord('A')):'') . chr($n%26+ord('A'));
	}
	/**
	 * Carga archivos de la libreria externa.
	 * @param	string	$file	Path al archivo sin extencion.
	 * @return	bloean	Si el archivo fue cargado
	 */
	public static function callExternal($file){
		$file = EXTERNALS_PATH . $file . '.php';
		if(is_file($file)){
			return require_once($file);
		}else{
			self::trace('Error: El archivo ' . $file . ' no se encuentra');
			return false;
		}
	}
	/**
	 * Obtiene la informacion del disco donde se esta trajando
	 * @param	string	$sUnidad	Unidad de medida de los bytes, Default mb
	 * @return	object	Un objeto con las propiedades; free, total y used
	 */
	public static function getDDInfo($sUnidad='mb'){
		$tmp['free'] = disk_free_space(SITE_BASE_PATH);
		$tmp['total'] = disk_total_space(SITE_BASE_PATH);
		$tmp['used'] = $this->total - $this->free;
		switch($sUnidad){
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
	 * Comprueba la existencia de un path y si no lo crea
	 * @param	string	$path	El path es completo, sin nombre de archivo y debe empesar con /
	 * @param	boolean	$haveFilename	Si el path contiene nombre de archivo.
	 * @return	boolean	Si el path existe
	 */
	public static function generatePath($path, $haveFilename = false){
		if($haveFilename)
			return is_dir(dirname($path));
		else
			return is_dir($path);
		$aDirs = explode('/', $path);
		if($aDirs[0] == '')
			array_shift($aDirs);
		if($haveFilename)
			array_pop($aDirs);
		if(!(mkdir($path, 0775, true))){
			while($d = array_shift($aDirs)){
				$fulDir .= $d . '/';
				$fulDir;
				if(!is_dir($fulDir))
					mkdir($fulDir, 0777, true);
			}
		}
		if($haveFilename)
			return is_dir(dirname($path));
		else
			return is_dir($path);
		return false;
	}
	/**#@-*/
}
