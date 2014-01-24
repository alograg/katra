<?php
//namespace core\database\mysql;
/**
 * Contiene la clase para manejo de datos de MySQL
 *
 * @author Henry I. Galvez T. <alograg@alograg.me>
 * @copyright Copyright(c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl.database
 * @since 2011-03-01
 * @subpackage mysql
 * @version $Id$
 */

/**
 * Clase de control de acceso a MySQL
 *
 * @package Mekayotl.database
 * @subpackage mysql
 * @uses Mekayotl_database_SQLAbstract Es utilizado para generar los filtrados
 * de consulta
 */
class Mekayotl_database_mysql_Adapter extends Mekayotl_database_SQLAbstract
{
    /**
     * Enlace de la coneccion
     * @var resource
     */

    private $_link = NULL;
    /**
     * Pre-configura el objeto
     * @param array|string $oConfig Se espera un array asociativo para asignar
     * variables o un URL de acceso [@link
     * http://mx.php.net/manual/es/function.parse-url.php]
     */

    public function __construct($oConfig = 'mysql://root:@localhost/test',
            $useMySQLi = FALSE)
    {
        $this->setConfig($oConfig);
        $this->_driver = 'mysql';
        $this->enambleMySQLi($useMySQLi);
    }

    /**
     * Genera los DNS para una conexión con PDO
     * @access protected
     * @return string El string DNS de conexión
     * @uses Mekayotl_database_SQLAbstract::$pdo Lo utiliza para generar el PDO
     * [@link http://mx.php.net/manual/es/pdo.construct.php]
     */

    protected function getDSN()
    {
        $s = 'mysql:';
        $s .= 'host=' . $this->server . ';';
        $s .= 'dbname=' . $this->server;
        return $s;
    }

    /**
     * Obtiene los campos de una consulta
     * @access private
     * @param resource $resultset El resource del cual se quieren los campos
     * @return array Los campos de la consulta
     */

    protected function getFields($resultset)
    {
        if ($resultset instanceof mysqli_result) {
            $fields = $resultset->fetch_fields();
            for ($i = 0; $i < count($fields); $i++) {
                $field[] = $fields[$i]->name;
            }
            return $field;
        } elseif (get_resource_type($resultset) == 'mysql result') {
            $fields = mysql_num_fields($resultset);
            for ($i = 0; $i < $fields; $i++) {
                $field[] = mysql_field_name($resultset, $i);
            }
            return $field;
        }
        return FALSE;
    }

    /**
     * Ejecuta una consulta SQL
     * @access protected
     * @param string $sql Un SQL bien formado
     * @return mixed El resource obtenido de la ejecución del SQL o lo
     * implementado en la clase
     * @uses Mekayotl_database_SQLAbstrac::select() Lo utiliza para ejecutar
     * el SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::insert() Lo utiliza para ejecutar
     * el SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::replace() Lo utiliza para ejecutar
     * el SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::update() Lo utiliza para ejecutar
     * el SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::delete() Lo utiliza para ejecutar
     * el SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::pivotSelect() Lo utiliza para
     * ejecutar el SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::batchInsert() Lo utiliza para
     * ejecutar el SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::batchReplace() Lo utiliza para
     * ejecutar el SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::batchUpdate() Lo utiliza para
     * ejecutar el SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::query() Lo utiliza para ejecutar
     * el SQL
     */

