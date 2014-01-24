<?php
//namespace core\database;
/**
 * Abstracción de conexión a base de datos
 *
 * @author Henry I. Galvez T. <alograg@alograg.me>
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since 2011-03-01
 * @subpackage database
 * @version $Id$
 */

/**
 * Abstracción de conexión a base de datos
 *
 * Define funciones básicas y funciones que se deben de implementar en cualquier
 * conexión SQL
 * @package Mekayotl
 * @subpackage database
 * @abstract
 * @property-read PDO $pgo Objeto de conexión PDO de acceso publico
 * @property-read string $fetchMode El modo de fetch
 */
abstract class Mekayotl_database_SQLAbstract
{
    /**#@+
     * @access protected
     */
    /**
     * La ultima consulta realizada
     * @access public
     * @var string
     */

    public $query = '';
    /**
     * El tipo de base de datos
     * @var string
     * @uses Mekayotl_database_SQLAbstract::$pdo Es usado para generar el PDO
     */
    protected $_driver;
    /**
     * El nombre de servidor
     * @var string
     * @uses Mekayotl_database_SQLAbstract::doQuery() Es usado para crear la conexión
     */
    protected $_server = 'localhost';
    /**
     * Usuario de acceso
     * @var string
     * @uses Mekayotl_database_SQLAbstract::doQuery() Es usado para crear la conexión
     */
    protected $_user = 'root';
    /**
     * Clave de acceso
     * @var string
     * @uses Mekayotl_database_SQLAbstract::doQuery() Es usado para crear la conexión
     */
    protected $_password = '';
    /**
     * Nombre de la base de datos
     * @var string
     * @uses Mekayotl_database_SQLAbstract::doQuery() Es usado para crear la conexión
     */
    protected $_dbname = 'test';
    /**
     * PHP Data Objects para implementación de otras bases de datos
     * @var PDO
     */
    protected $_pdo = NULL;
    /**
     * Modo de fetch
     * @var integer Modo actual de fetch
     * @see Mekayotl_database_SQLAbstract::FETCH_ASSOC
     * @see Mekayotl_database_SQLAbstract::FETCH_NUM
     * @see Mekayotl_database_SQLAbstract::FETCH_BOTH
     * @see Mekayotl_database_SQLAbstract::FETCH_OBJ
     * @see Mekayotl_database_SQLAbstract::FETCH_INTO
     * @see Mekayotl_database_SQLAbstract::FETCH_CLASS
     */
    protected $_fetchMode = 1;
    /**
     * Nombre de clase u objeto creado para recibir los datos de un registro
     * @var mixed
     */
    protected $_fetchObject = 'stdClass';
    /**#@-*/
    /**#@+
     * Constantes de acceso publico para asignar el modo de retorno de búsqueda
     * @access public
     * @uses Mekayotl_database_SQLAbstract::setFetchMode() Para asignar el tipo de
     * búsqueda
     */
    /**
     * Para array asociativos
     * @var integer Valor 0
     */
    const FETCH_ASSOC = 1;
    /**
     * Para un array nomal
     * @var integer Valor 1
     */
    const FETCH_NUM = 2;
    /**
     * Para mezclar un array asociativo con un array normal
     * @var integer Valor 2
     */
    const FETCH_BOTH = 3;
    /**
     * Para un objeto estándar
     * @var integer Valor 3
     */
    const FETCH_OBJ = 4;
    /**
     * Para un objeto especifico
     * @var integer Valor 4
     */
    const FETCH_INTO = 5;
    /**
     * Para generar una clase especificada
     * @var integer Valor 5
     */
    const FETCH_CLASS = 6;
    /**#@-*/
    /**#@+
     * @access public
     */
    /**
     * Construye el objeto y asigna las variables predeterminadas
     * @param string $server Nombre del servidor o array con la configuración
     * @param string $user Nombre de usuario para acceder
     * @param string $password Clave de acceso
     * @param string $dbname Nombre de la base de datos
     */

