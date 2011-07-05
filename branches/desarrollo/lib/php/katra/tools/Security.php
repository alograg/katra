<?php
namespace core\tools;
/**
 * Herramienta para implementar algunos casos de seguridad.
 *
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://alograg.me}
 * @package	Katra
 * @since	2011-03-01
 * @subpackage	tools
 * @version	$Id$
 */

/**
 * Clace con funciones estaticas para implementacion de seguridad.
 *
 * @package	Katra
 * @subpackage	tools
 */
class Security {
	/**#@+
	 * @access	public
	 */
	/**
	 * Genera un objeto.
	 */
	public function __construct(){
	}
	/**
	 * Proporciona un acceso de objeto a todas las funciones estaticas de esta clace
	 * @param	string	$method	Nombre del metodo
	 * @param	array	$arguments	Argumentos para el metodo.
	 * @return	mixed	El valor debuelto por el metodo estatico.
	 */
	public function __call($method, $arguments){
		try{
			return call_user_func_array(array(self, $method), $arguments);
		}catch(Exception $e){
			throw new Exception("Error: Metodo no existe.");
		}
	}
	/**#@-*/
	/**#@+
	 * @static
	 */
	/**
	 * Regreza el ip del usuario actual
	 * @return	array	Los lenguajes del browser
	 */
	public static function getIP() {
		if(preg_match('/^(\d{1,3}\.){3}\d{1,3}$/s', $_SERVER["HTTP_CLIENT_IP"]))
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		else{
			if(preg_match('/^(\d{1,3}\.){3}\d{1,3}$/s', $_SERVER["HTTP_X_FORWARDED_FOR"]))
				$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			else if(preg_match('/^(\d{1,3}\.){3}\d{1,3}$/s', $_SERVER["REMOTE_HOST"]))
				$ip = $_SERVER["REMOTE_HOST"];
			else
				$ip = $_SERVER["REMOTE_ADDR"];
		}
		\core\Katra::trace("IP: ".$ip);
		return $ip;
	}
	/**
	 * Comprueba la existencia de una session valida.
	 * @param	string	$spaceName	Espacio de la session
	 * @return boolean
	 * @TODO:	Comprovar este proceso de verificacion.
	 */
	public static function hasSession($spaceName = 'Katra'){
		$ses_PHP = (boolean) session_id();
		$ses_KatraUser = isset($_SESSION[$spaceName]['user']);
		$ses_KatraSession_store = $_SESSION[$spaceName]['session__id'];
		$ses_KatraSession_create = self::getIP() . '_' . $_SESSION[$spaceName]['user']['id'];
		$hasSession = (boolean)$ses_PHP &&
							$ses_KatraUser &&
							($ses_KatraSession_store === $ses_KatraSession_create);
		return $hasSession;
	}
	/**
	 * Limpia una variable de caracteres no deseados.
	 * @param	string|array	$mixValues	Variable a ser limpiada.
	 * @return	mixed
	 */
	public static function cleanValues($mixValues){
		if(is_array($mixValues)){
			foreach($mixValues as $key=>$values)
				$mixValues[$key] = $this->cleanValues($values);
		}else{
			$mixValues = ereg_replace(array("\n", "\r"), '', '' . $mixValues);
			$mixValues = trim($mixValues);
		}
		return $mixValues;
	}
	/**
	 * Limita el envio de valores
	 * @param	string	$url	URL a ser analisado para bloqueo
	 * @param	int	$intTimes	Veces que se puede acceder el URL
	 * @param	boolean|string	$blockPage	URL de la pagina de bloqueado
	 * @TODO:	Optimizar este proceso.
	 */
	public static function sendLimit($url, $intTimes, $blockPage = false){
		$tmps = LOGS_PATH;
		$logFile = LOGS_PATH . '/access.' . $_SERVER["HTTP_HOST"] . '.log';
		if(is_file($logFile) && filesize($logFile) > 0){
			$fp = fopen($logFile, "r");
			$logData = fread($fp, filesize($logFile));
			fclose($fp);
			$logData = trim($logData);
			eval("\$logVariable = " . $logData . ";");
		};
		$logArray = $logVariable[$_SERVER['REMOTE_ADDR']];
		if($logArray['date'] == date('Ymd'))
			$logArray['sendit'] += 1;
		else{
			$logArray['date'] = date('Ymd');
			$logArray['sendit'] = 1;
		}
		$logVariable[$_SERVER['REMOTE_ADDR']] = $logArray;
		$logVariables = var_export($logVariable, true);
		$fp = fopen($logFile, "w+");
		fwrite($fp, trim($logVariables));
		fclose($fp);
		if($logArray['sendit'] > $intTimes){
			if($blockPage !== true)
				header("Location: " . $blockPage);
			die();
		}
	}
	/**
	 * Comprueba si un URL ha sido llamado desde un URL especificado o el mismo.
	 * @param	string	$blnSpecific	El URL especifico de donde el URL debe ser llamado
	 * @return	boolean
	 */
	public static function callBySelf($blnSpecific = false){
		if($blnSpecific)
			$same = (
				('http://www.' . $_SERVER["HTTP_HOST"] . $blnSpecific == 'http://www.' . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"])
				||
				('http://' . $_SERVER["HTTP_HOST"] . $blnSpecific == 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"])
				);
		else
			$same = (
				($_SERVER["HTTP_REFERER"] == 'http://www.' . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"])
				||
				($_SERVER["HTTP_REFERER"] == 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"])
				);
		return $same;
	}
	/**
	 * Esta funcion gene contraseñas bajo siertos niveles de seguridad
	 * @param	integer	$length	Longitud de la contraseña
	 * @param	integer	$strength	Fortaleza de la contraseña, 0 y 1; letras, 3: letras y numeros, 4: letras, numeros y simbolos
	 * @return	string
	 */
	public static function generatePassword($length = 8, $strength = 3) {
		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';
		switch($strength){
			case 4:
				$consonants .= '@#$%';
			case 3:
				$consonants .= '23456789';
			case 2:
				$vowels .= "AEUY";
			case 1:
			default:
				$consonants .= 'BDGHJLMNPQRSTVWXZ';
		}
		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) {
			if ($alt == 1) {
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}
	/**#@-*/
}