    protected function doQuery($sql)
    {
        if (MEKAYOTL_DEBUG) {
            Mekayotl_Debug::startTimer();
        }
        $this->query = $sql;
        $return = NULL;
        $rs = ($this->_driver == 'mysqli') ? $this->useMySQLi($sql)
                : $this->useMySQL($sql);
        if (MEKAYOTL_DEBUG) {
            $time = Mekayotl_Debug::stopTimer();
            $logQuery = '- [' . get_class($this) . '] - time: ' . $time
                    . "s -\r\n" . $sql . "\r\n" . serialize($rs);
            Mekayotl_Debug::trace($logQuery);
            Mekayotl_Debug::addToLogFile('query', $logQuery, "YmdHi");
        }
        if (!(substr_count($sql, 'SELECT ') > 0
                || substr_count($sql, 'SHOW ') > 0)) {
            // Regresa el ultimo id insertado
            $lastID = $this->getLastInsertId($rs);
            if ($lastID > 0) {
                // Regresa el ultimo id insertado
                return $lastID;
            }
            // Regresa la información de la ultima consulta
            $return = $this->getInfo($rs);
            if (!$return) {
                // Regresa la información de la ultima consulta
                return $this->getAfectedRows($rs);
            }
            return $return;
        }
        return $this->getError($rs, $return);
    }

    /**
     * Establece el uso de la librería MySQLi
     * @param bollean $b
     * @return Adapter This
     */

    public function enambleMySQLi($b)
    {
        $this->_driver = ($b) ? 'mysqli' : 'mysql';
        return $this;
    }

    /**
     * Realiza la consulta utilizando la librería tradicional de MySQL
     * @param string $sql La consulta
     * @return resource El resultado de {@link
     * http://mx2.php.net/manual/es/function.mysql-query.php mysql_query}
     */

    private function useMySQL($sql)
    {
        $this->_link = mysql_connect($this->_server, $this->_user,
                $this->_password);
        mysql_select_db($this->_dbname, $this->_link);
        mysql_set_charset('utf8', $this->_link);
        return mysql_query($sql, $this->_link);
    }

    /**
     * Realiza la consulta utilizando la librería tradicional de MySQLi
     * @param string $sql La consulta
     * @return mysqli_stmt El
     * {@link http://mx2.php.net/manual/es/mysqli.prepare.php objeto stmt}
     * de la consulto
     */