    public function __construct($server = 'localhost', $user = 'root',
            $password = '', $dbname = 'test')
    {
        if (is_string($server)) {
            $this->_server = $server;
            $this->_user = $user;
            $this->_password = $password;
            $this->_dbname = $dbname;
        } elseif (is_array($server)) {
            $this->setConfig($server);
        }
    }

    /**
     * Acciones a tomar en la destrucción del objeto
     */

    public function __destruct()
    {
    }

    /**
     * Retorna variables generadas o privadas
     * @param string $name Nombre de la variable
     * @return mixed Variable solicitada
     * @uses Mekayotl_database_SQLAbstract::$pdo Para acceder a
     * Mekayotl_database_SQLAbstract::$_pdo
     */

    public function __get($name)
    {
        switch ($name) {
            case 'pdo':
                if (!$this->_pdo) {
                    $available = PDO::getAvailableDrivers();
                    if (!in_array($this->_driver, $available)) {
                        throw new Exception(
                                'Error: El driver no esta disponible');
                    }
                    $this->_pdo = new PDO($this->getDNS(), $this->_user,
                            $this->_password);
                }
                return $this->_pdo;
                break;
            case 'fetchMode':
                switch ($this->_fetchMode) {
                    case 5:
                        return 'fetchClass';
                        break;
                    case 4:
                        return 'fetchInto';
                        break;
                    case 3:
                        return 'fetchObj';
                        break;
                    case 2:
                        return 'fetchBoth';
                        break;
                    case 1:
                        return 'fetchNum';
                        break;
                    case 0:
                    default:
                        return 'fetchAssoc';
                }
                break;
            default:
                return NULL;
        }
    }

    /**
     * Para identificar la clase como string
     * @return string El nombre de la clase con la ultima consulta realizada
     */

    public function __toString()
    {
        return '[' . get_class($this) . ']' . $this->query;
    }

    /**
     * Limpia un valor para ser ingresado como dato
     * @static
     * @param array $data Un array para limpiar sus valores
     * @return array Contiene el mismo array recibido pero después de un mapeo de
     * mysql_real_escape_string, trim y addslashes
     */

    static public function clean($data)
    {
        if (is_array($data)) {
            return array_map('trim', $data);
        }
        return trim(addslashes($data));
    }

    /**
     * Asigna la configuración para las conexiones
     * @param array|string $oConfig Se espera un array asociativo para asignar
     * variables o un [@link http://mx.php.net/manual/es/function.parse-url.php URL
     * de acceso]
     * @return mixed This
     */

    public function setConfig($oConfig = 'mysql://root:none@localhost/test')
    {
        if (is_array($oConfig)) {
            foreach ($oConfig as $key => $value) {
                $key = '_' . $key;
                $this->$key = $value;
            }
        } elseif (is_string($oConfig)) {
            $oConfig = parse_url($oConfig);
            $this->_driver = $oConfig['scheme'];
            $this->_server = $oConfig['host'];
            $this->_user = $oConfig['user'];
            $this->_password = $oConfig['pass'];
            $this->_dbname = substr($oConfig['path'], 1);
        } else {
            throw new Exception("Error: Config Error");
        }
        return $this;
    }

    /**
     * Evalúa si esta activa una session para realizar la consulta proporcionada.
     * @param string $sql Un SQL bien formado
     * @return mixed El valor retornado por this::doQuery()
     */

    public function query($sql)
    {
        if (Mekayotl_tools_Security::hasSession()) {
            return $this->doQuery($sql);
        }
        return NULL;
    }

    /**
     * Realiza una consulta SQL
     * @param string $table El nombre de la tabla a consultar
     * @param string $fields Los campos a mostrar. Default: * (todos)
     * @param mixed $where Un SQL string de filtrado o un WhereCollection con el
     * filtro a aplicar
     * @param string $order Un SQL string de orden a aplicar
     * @param array $join Un array asociativo con claves por tablas y valors por
     * tiempo de union
     * @param string|array $group Un SQL string de agrupamiento
     * @param mixed $having Un SQL string de filtrado o un WhereCollection con el
     * filtro a aplicar despues del agrupamiento
     * @param integer $limit La cantidad de registros a devolver por pagina. Default:
     * 0 (todos)
     * @param integer $page La pagina de resultados deseada. Default: 0 (la primera)
     * @return resource El enlace de la consulta
     */

