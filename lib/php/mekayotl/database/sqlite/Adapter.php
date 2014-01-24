<?php
//namespace core\database\sqlite;
/**
 * Contiene la clase para manejo de datos de SQLite
 *
 * @author Henry I. Galvez T. <alograg@alograg.me>
 * @copyright Copyright (c) 2008, Cuatromedios.com  {@link
 * http://www.cuatromedios.com}
 * @package Mekayotl.database
 * @since 2011-03-01
 * @subpackage sqlite
 * @version $Id$
 */

/**
 * Clase de control de acceso a SQLite
 *
 * @package Mekayotl.database
 * @subpackage sqlite
 * @uses Mekayotl_database_SQLAbstract Es utilizado para generar los
 * filtrados de consulta
 */
class Mekayotl_database_sqlite_Adapter extends Mekayotl_database_SQLAbstract
{
    /**
     * Pre-configura el objeto
     * @param array|string $oConfig Se espera un array asociativo para asignar
     * variables o un URL de acceso [@link
     * http://mx.php.net/manual/es/function.parse-url.php]
     */

    public function __construct($oConfig = 'sqlite://root:@localhost/test')
    {
        $this->setConfig($oConfig);
        $this->driver = 'sqlite2';
        $dns = $this->getDNS();
        $this->_pdo = new PDO($dns);
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
        $return = MEKAYOTL_PATH . '/database/sqlite/data/' . $this->dbname
                . '.sq2';
        $return = str_replace('\\', DIRECTORY_SEPARATOR, $return);
        if (!is_file($return)) {
            $tmp = sqlite_open($return, 0666);
            sqlite_close($tmp);
        }
        if (is_file($return)) {
            return $this->driver . ':' . $return;
        } else {
            throw new Exception('Error: No se tiene acceso.');
        }
    }

    /**
     * Obtiene los campos de una consulta
     * @access private
     * @param resource $resultset El resource del cual se quieren los campos
     * @return array Los campos de la consulta
     */

    protected function getFields($resultset)
    {
        $field = array();
        $fields = $resultset->columnCount();
        for ($i = 0; $i < $fields; $i++) {
            $f = (object) $resultset->getColumnMeta();
            $field[] = $f->name;
        }
        if (count($field) > 0) {
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
     * @uses Mekayotl_database_SQLAbstract::select() Lo utiliza para ejecutar
     * el SQL que genera
     * @uses Mekayotl_database_SQLAbstract::insert() Lo utiliza para ejecutar
     * el SQL que genera
     * @uses Mekayotl_database_SQLAbstract::replace() Lo utiliza para ejecutar
     * el SQL que genera
     * @uses Mekayotl_database_SQLAbstract::update() Lo utiliza para ejecutar
     * el SQL que genera
     * @uses Mekayotl_database_SQLAbstract::delete() Lo utiliza para ejecutar
     * el SQL que genera
     * @uses Mekayotl_database_SQLAbstract::pivotSelect() Lo utiliza para
     * ejecutar el SQL que genera
     * @uses Mekayotl_database_SQLAbstract::batchInsert() Lo utiliza para
     * ejecutar el SQL que genera
     * @uses Mekayotl_database_SQLAbstract::batchReplace() Lo utiliza para
     * ejecutar el SQL que genera
     * @uses Mekayotl_database_SQLAbstract::batchUpdate() Lo utiliza para
     * ejecutar el SQL que genera
     */

    protected function doQuery($sql)
    {
        if (MEKAYOTL_DEBUG) {
            Mekayotl::startTimer();
        }
        $this->query = $sql;
        $return = NULL;
        if ($this->_pdo) {
            $rs = $this->_pdo
                    ->prepare(utf8_encode($sql));
            $rs->execute();
            if (!(substr_count($sql, 'SELECT ') > 0
                    || substr_count($sql, 'SHOW ') > 0)) {
                if ($this->_pdo
                        ->lastInsertId() > 0) {
                    $return = $this->_pdo
                            ->lastInsertId();
                }
                // Regresa el ultimo id insertado
                $return = $rs->rowCount();
                // Regresa la información de la ultima consulta
            }
            if ($rs->errorCode() != 0) {
                $errorMsg = $rs->errorInfo();
                $return = $rs->errorCode() . ': ' . $errorMsg[0] . ': ' . $sql;
            } else {
                $return = $rs;
            }
        }
        if (MEKAYOTL_DEBUG) {
            $time = Mekayotl::stopTimer();
            $logQuery = '- [' . get_class($this) . '] - time: ' . $time
                    . "s -\r\n" . $sql . "\r\n" . get_class($rs);
            Mekayotl::trace($logQuery);
            Mekayotl::addToLogFile('query', $logQuery, "YmdHi");
        }
        return $return;
    }

    /**
     * Cuenta los registros devueltos por una consulta
     * @access public
     * @param resource $resultset El resource del cual se quiere el conteo
     * @return integer La cantidad de registros
     */

    public function count($resultset)
    {
        return $resultset->rowCount();
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
        switch ($this->_fetchMode) {
            case 6:
            case 5:
            case 4:
                $class = (is_string($this->_fetchObject)) ? $this->_fetchObject
                        : get_class($this->_fetchObject);
                $return = $resultset->fetch(5);
                if ($class != 'stdClass') {
                    $newCalss = new $class();
                    foreach ($newCalss as $attr => $value) {
                        $newCalss->$attr = $return->$attr;
                    }
                    $return = $newCalss;
                }
                break;
            case 3:
            case 2:
            case 1:
            default:
                $return = $resultset->fetch($this->_fetchMode + 1);
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
        if (in_array($this->_fetchMode,
                array(
                        1,
                        2,
                        3
                )))
            return $resultset->fetchAll($this->_fetchMode + 1);
        while ($row = $this->fetch($resultset)) {
            $return[] = $row;
        }
        $this->resetFetch($resultset);
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
        $resultset->closeCursor();
        $resultset->execute();
        return $this;
    }

}
