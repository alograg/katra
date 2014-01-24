<?php
//namespace core\tools;
/**
 * Adaptador para envío de correos.
 *
 * @author Henry I. Galvez T. <alograg@alograg.me>
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since 2011-03-01
 * @subpackage tools
 * @version $Id$
 */

Mekayotl::callExternal('phpmailer/class.phpmailer');

/**
 * Adaptador para envío de correos, extendiendo de {@link
 * http://phpmailer.worxware.com/ PHPMailer}.
 *
 * @package Mekayotl
 * @subpackage tools
 */
class Mekayotl_tools_Mailer extends PHPMailer
{
    /**
     * Crea el objeto extendiendo {@link http://phpmailer.worxware.com/
     * PHPMailer}
     * @param string $lang El lenguaje del manejador de correos
     */

    public function __construct($lang = 'es')
    {
        $config = $GLOBALS['config'];
        $this->SetLanguage($lang, EXTERNALS_PATH . "/phpmailer/language/");
        $this->PluginDir = EXTERNALS_PATH . "/phpmailer/";
        $this->IsSMTP();
        $this->Host = $config['email']['server'];
        $this->Hostname = $config['email']['server'];
        $this->SMTPAuth = TRUE;
        //$this -> SMTPDebug = MEKAYOTL_DEBUG;
        if (isset($config['email']['port'])
                && intval($config['email']['port']) > 0) {
            $this->Port = 465;
        }
        $this->Timeout = 60;
        $this->Username = $config['email']['user'];
        $this->Password = $config['email']['password'];
        if (!isset($config['email']['from']) || $config['email']['from'] == '') {
            $config['email']['from'] = $config['email']['user'];
        }
        $this->From = $config['email']['from'];
        $this->FromName = $config['email']['name'];
        if ($config['email']['confirm']) {
            $this->ConfirmReadingTo = $this->From;
        }
    }

    /**
     * Envía una serie de correos
     * @param array $mails Un arreglo de arreglos asociativos con email y name
     * @param string $subject El asunto del correo electrónico
     * @param string $body El cuerpo del correo
     * @return boolean Si alguno de los envíos contiene un error regesa FALSE
     */

    public function sendMailsTo(array $mails, $subject = '', $body = '')
    {
        if (is_array($mails)) {
            $this->Subject = $subject;
            $this->MsgHTML($this->correctBody($body));
            $this->ClearAllRecipients();
            foreach ($mails as $mail) {
                $this->AddAddress($mail['email'], $mail['name']);
            }
            $bSend = ($this->Send()) && ($this->error_count == 0);
            if ($bSend) {
                return TRUE;
            } else {
                Mekayotl::trace($this->ErrorInfo);
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * Corrige la variable a un string para envío de correo
     * @param string|array $body Si se le proporciona un array asociativo lo
     * transforma en un string HTML de campo => valor
     * @return string
     */

    public function correctBody($body)
    {
        $message = '';
        if (is_array($body)) {
            $message = "\n";
            foreach ($body as $field => $value) {
                $message .= '<b>' . ucwords(str_replace('_', ' ', $field))
                        . "</b>:<br />";
                $message .= $value . "<br />\n";
            }
        } elseif (is_string($body)) {
            $message = $body;
        }
        return $message;
    }

    /**
     * Manda un correo por medio de SendGrid
     * @param array $recipients Arreglo con los arreglos asociativos de los
     * destinatarios: ej.
     * array('email'=>'example@example.com','nombre'=>'Example')
     * @param string $asunto Texto del asunto del correo
     * @param string $mensaje Texto con el mensaje, puede tener remplazo de
     * textos por datos: ej; 'Hola {nombre}'
     */

    public function sendGridMailList(array $recipients, $asunto = NULL,
            $mensaje = NULL)
    {
        $config = $GLOBALS['config'];
        if (!isset($config['sendgrid'])) {
            return FALSE;
        }
        Mekayotl::callExternal('swift/swift_required');
        $transport = Swift_SmtpTransport::newInstance(
                $config['sendgrid']['server'], 587);
        $transport->setUsername($config['sendgrid']['user']);
        $transport->setPassword($config['sendgrid']['password']);
        $swift = Swift_Mailer::newInstance($transport);
        // Create a message (subject)
        if (!$asunto) {
            $asunto = 'Mail List: ';
            $asunto = ($config['site']['name']) ? $config['site']['name']
                    : $_SERVER['SERVER_NAME'];
        }
        $message = new Swift_Message($asunto);
        // attach the body of the email
        $message->setFrom(
                        array(
                                $config['sendgrid']['email'] => $config['sendgrid']['name']
                        ));
        $message->addPart("Contenido solo visible en html", 'text/plain');
        try {
            foreach ($recipients as $key => $receiver) {
                $mailMessage = $mensaje;
                if (is_string($mailMessage)) {
                    $mailMessage = Mekayotl_tools_utils_Conversion::substitute(
                            $receiver, $mailMessage);
                } else {
                    $mailMessage = $this->correctBody($mailMessage);
                }
                $message->setBody($mailMessage, 'text/html');
                $message->setTo(
                                array(
                                        $receiver['email'] => $receiver['nombre']
                                ));
                $recipients[$key]['send'] = $swift->send($message, $failures);
            }
        } catch (Exception $e) {
            return array(
                    'error' => $e->xdebug_message
            );
        }
        return $recipients;
    }

}