    public function select($table, $fields = "*", $where = '', $order = "",
            $join = array(), $group = "", $having = "", $limit = 0, $page = 0)
    {
        if ($table == "" || is_null($table)) {
            return FALSE;
        }
        $query = array();
        if ($fields == "" || is_null($fields)) {
            $fields = "*";
        }
        $query[] = "SELECT " . implode(", ", (array) $fields);
        $query[] = "FROM " . $table;
        if (is_array($join)) {
            foreach ($join as $joinTable => $on) {
                $query[] = "LEFT JOIN " . $joinTable
                        . ((substr_count($on, 'USING')) ? ' ' : " ON ") . $on;
            }
        }
        if ($where != NULL && ('' . $where) != '') {
            $query[] = "WHERE " . $where;
        }
        if ($group != "") {
            $query[] = "GROUP BY " . implode(", ", (array) $group);
        }
        if ($having != NULL && ('' . $having) != '') {
            $query[] = "HAVING " . $having;
        }
        if ($order != "") {
            $query[] .= "ORDER BY " . implode(', ', (array) $order);
        }
        if ($limit > 0) {
            if ($page == "") {
                $page = 0;
            }
            $query[] = "LIMIT " . ($page * $limit) . ", " . $limit;
        }
        $this->query = implode("\n", $query);
        return $this->doQuery($this->query);
    }

    /**
     * Pivote los campos de una tabla para ofrecer un resumen de datos
     * @param string $table La tabla a la que se le aplicara el pivote
     * @param mixed $where Un SQL string de filtrado o un
     * Mekayotl_database_WhereCollection con el filtro a aplicar
     * @param string|array $groupField El(los) campo(s) principal de agrupamiento
     * @param string $colField Campo con las columnas
     * @param string $resultFields Campo con los valores
     * @param string $groupType Tipo de agrupamiento SQL. Default: MAX
     * @return boolean|resource El enlace de la consulta o falso si no hay valores a
     * pivotear
     */

    public function pivotSelect($table, $where, $groupField = '',
            $colField = '', $resultFields = '', $groupType = 'MAX')
    {
        $rCols = $this->select($table, $colField, $where, NULL, $colField);
        if ($this->count($rCols) == 0) {
            return FALSE;
        }
        if ($resultFields == '' || $resultFields == NULL) {
            $resultFields = $colField;
        }
        if ($groupField != '') {
            $aPivoteFields[] = $groupField;
        }
        $oldFetchMode = $this->_fetchMode;
        $this->setFetchMode(self::FETCH_NUM);
        while ($row = $this->fetch($rCols)) {
            if ($row[0] != '') {
                $aPivoteFields[] = $groupType . "(IF(" . $colField . "='"
                        . $row[0] . "', " . $resultFields . ", NULL)) as '"
                        . $row[0] . "'";
            }
        }
        $this->setFetchMode($oldFetchMode);
        return $this->select($table, $aPivoteFields, $where, $groupField, NULL,
                        $groupField);
    }

    /**
     * Inserta un registro a la tabla
     * @param string $table Nombre de la tabla
     * @param ArrayAccess $data Un array asociativo o similar que contiene los datos
     * a ser insertados, llaves como campos
     * @return mixed Regresa el resultado generado por
     * Mekayotl_database_SQLAbstrac::doQuery()
     */

    public function insert($table, $data)
    {
        if ($table != "" && $data != NULL) {
            if (!is_array($data)) {
                $data = array_filter((array) $data);
            }
            $keys = array_keys($data);
            $this->query = "INSERT INTO " . $table;
            if (is_string($keys[0])) {
                $this->query .= " (" . implode(', ', $keys) . ")";
            }
            $this->query .= " VALUES ";
            $aData = $this->clean($data);
            $this->query .= "('" . implode("', '", $aData) . "')";
            return $this->doQuery($this->query);
        }
        return FALSE;
    }