    private function useMySQLi($sql)
    {
        $mysqli = new mysqli($this->_server, $this->_user, $this->_password,
                $this->_dbname);
        $mysqli->set_charset("utf8");
        if (substr_count($sql, 'SELECT ') > 0
                || substr_count($sql, 'SHOW ') > 0) {
            $result = $mysqli->query($sql);
            if ($result) {
                return $result;
            }
        } else {
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->execute();
                return $stmt;
            }
        }
        return $mysqli;
    }

    /**
     * Obtiene el ultimo id agregado
     * @param mysqli_stmt|resource $resultset El objeto a ser analizado
     * @return integer El identificador del registro agregado
     */

    private function getLastInsertId($resultset)
    {
        return ($this->_driver == 'mysqli') ? $resultset->insert_id
                : mysql_insert_id($this->_link);
    }

    /**
     * Obtiene la información de la consulta
     * @param mysqli_stmt|resource $resultset El objeto a ser analizado
     * @return string El resultado de la consulta, {@link
     * http://mx2.php.net/manual/es/function.mysql-info.php MySQL} o {@link
     * http://mx2.php.net/manual/es/mysqli.info.php MySQLi}
     */

    private function getInfo($resultset)
    {
        return ($this->_driver == 'mysqli') ? $resultset->info
                : mysql_info($this->_link);
    }

    /**
     * Obtiene la cantidad de registros afectados
     * @param mysqli_stmt|resource $resultset El objeto a ser analizado
     * @return integer El resultado de la consulta, {@link
     * http://mx2.php.net/manual/es/function.mysql-affected-rows.php MySQL} o
     * {@link http://mx2.php.net/manual/es/mysqli.affected-rows.php MySQLi}
     */

    private function getAfectedRows($resultset)
    {
        return ($this->_driver == 'mysqli') ? $resultset->affected_rows
                : mysql_affected_rows($this->_link);
    }

    /**
     * Obtiene la cantidad de registros afectados
     * @param mysqli_stmt|resource $resultset El objeto a ser analizado
     * @param mixed $return El valor a regresar si no contiene errores
     * el $resultset
     * @return mixed Una descripción completa del error, el valor de return
     * si existe o $resultset
     */

    private function getError($resultset, $return)
    {
        $error = ($this->_driver == 'mysqli') ? $resultset->errno
                : mysql_errno($this->_link);
        if ($error == 0) {
            return ($return) ? $return : $resultset;
        }
        return ($this->_driver == 'mysqli') ? $resultset->errno . ': '
                        . $resultset->error
                : mysql_errno($this->_link) . ': ' . mysql_error($this->_link);
    }

    /**
     * Cuenta los registros devueltos por una consulta
     * @access public
     * @param resource $resultset El resource del cual se quiere el conteo
     * @return integer La cantidad de registros
     */

    public function count($resultset)
    {
        return ($this->_driver == 'mysqli') ? $resultset->num_rows
                : mysql_num_rows($resultset);
    }

    /**
     * Asigna el tipo de retorno de una búsqueda de registro de una consulta
     * @access public
     * @param integer $mode Tipo de retorno de fetch
     * @param string|object $class Nombre de la clase u objeto de retorno
     * @param array $ctorargs Opciones de construcción de clase especifica
     * @return mixed This
     */

    public function setFetchMode($mode, $class = 'stdClass',
            $ctorargs = array())
    {
        $this->_fetchMode = $mode;
        $this->_fetchObject = $class;
        return $this;
    }

    /**
     * Obtener el registro siguiente del recurso
     * @access public
     * @param resource $resultset El resource del cual se quiere el conteo
     * @return mixed Una variable de tipo de formato según fetchmode
     * @uses Mekayotl_database_SQLAbstract::fetchAll() Lo utiliza para generar
     * los elementos del array
     */

    public function fetch($resultset)
    {
        $return = FALSE;
        if ($resultset instanceof mysqli_result) {
            switch ($this->_fetchMode) {
                case 6:
                case 5:
                case 4:
                    $class = (is_string($this->_fetchObject)) ? $this->_fetchObject
                            : get_class($this->_fetchObject);
                    $return = $resultset->fetch_object($class);
                    break;
                case 3:
                case 2:
                case 1:
                default:
                    $return = $resultset->fetch_array($this->_fetchMode);
            }
        } else {
            switch ($this->_fetchMode) {
                case 6:
                case 5:
                case 4:
                    $class = (is_string($this->_fetchObject)) ? $this->_fetchObject
                            : get_class($this->_fetchObject);
                    $return = mysql_fetch_object($resultset, $class);
                    break;
                case 3:
                case 2:
                case 1:
                default:
                    $return = mysql_fetch_array($resultset, $this->_fetchMode);
            }
        }
        return $return;
    }

    /**
     * Obtener el registro siguiente del recurso
     * @access public
     * @param resource $resultset El resource del cual se quiere el conteo
     * @return array Un array con todos los registros según fetchmode
     */

    public function fetchAll($resultset)
    {
        $return = array();
        if ($this->_driver == 'mysqli' && $resultset instanceof mysqli_result
                && in_array($this->_fetchMode,
                        array(
                                1,
                                2,
                                3
                        ))) {
            return $resultset->fetch_all($this->_fetchMode);
        }
        while ($row = $this->fetch($resultset)) {
            $return[] = $row;
        }
        if (count($return) > 0) {
            $this->resetFetch($resultset);
        }
        return $return;
    }

    /**
     * Regresa el apuntador de registros al primero
     * @param resource $resultset El resource del cual se quiere el conteo
     * @return mixed This
     * @uses Mekayotl_database_SQLAbstract::fetchAll() Lo utiliza para
     * reiniciar el apuntador al primer registro
     */

    public function resetFetch($resultset)
    {
        $error = ($this->_driver == 'mysqli') ? $resultset->data_seek(0)
                : mysql_data_seek($resultset, 0);
        return $this;
    }

}
