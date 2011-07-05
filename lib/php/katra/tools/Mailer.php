<?php
namespace core\tools;
/**
 * Contiene una coleccion de propiedades que se pueden tranformar en un SQL de filtrado
 *
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://alograg.me}
 * @package	Katra
 * @since	2011-03-01
 * @subpackage	tools
 * @version	$Id$
 */

\core\Katra::callExternal('phpmailes/class.phpmailer');
/**
 * Contiene propiedades de tipo WhereElement que se utilizan para generar los filtros
 *
 * @package	Katra
 * @subpackage	tools
 */
class Mailer extends \PHPMailer {
	/**
	 * Crea el objeto extendiendo {@link http://phpmailer.worxware.com/ PHPMailer}
	 * @param	string	$lang	El lenguaje del manejador de correos
	 */
	public function __construct($lang = 'es'){
		$config = $GLOBALS['config'];
		$this->SetLanguage($lang, EXTERNALS_PATH."/phpmailer/language/");
		$this->PluginDir = EXTERNALS_PATH . "/phpmailer/";
		$this->IsSMTP();
		$this->Host = $config['email']['server'];
		$this->Hostname = $config['email']['server'];
		$this->SMTPAuth = false;
		$this->SMTPDebug= KATRA_DEBUG;
		//$this->Port= 465;
		$this->Timeout= 60;
		$this->Username = $config['email']['user'];
		$this->Password = $config['email']['password'];
		$this->From = $config['email']['user'];
		$this->FromName = $config['email']['name'];
		$this->ConfirmReadingTo = $this->From;
	}
	/**
	 * Envia una serie de correos
	 * @param	array	$mails	Un array de arrays asociativos con email y name
	 * @param	string	$subject	El asunto del correo electronico
	 * @param	string	$body	El cuerpo del correo
	 * @return	boolean	Si alguno de los envios contiene un error regesa false
	 */
	function sendMailsTo(array $mails, $subject = '', $body = ''){
		if(is_array($mails)){
			$this->Subject = $subject;
			$this->MsgHTML($this->correctBody($body));
			$this->ClearAllRecipients();
			foreach($mails as $mail)
				$this->AddAddress($mail['email'], $mail['name']);
			$bSend = ($this->Send()) && ($this->error_count == 0);
			if($bSend)
				return true;
			else{
				\core\Katra::trace($this->ErrorInfo);
				return false;
			}
		}else
			return false;
	}
	/**
	 * Conrrige la variable a un string para envio de correo
	 * @param	string|array	$body	Si se le proporciona un array asociativo lo transforma en un string HTML de campo => valor
	 * @return	string
	 */
	function correctBody($body){
		$message = '';
		if(is_array($body)){
			$message = "\n";
			foreach($body as $field=>$value)
				$message .= '<b>' . ucwords(str_replace('_', ' ', $field)) . "</b>:<br />"
								. $value ."<br />\n";
		}elseif(is_string($body))
			$message = $body;
		return $message;
	}
}