    /**
     * Remplaza un registro a la tabla
     * @param string $table Nombre de la tabla
     * @param ArrayAccess $data Un array asociativo o similar que contiene los datos
     * a ser insertados, llaves como campos
     * @return mixed Regresa el resultado generado por
     * Mekayotl_database_SQLAbstrac::doQuery()
     */

    public function replace($table, $data)
    {
        if ($table != "" && $data != NULL) {
            $this->query = "REPLACE INTO " . $table;
            $keys = array_keys((array) $data);
            if (is_string($keys[0])) {
                $this->query .= " (" . implode(', ', $keys) . ")";
            }
            $aData = $this->clean((array) $data);
            $this->query .= " VALUES ('" . implode("', '", $aData) . "')";
            return $this->doQuery($this->query);
        }
        return FALSE;
    }

    /**
     * Actualiza un registro a la tabla
     * @param string $table Nombre de la tabla
     * @param string|array $idKey Un string con el nombre del campo principal o un
     * array con los nombres de los campos que serán tomados en cuenta para el filtro
     * @param ArrayAccess $data Un array asociativo o similar que contiene los datos
     * a ser insertados, llaves como campos
     * @return mixed Regresa el resultado generado por
     * Mekayotl_database_SQLAbstrac::doQuery()
     */

    public function update($table, $idKey, $data)
    {
        $this->query = "UPDATE " . $table . " SET ";
        $where = new Mekayotl_database_WhereCollection();
        $updateFields = array();
        foreach ($data as $field => $value) {
            if (($field != $idKey) && !in_array($field, $idKey)) {
                switch (gettype($value)) {
                    case 'NULL':
                        break;
                    case 'string':
                    default:
                        $updateFields[] = $field . "='" . self::clean($value)
                                . "'";
                }
            } else {
                $where->$field = $value;
            }
        }
        if (is_array($updateFields)) {
            $this->query .= implode(", ", $updateFields);
            $this->query .= " WHERE " . $where;
            return $this->doQuery($this->query);
        }
        throw new Exception("Error: No contiene filtro de actualizacion");
    }

    /**
     * Realiza una eliminación de registros según filtro
     * @param string $table Nombre de la tabla
     * @param array|Mekayotl_database_WhereCollection $where Un objeto para
     * realizar el filtro de lo que se va a borrar
     * @return mixed Regresa el resultado generado por
     * Mekayotl_database_SQLAbstrac::doQuery()
     */

    public function delete($table, $where)
    {
        $this->query = "DELETE FROM " . $table;
        if ($where instanceof Mekayotl_database_dal_ValueAbstract) {
            $where = $where->asWhere();
        }
        if (!($where instanceof Mekayotl_database_WhereCollection)) {
            $where = new Mekayotl_database_WhereCollection($where);
        }
        $this->query .= " WHERE " . $where;
        return $this->doQuery($this->query);
    }

    /**
     * Inserta una serie de datos en una tabla
     * @param string $table Nombre de la tabla
     * @param array $data Un array de ArrayAccess a ser insertados en la tabla,
     * llaves como campos
     * @return mixed Regresa el resultado generado por
     * Mekayotl_database_SQLAbstrac::doQuery() en una inserción extendida
     */

    public function batchInsert($table, array $data)
    {
        if ($table != "" && $data != NULL) {
            $keys = array_keys($data[0]);
            $this->query = "INSERT INTO " . $table;
            if (is_string($keys[0])) {
                $this->query .= " (" . implode(', ', $keys) . ")";
            }
            $this->query .= " VALUES ";
            $queryInserts = array();
            for ($i = 0; $i < count($data); $i++) {
                $aData = $this->clean((array) $data[$i]);
                $queryInserts[] = "('" . implode("', '", $aData) . "')";
            }
            $this->query .= implode(",\n", $queryInserts);
            return $this->doQuery($this->query);
        }
        return FALSE;
    }

