<?php
//namespace core\tools;
/**
 * Herramienta para implementar algunos casos de seguridad.
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
 * Clase con funciones estéticas para implementación de seguridad.
 *
 * @package Mekayotl
 * @subpackage tools
 */
class Mekayotl_tools_Security
{
    /**#@+
     * @access public
     */
    /**
     * Genera un objeto.
     */

    public function __construct()
    {
    }

    /**
     * Proporciona un acceso de objeto a todas las funciones estíticas de
     * esta clase
     * @param string $method Nombre del método
     * @param array $arguments Argumentos para el método.
     * @return mixed El valor devuelto por el método estático.
     */

    public function __call($method, $arguments)
    {
        //try {
        return call_user_func_array(
                array(
                        self,
                        $method
                ), $arguments);
        /*} catch(Exception $e) {
        throw new Exception("Error: Metodo no existe.");
        }*/
    }

    /**#@-*/
    /**#@+
     * @static
     */
    /**
     * Regresa el ip del usuario actual
     * @return string El ip deducido del usuario.
     */

    public static function getIP()
    {
        if (preg_match('/^(\d{1,3}\.){3}\d{1,3}$/s', $_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            if (preg_match('/^(\d{1,3}\.){3}\d{1,3}$/s',
                    $_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (preg_match('/^(\d{1,3}\.){3}\d{1,3}$/s',
                    $_SERVER["REMOTE_HOST"])) {
                $ip = $_SERVER["REMOTE_HOST"];
            } else {
                $ip = $_SERVER["REMOTE_ADDR"];
            }
        }
        Mekayotl_Debug::trace("IP: " . $ip);
        return $ip;
    }

    /**
     * Crea una session
     * @param string $spaceName Nombre del espacio de la session.
     * @param string|array $user Los datos de la session del usuario.
     */

    public static function createSession($spaceName = 'Mekayotl',
            $user = array(
                    'name' => 'guest',
                    'id' => 0
            ))
    {
        $_SESSION[$spaceName]['user'] = $user;
        $_SESSION[$spaceName]['session_id'] = self::getIP() . '_'
                . $_SESSION[$spaceName]['user']['id'];
    }

    /**
     * Comprueba la existencia de una session valida.
     * @param string $spaceName Espacio de la session
     * @return boolean
     * @TODO: Comprobar este proceso de verificación.
     */

    public static function hasSession($spaceName = 'Mekayotl')
    {
        $sesPhp = (boolean) session_id();
        if (!isset($_SESSION[$spaceName])) {
            return FALSE;
        }
        $sesMekayotlUser = isset($_SESSION[$spaceName]['user']);
        $sesMekayotlSessionStore = $_SESSION[$spaceName]['session_id'];
        $sesMekayotlSessionCreate = self::getIP() . '_'
                . $_SESSION[$spaceName]['user']['id'];
        $hasSession = (boolean) $sesPhp && $sesMekayotlUser
                && ($sesMekayotlSessionStore === $sesMekayotlSessionCreate);
        return $hasSession;
    }

    /**
     * Limpia una variable de caracteres no deseados.
     * @param string|array $mixValues Variable a ser limpiada.
     * @return mixed
     */

    public static function cleanValues($mixValues)
    {
        if (is_array($mixValues)) {
            foreach ($mixValues as $key => $values) {
                $mixValues[$key] = $this->cleanValues($values);
            }
        } else {
            $mixValues = ereg_replace(
                    array(
                            "\n",
                            "\r"
                    ), '', '' . $mixValues);
            $mixValues = trim($mixValues);
        }
        return $mixValues;
    }

    /**
     * Limita el envío de valores
     * @param string $url URL a ser analizado para bloqueo
     * @param int $intTimes Veces que se puede acceder el URL
     * @param boolean|string $blockPage URL de la pagina de bloqueado
     * @TODO: Optimizar este proceso.
     */

    public static function sendLimit($url, $intTimes, $blockPage = FALSE)
    {
        $tmps = LOGS_PATH;
        $logFile = LOGS_PATH . '/access.' . $_SERVER["HTTP_HOST"] . '.log';
        if (is_file($logFile) && filesize($logFile) > 0) {
            $fp = fopen($logFile, "r");
            $logData = fread($fp, filesize($logFile));
            fclose($fp);
            $logData = trim($logData);
            eval("\$logVariable = " . $logData . ";");
        }
        $logArray = $logVariable[$_SERVER['REMOTE_ADDR']];
        if ($logArray['date'] == date('Ymd')) {
            $logArray['sendit'] += 1;
        } else {
            $logArray['date'] = date('Ymd');
            $logArray['sendit'] = 1;
        }
        $logVariable[$_SERVER['REMOTE_ADDR']] = $logArray;
        $logVariables = var_export($logVariable, TRUE);
        $fp = fopen($logFile, "w+");
        fwrite($fp, trim($logVariables));
        fclose($fp);
        if ($logArray['sendit'] > $intTimes) {
            if ($blockPage !== TRUE) {
                header("Location: " . $blockPage);
            }
            Mekayotl::end();
        }
    }

    /**
     * Comprueba si un URL ha sido llamado desde un URL especificado o el mismo.
     * @param string $blnSpecific El URL especifico de donde el URL debe ser
     * llamado
     * @return boolean
     */

    public static function callBySelf($blnSpecific = FALSE)
    {
        if ($blnSpecific) {
            $same = (('http://www.' . $_SERVER["HTTP_HOST"] . $blnSpecific
                    == 'http://www.' . $_SERVER["HTTP_HOST"]
                            . $_SERVER["SCRIPT_NAME"])
                    || ('http://' . $_SERVER["HTTP_HOST"] . $blnSpecific
                            == 'http://' . $_SERVER["HTTP_HOST"]
                                    . $_SERVER["SCRIPT_NAME"]));
        } else {
            $same = (($_SERVER["HTTP_REFERER"]
                    == 'http://www.' . $_SERVER["HTTP_HOST"]
                            . $_SERVER["SCRIPT_NAME"])
                    || ($_SERVER["HTTP_REFERER"]
                            == 'http://' . $_SERVER["HTTP_HOST"]
                                    . $_SERVER["SCRIPT_NAME"]));
        }
        return $same;
    }

    /**
     * Esta función gene contraseñas bajo ciertos niveles de seguridad
     * @param integer $length Longitud de la contraseña
     * @param integer $strength Fortaleza de la contraseña, 0 y 1; letras,
     * 3: letras y números, 4: letras, números y símbolos
     * @return string
     */

    public static function generatePassword($length = 8, $strength = 3)
    {
        $vowels = 'aeiou';
        $consonants = 'bcdfghjklmnpqrstvwxyz';
        switch ($strength) {
            case 4:
                $consonants .= '@#$%';
            case 3:
                $consonants .= '23456789';
            case 2:
                $vowels .= "AEIOU";
                $consonants .= 'BCDFGHJKLMNPQRSTVWXYZ';
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

    /**
     * Destruye la session o un espacio de la sesión.
     * @param string $spaceName
     */

    public static function deleteSession($spaceName = NULL)
    {
        if (is_null($spaceName)) {
            session_destroy();
            session_unset();
        } else {
            unset($_SESSION[$spaceName]);
        }
    }

    /**
     * Establece una cookie para la aplicación
     * @param string $name
     * @param mixed $value
     * @param string $duration Duracion en
     * @param string $host URL para la cookie
     * @return boolean
     */

    public static function setAppCookie($name, $value, $duration = '+8 hours',
            $host = NULL)
    {
        if (!is_string($value)) {
            $value = json_encode($value);
        }
        if (is_null($host)) {
            $host = Mekayotl_tools_Request::parseURI()->host;
        }
        $app = Mekayotl::getAplicationName();
        $name = $app . '[' . $name . ']';
        return self::setCookie($name, $value, $duration, $host);
    }

    /**
     * Establece una cookie.
     * @param string $name
     * @param mixed $value
     * @param string $duration Duracion en
     * @param string $host URL para la cookie
     * @return boolean
     */

    public static function setCookie($name, $value, $duration = '+8 hours',
            $host = NULL)
    {
        if (!is_string($value)) {
            $value = json_encode($value);
        }
        $expire = strtotime($duration);
        return setcookie($name, $value, $expire, '/', $host);
    }

    /**
     * Toma el valor de una cookie para una aplicación
     * @param string $name
     */

    public static function getAppCookie($name)
    {
        $app = Mekayotl::getAplicationName();
        $data = $_COOKIE[$app][$name];
        return Mekayotl_tools_utils_Conversion::toPHP($data);
    }

    /**
     * Elimina una cookie de la aplicación
     * @param string $name
     */

    public static function removeAppCookie($name)
    {
        return self::setAppCookie('token', 'FALSE', '-1 day');
    }

    /**#@-*/
}