    /**
     * Remplace una serie de datos en una tabla
     * @param string $table Nombre de la tabla
     * @param array $data Un array de ArrayAccess a ser remplazados en la tabla,
     * llaves como campos
     * @return mixed Regresa el resultado generado por
     * Mekayotl_database_SQLAbstrac::doQuery() en una inserción extendida
     */

    public function batchReplace($table, array $data)
    {
        if ($table != "" && $data != NULL) {
            $this->query = "REPLACE INTO " . $table;
            $keys = array_keys($data[0]);
            if (is_string($keys[0])) {
                $this->query .= " (" . implode(', ', $keys) . ")";
            }
            $this->query .= " VALUES ";
            $queryInserts = array();
            for ($i = 0; $i < count($data); $i++) {
                $aData = $this->clean((array) $data[$i]);
                $query[] = "('" . implode("', '", $aData) . "')";
            }
            $this->query .= implode(",\n", $query);
            return $this->doQuery($this->query);
        }
        return FALSE;
    }

    /**
     * Actualiza una serie de datos en una tabla
     * @param string $table Nombre de la tabla
     * @param string|array $idKey Un string con el nombre del campo principal o un
     * array con los nombres de los campos que serán tomados en cuenta para el filtro
     * @param array $data Un array de ArrayAccess a ser actualizado en la tabla,
     * llaves como campos
     * @return array Regresa array con los resultados generados por
     * Mekayotl_database_SQLAbstrac::doQuery() para cada elemento
     */

    public function batchUpdate($table, $idKey, array $data)
    {
        $return = array();
        for ($i = 0; $i < count($data); $i++) {
            $this->query = "UPDATE " . $table . " SET ";
            $where = new Mekayotl_database_WhereCollection();
            $current = $data[$i];
            foreach ($current as $field => $value) {
                if (($field != $idKey) && !in_array($field, $idKey)) {
                    switch (gettype($value)) {
                        case 'NULL':
                            break;
                        case 'string':
                            $value = "'" . self::clean($value) . "'";
                        default:
                            $upFields[] = $field . "='" . self::clean($value)
                                    . "'";
                    }
                } else {
                    $where[$field] = $value;
                }
            }
            if (is_array($upFields)) {
                $this->query .= implode(", ", $upFields);
                $this->query .= " WHERE " . $where;
                //try {
                $return[] = $this->doQuery($this->query);
                /*} catch(Exception $e) {
                $return[] = $e -> getMessage();
                }*/
            } else {
                $return[] = "Error: No contiene filtro de actualización";
            }
        }
        return $return;
    }

    /**
     * Transforma un resultado de una consulta de select en un texto separado por
     * comas
     * @param resource $resultset El resource del cual se quieren los datos
     * @param array $fields Es un arreglo asociativo con las llaves como nombre de
     * los campos que se desean y por valor los titulo de las columnas
     * @return string El texto con los valores serados por comas
     */

    public function toCSV($resultset, $fields = array())
    {
        $string = array();
        $peronalFields = TRUE;
        if (count($fields) < 1) {
            $fields = $this->getFields($resultset);
            $peronalFields = FALSE;
        }
        $string[] = implode(',', $fields);
        $oldFetchMode = $this->_fetchMode;
        $this->_fetchMode = 1;
        while ($row = $this->fetch($resultset)) {
            $data = array();
            if ($peronalFields) {
                foreach ($fields as $field => $name) {
                    $data[] = (preg_match('/^0/', $row[$field])) ? "'"
                                    . $row[$field] : $row[$field];
                }
            } else {
                $data = $row;
            }
            $string[] = '"' . implode('","', $data) . '"';
        }
        $this->_fetchMode = $oldFetchMode;
        return implode("\r\n", $string);
    }

    /**
     * Transforma un resultado de una consulta de select en un texto xml
     * @param resource $resultset El resource del cual se quieren los datos
     * @return string Texto con/del XML
     */

    public function toXML($resultset)
    {
        $string = array();
        $string[] = '<table>';
        $oldFetchMode = $this->_fetchMode;
        $this->_fetchMode = Mekayotl_database_SQLAbstract::FETCH_ASSOC;
        $string[] = "\t" . '<rows>';
        while ($row = $this->fetch($resultset)) {
            $elementXML = Mekayotl_tools_utils_Conversion::toXML($row, 'row',
                    TRUE);
            $string[] = "\t\t" . $elementXML;
        }
        $string[] = "\t" . '</rows>';
        $this->_fetchMode = $oldFetchMode;
        $string[] = '</table>';
        return implode("\r\n", $string);
    }

    /**#@-*/
    /**#@+
     * Funciones abstractas a declarar
     * @abstract
     */
    /**
     * Genera los DNS para una conexión con PDO
     * @access protected
     * @return string El string DNS de conexión
     * @uses Mekayotl_database_SQLAbstrac::$pdo Lo utiliza para generar el [@link
     * http://mx.php.net/manual/es/pdo.construct.php PDO]
     */

    abstract protected function getDSN();
    /**
     * Obtiene los campos de una consulta
     * @access protected
     * @param resource $resultset El resource del cual se quieren los campos
     * @return array Los campos de la consulta
     */

    abstract protected function getFields($resultset);
    /**
     * Ejecuta una consulta SQL
     * @access protected
     * @param string $sql Un SQL bien formado
     * @return mixed El resource obtenido de la ejecución del SQL o lo implementado
     * en la clase
     * @uses Mekayotl_database_SQLAbstrac::select() Lo utiliza para ejecutar el SQL
     * que genera
     * @uses Mekayotl_database_SQLAbstrac::insert() Lo utiliza para ejecutar el SQL
     * que genera
     * @uses Mekayotl_database_SQLAbstrac::replace() Lo utiliza para ejecutar el SQL
     * que genera
     * @uses Mekayotl_database_SQLAbstrac::update() Lo utiliza para ejecutar el SQL
     * que genera
     * @uses Mekayotl_database_SQLAbstrac::delete() Lo utiliza para ejecutar el SQL
     * que genera
     * @uses Mekayotl_database_SQLAbstrac::pivotSelect() Lo utiliza para ejecutar el
     * SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::batchInsert() Lo utiliza para ejecutar el
     * SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::batchReplace() Lo utiliza para ejecutar el
     * SQL que genera
     * @uses Mekayotl_database_SQLAbstrac::batchUpdate() Lo utiliza para ejecutar el
     * SQL que genera
     */

    abstract protected function doQuery($sql);
    /**
     * Cuenta los registros devueltos por una consulta
     * @access public
     * @param resource $resultset El resource del cual se quiere el conteo
     * @return integer La cantidad de registros
     */

    abstract public function count($resultset);
    /**
     * Asigna el tipo de retorno de una búsqueda de registro de una consulta
     * @access public
     * @param integer $mode Tipo de retorno de fetch
     * @param string|object $class Nombre de la clase u objeto de retorno
     * @param array $ctorargs Opciones de construcción de clase especifica
     * @return mixed This
     */

    abstract public function setFetchMode($mode, $class = 'object',
            $ctorargs = array());
    /**
     * Obtener el registro siguiente del recurso
     * @access public
     * @param resource $resultset El resource del cual se quiere el conteo
     * @return mixed Una variable de tipo de formato según fetchmode
     * @uses SQLAbstract::fetchAll() Lo utiliza para generar los elementos del array
     */

    abstract public function fetch($resultset);
    /**
     * Obtener el registro siguiente del recurso
     * @access public
     * @param resource $resultset El resource del cual se quiere el conteo
     * @return array Un array con todos los registros según fetchmode
     */

    abstract public function fetchAll($resultset);
    /**
     * Regresa el apuntador de registros al primero
     * @param resource $resultset El resource del cual se quiere el conteo
     * @return mixed This
     * @uses Mekayotl_database_SQLAbstrac::fetchAll() Lo utiliza para reiniciar el
     * apuntador al primer registro
     */

    abstract public function resetFetch($resultset);
    /**#@-*/
}
