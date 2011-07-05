<?php
namespace core\database;
/**
 * Abstact de coneccion a base de datos
 *
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://alograg.me}
 * @package	Katra
 * @since	2011-03-01
 * @subpackage	database
 * @version	$Id$
 */

/**
 * Abstact de coneccion a base de datos
 *
 * Define funciones basicas y funciones que se deben de implementar en cualquier coneccion SQL
 * @package	Katra
 * @subpackage	database
 * @abstract
 * @property-read	PDO	$pgo	Objeto de coneccion PDO de acceso publico
 * @property-read	string	$fetchMode	El modo de fetch
 */
abstract class SQLAbstract {
	/**#@+
	 * @access	protected
	 */
	/**
	 * La ultima consulta realizada
	 * @access	public
	 * @var	string
	 */
	public $query = '';
	/**
	 * El tipo de base de datos
	 * @var	string
	 * @uses	SQLAbstract::$pdo	Es usado para generar el PDO
	 */
	protected $driver;
	/**
	 * El nombre de servidor
	 * @var	string
	 * @uses	SQLAbstract::doQuery()	Es usado para crear la coneccion
	 */
	protected $server = 'localhost';
	/**
	 * Usuario de acceso
	 * @var	string
	 * @uses	SQLAbstract::doQuery()	Es usado para crear la coneccion
	 */
	protected $user = 'root';
	/**
	 * Clave de acceso
	 * @var	string
	 * @uses	SQLAbstract::doQuery()	Es usado para crear la coneccion
	 */
	protected $password = '';
	/**
	 * Nombre de la base de datos
	 * @var	string
	 * @uses	SQLAbstract::doQuery()	Es usado para crear la coneccion
	 */
	protected $dbname = 'test';
	/**
	 * PHP Data Objects para implementacion de otras bases de datos
	 * @var PDO
	 */
	protected $_pdo = null;
	/**
	 * Modo de fetch
	 * @var	integer	Modo actual de fetch
	 * @see	SQLAbstract::FETCH_ASSOC
	 * @see	SQLAbstract::FETCH_NUM
	 * @see	SQLAbstract::FETCH_BOTH
	 * @see	SQLAbstract::FETCH_OBJ
	 * @see	SQLAbstract::FETCH_INTO
	 * @see	SQLAbstract::FETCH_CLASS
	 */
	protected $_fetchMode = 1;
	/**
	 * Nobre de clace u objecto creado para recivir los datos de un registro
	 * @var	mixed
	 */
	protected $_fetchObject = 'stdClass';
	/**#@-*/
	/**#@+
	 * Constantes de acceo publico para asignar el modo de retorno de busqueda
	 * @access	public
	 * @uses	SQLAbsctract::setFetchMode()	Para asignar el tipo de busqueda
	 */
	/**
	 * Para array asociativos
	 * @var	integer	Valor 0
	 */
	const FETCH_ASSOC = 1;
	/**
	 * Para un array nomal
	 * @var	integer	Valor 1
	 */
	const FETCH_NUM = 2;
	/**
	 * Para mesclar un array asociativo con un array normal
	 * @var	integer	Valor 2
	 */
	const FETCH_BOTH = 3;
	/**
	 * Para un objeto estandar
	 * @var	integer	Valor 3
	 */
	const FETCH_OBJ = 4;
	/**
	 * Para un objeto especifico
	 * @var	integer	Valor 4
	 */
	const FETCH_INTO = 5;
	/**
	 * Para quenerar una clace especificada
	 * @var	integer	Valor 5
	 */
	const FETCH_CLASS = 6;
	/**#@-*/
	/**#@+
	 * @access	public
	 */
	/**
	 * Contruye el objeto y asigna las variables predeterminadas
	 * @param	string	$server	Nombre del servidor o array con la configuracion
	 * @param	string	$user	Nombre de usuario para acceder
	 * @param	string	$password	Clave de acceso
	 * @param	string	$dbname	Nombre de la base de datos
	 */
	public function __construct($server = 'localhost', $user = 'root', $password = '', $dbname = 'test'){
		if(is_string($server)){
			$this->server = $server;
			$this->user = $user;
			$this->password = $password;
			$this->dbname = $dbname;
		}elseif(is_array($server))
			$this->setConfig($server);
	}
	/**
	 * Acciones a tomar en la destruccion del objeto
	 */
	public function __destruct(){
	}
	/**
	 * Retorna variables generadas o privadas
	 * @param	string	$name	Nombre de la variagle
	 * @return	mixed	Variable solicitada
	 * @uses	SQLAbstract::$pdo	Para accesder a SQLAbstract::$_pdo
	 */
	public function __get($name){
		switch($name){
			case 'pdo':
				if(!$this->_pdo){
					$available = PDO::getAvailableDrivers();
					if(!in_array($this->driver, $available)){
						throw new Exception('Error: El driver no esta disponible');
					}
					$this->_pdo = new PDO($this->getDNS(), $this->user, $this->password);
				}
				return $this->_pdo;
				break;
			case 'fetchMode':
				switch($this->_fetchMode){
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
				return null;
		}
	}
	/**
	 * Para identificar la clace como string
	 * @return	string	El nombre de la clace con la ultima consulta realizada
	 */
	public function __toString() {
		return '[' . get_class($this) . ']' . $this->query;
	}
	/**
	 * Limpia un valor para ser ingresado como dato
	 * @static
	 * @param	array	$data	Un array para limpiar sus valores
	 * @return	array	Contiene el mismo array recivido pero despues de un mapeo de trim y addslashes
	 */
	static public function clean($data){
		return array_map('trim', array_map('addslashes', $data));
	}
	/**
	 * Asigna la configuracion para las conecciones
	 * @param	array|string	$oConfig	Se espera un array asociativo para asignar variables o un [@link http://mx.php.net/manual/es/function.parse-url.php URL de acceso]
	 * @return	mixed	This
	 */
	public function setConfig($oConfig = 'mysql://root:@localhost/test'){
		if(is_array($oConfig)){
			foreach($oConfig as $key=>$value)
				$this->$key = $value;
		}elseif(is_string($oConfig)){
			$oConfig = parse_url($oConfig);
			$this->driver = $oConfig['scheme'];
			$this->server = $oConfig['host'];
			$this->user = $oConfig['user'];
			$this->password = $oConfig['pass'];
			$this->dbname = substr($oConfig['path'], 1);
		}else
			throw new Exception("Error: Config Error");
		return $this;
	}
	/**
	 * Evalua si esta activa una session para realizar la consulta proporcionada.
	 * @param	string	$SQL	Un SQL bien formado
	 * @return	mixed	El valor retornado por this::doQuery()
	 */
	public function query($SQL){
		if(\core\tools\Security::hasSession())
			return $this->doQuery($SQL);
		return null;
	}
	/**
	 * Realiza una consulta SQL
	 * @param	string	$fields	Los campos a mostrar. Default: * (todos)
	 * @param	string	$table	El nombre de la tabla a consultar
	 * @param	array	$join	Un array asociativo con claves por tablas y valors por timpo de union
	 * @param	mixed	$where	Un SQL string de filtrado o un WhereCollection con el filtro a aplicar
	 * @param	string	$group	Un SQL string de agrupamiento
	 * @param	string	$order	Un SQL string de orden a aplicar
	 * @param	integer	$limit	La cantidad de registros a devolver por pagina. Default: 0 (todos)
	 * @param	integer	$page	La pagina de resultados deseada. Default: 0 (la primera)
	 * @return	resource	El enlace de la consulta
	 */
	public function select($fields = "*", $table, $join = array(), $where = '', $group = "", $order = "", $limit = 0, $page=0){
		if($table != ""){
			$this->query = "SELECT ";
			if(is_string($fields) && $fields != "")
				$this->query .= $fields . "\n";
			else
				$this->query .= "*\n";
			$this->query .= "FROM " . $table;
			if(is_array($join))
				foreach($join as $join_table=>$on)
					$this->query .= "\nLEFT JOIN " . $join_table . " ON " . $on;
			if($where != null || '' . $where != '')
				$this->query .= "\nWHERE " . $where;
			if($group != "")
				$this->query .= "\nGROUP BY " . $group;
			if($order != "")
				$this->query .= "\nORDER BY " . $order;
			if($limit > 0){
				if($page == "")
					$page = 0;
				$this->query .= "\nLIMIT " . ($page*$limit) . ", " . $limit;
			}
			return $this->doQuery($this->query);
		}
		return false;
	}
	/**
	 * Pivote los campos de una tabla para ofrecer un resumen de datos
	 * @param	string	$table	La tabla a la que se le aplicara el pivote
	 * @param	mixed	$where	Un SQL string de filtrado o un WhereCollection con el filtro a aplicar
	 * @param	string|array	$groupField	El(los) campo(s) principal de agraupamiento
	 * @param	string	$colField	Campo con las columnas
	 * @param	string	$resultFields	Campo con los valores
	 * @param	string	$groupType	Tipo de agrupamiento SQL. Default: MAX
	 * @return	boolean|resource	El enlace de la consulta o falso si no ahi valores a pivotear
	 */
	public function pivotSelect($table, $where, $groupField = '', $colField = '', $resultFields = '', $groupType = 'MAX'){
		if(!isset($where[$colField]))
			$where[$colField] = new core\database\WhereElement();
		$rCols = $this->select($colField, $table, "", $where, $colField);
		if($this->count($rCols)>0){
			if($resultFields == '' || $resultFields == NULL)
				$resultFields = $colField;
			if($groupField != '')
				$aPivoteFields[] = $groupField;
			$oldFetchMode = $this->_fetchMode;
			$this->setFetchMode(1);
			while($row = $this->fetch($rCols)){
				if($row[0] != ''){
					$aPivoteFields[] = $groupType . "(IF(" . $colField . "='" . $row[0] . "', "
										. $resultFields . ", NULL)) as '" . $row[0] . "'";
				}
			};
			$this->setFetchMode($oldFetchMode);
			$this->query = "SELECT ";
			$this->query .= implode(', ', $aPivoteFields) . "\n";
			$this->query .= "FROM " . $sTable . "\n";
			$this->query .= "WHERE " . $where . "\n";
			if($groupField != ""){
				$this->query .= "GROUP BY " . $groupField . "\n";
				$this->query .= "ORDER BY " . $groupField;
			}
			return $this->doQuery($this->query);
		}else
			return false;
	}
	/**
	 * Inserta un registro a la tabla
	 * @param	string	$table	Nombre de la tabla
	 * @param	ArrayAccess	$data	Un array asociativo o similar que contiene los datos a ser incertados, llaves como campos
	 * @return	mixed	Regresa el resultado generado por SQLAbstrac::doQuery()
	 */
	public function insert($table, $data){
		if($table!="" && $data != null){
			$keys = array_keys($data);
			$this->query = "INSERT INTO " . $table;
			if(is_string($keys[0]))
				$this->query .= " (" . implode(', ', $keys) . ")";
			$this->query .= " VALUES ";
			$aData = $this->clean((array) $data );
			$this->query .= $fields . " VALUES ('" . implode("', '", $aData) . "')";
			return $this->doQuery($this->query);
		}
		return false;
	}
	/**
	 * Remplaza un registro a la tabla
	 * @param	string	$table	Nombre de la tabla
	 * @param	ArrayAccess	$data	Un array asociativo o similar que contiene los datos a ser incertados, llaves como campos
	 * @return	mixed	Regresa el resultado generado por SQLAbstrac::doQuery()
	 */
	public function replace($table, $data){
		if($table!="" && $data != null){
			$this->query = "REPLACE INTO " . $table;
			$keys = array_keys($data);
			if(is_string($keys[0]))
				$this->query .= " (" . implode(', ', $keys) . ")";
			$aData = $this->clean((array) $data );
			$this->query .= " VALUES ('" . implode("', '", $aData) . "')";
			return $this->doQuery($this->query);
		}
		return false;
	}
	/**
	 * Actualiza un registro a la tabla
	 * @param	string	$table	Nombre de la tabla
	 * @param	string|array	$idKey	Un string con el nombre del campo principal o un array con los nombres de los campos que seran tomados en cuenta para el filtro
	 * @param	ArrayAccess	$data	Un array asociativo o similar que contiene los datos a ser incertados, llaves como campos
	 * @return	mixed	Regresa el resultado generado por SQLAbstrac::doQuery()
	 */
	public function update($table, $idKey, $data){
		$this->query = "UPDATE " . $table . " SET ";
		$where = new core\database\WhereCollection();
		foreach($data as $field => $value){
			if(($field != $idKey) && !in_array($field, $idKey)){
				switch(gettype($value)){
					case 'NULL':
						break;
					case 'string':
						$value = "'" . addslashes( trim( $value )) . "'";
					default:
						$upFields[] = $field . "='" . addslashes( trim( $value )) . "'";
				};
			}else
				$where[$field] = $value;
		};
		if(is_array($upFields)){
			$this->query .= implode(", ", $upFields);
			$this->query .= " WHERE " . $where;
			return $this->doQuery($this->query);
		}else
			throw new Exception("Error: No contiene filtro de actualizacion");
	}
	/**
	 * Realiza una eliminacion de registros segun filtro
	 * @param	string	$table	Nombre de la tabla
	 * @param	mixed	$where	Un SQL string de filtrado o un WhereCollection con el filtro a aplicar
	 * @return	mixed	Regresa el resultado generado por SQLAbstrac::doQuery()
	 */
	public function delete($table, $where){
		$this->query = "DELETE FROM " . $table;
		if(!($where instanceof core\database\WhereCollection))
			$where = new core\database\WhereCollection($where);
		$this->query .= " WHERE " . $where;
		return $this->doQuery($this->query);
	}
	/**
	 * Incerta una serie de datos en una tabla
	 * @param	string	$table	Nombre de la tabla
	 * @param	array	$data	Un array de ArrayAccess a ser incertados en la tabla, llaves como campos
	 * @return	mixed	Regresa el resultado generado por SQLAbstrac::doQuery() en una incercion extendida
	 */
	public function batchInsert($table, array $data){
		if($table!="" && $data != null){
			$keys = array_keys($data[0]);
			$this->query = "INSERT INTO " . $table;
			if(is_string($keys[0]))
				$this->query .= " (" . implode(', ', $keys) . ")";
			$this->query .= " VALUES ";
			$queryInserts = array();
			for ($i = 0; $i < count($data); $i++) {
				$aData = $this->clean((array) $data[$i] );
				$queryInserts[] = "('" . implode("', '", $aData) . "')";
			}
			$this->query .= implode(",\n", $queryInserts);
			return $this->doQuery($this->query);
		}
		return false;
	}
	/**
	 * Remplace una serie de datos en una tabla
	 * @param	string	$table	Nombre de la tabla
	 * @param	array	$data	Un array de ArrayAccess a ser incertados en la tabla, llaves como campos
	 * @return	mixed	Regresa el resultado generado por SQLAbstrac::doQuery() en una incercion extendida
	 */
	public function batchReplace($table, array $data){
		if($table!="" && $data != null){
			$this->query = "REPLACE INTO " . $table;
			$keys = array_keys($data[0]);
			if(is_string($keys[0]))
				$this->query .= " (" . implode(', ', $keys) . ")";
			$this->query .= " VALUES ";
			$queryInserts = array();
			for ($i = 0; $i < count($data); $i++) {
				$aData = $this->clean((array) $data[$i] );
				$query[] = "('" . implode("', '", $aData) . "')";
			}
			$this->query .= implode(",\n", $query);
			return $this->doQuery($this->query);
		}
		return false;
	}
	/**
	 * Remplace una serie de datos en una tabla
	 * @param	string	$table	Nombre de la tabla
	 * @param	string|array	$idKey	Un string con el nombre del campo principal o un array con los nombres de los campos que seran tomados en cuenta para el filtro
	 * @param	array	$data	Un array de ArrayAccess a ser actualizado en la tabla, llaves como campos
	 * @return	array	Regresa array con los resultados generados por SQLAbstrac::doQuery() para cada elemento
	 */
	public function batchUpdate($table, $idKey, array $data){
		$return = array();
		for($i = 0; $i < count($data); $i++) {
			$this->query = "UPDATE " . $table . " SET ";
			$where = new core\database\WhereCollection();
			$current = $data[$i];
			foreach($current as $field => $value){
				if(($field != $idKey) && !in_array($field, $idKey)){
					switch(gettype($value)){
						case 'NULL':
							break;
						case 'string':
							$value = "'" . addslashes( trim( $value )) . "'";
						default:
							$upFields[] = $field . "='" . addslashes( trim( $value )) . "'";
					};
				}else
					$where[$field] = $value;
			};
			if(is_array($upFields)){
				$this->query .= implode(", ", $upFields);
				$this->query .= " WHERE " . $where;
				try{
					$return[] = $this->doQuery($this->query);
				}catch(Exception $e){
					$return[] = $e->getMessage();
				}
			}else
				$return[] = "Error: No contiene filtro de actualizacion";
		}
		return $return;
	}
	/**
	 * Tranforma un resultado de una consulta de select en un texto separado por comas
	 * @param	resource	$resultset	El resource del cual se quieren los datos
	 * @param	array	$fields	Es un qrray asociativo con las llaves como nombre de los campos que se desean y por valor los titulos de las columnas
	 * @return	string	El texto con los valores seprados por comas
	 */
	public function toCSV($resultset, $fields = array()){
		$string = array();
		if(count($fields)<1)
			$fields = $this->getFields($resultset);
		$string[] = implode(',', $fields);
		$oldFetchMode = $this->_fetchMode;
		$this->_fetchMode = 1;
		while($row = $this->fetch($resultset)){
			$data = array();
			if(count($fields)>0){
				foreach($fields as $field=>$name)
					$data[] = (preg_match('/^0/', $row[$field]))
							? "'" . $row[$field]
							: $row[$field];
			}else
				$data = $row;
			$string[]='"' . implode('","', $data) . '"';
		}
		$this->_fetchMode = $oldFetchMode;
		return implode("\r\n", $string);
	}
	/**
	 * Tranforma un resultado de una consulta de select en un texto separado por comas
	 * @param	resource	$resultset	El resource del cual se quieren los datos
	 * @return	string	Texto condel XML
	 */
	public function toXML($resultset){
			$string = array();
		$string[] = '<table>';
		$oldFetchMode = $this->_fetchMode;
		$this->_fetchMode = 1;
		while($row = $this->fetch($resultset)){
			$string[] = "\t" . '<row>';
			$string[] = "\t\t" . \core\Katra::arrayToXML($row);
			$string[] = "\t" . '</row>';
		}
		$this->_fetchMode = $oldFetchMode;
		$string[] = '</table>';
		return implode("\r\n", $string);
	}
	/**#@-*/
	/**#@+
	 * Funciones abtractas a declarar
	 * @abstract
	 */
	/**
	 * Genera los DNS para una coneccion con PDO
	 * @access	protected
	 * @return	string	El string DNS de coneccion
	 * @uses	SQLAbstract::$pdo	Lo utiliza para generar el [@link http://mx.php.net/manual/es/pdo.construct.php PDO]
	 */
	abstract protected function getDNS();
	/**
	 * Obtiene los campos de una consulta
	 * @access	protected
	 * @param	resource	$resultset	El resource del cual se quieren los campos
	 * @return	array	Los campos de la consulta
	 */
	abstract protected function getFields($resultset);
	/**
	 * Ejecuta una consulta SQL
	 * @access	protected
	 * @param	string	$SQL	Un SQL bien formado
	 * @return	mixed	El resource obtenido de la ejecucion del SQL o lo implementado en la clace
	 * @uses	SQLAbstrac::select()	Lo utiliza para ejecutar el SQL que genera
	 * @uses	SQLAbstrac::insert()	Lo utiliza para ejecutar el SQL que genera
	 * @uses	SQLAbstrac::replace()	Lo utiliza para ejecutar el SQL que genera
	 * @uses	SQLAbstrac::update()	Lo utiliza para ejecutar el SQL que genera
	 * @uses	SQLAbstrac::delete()	Lo utiliza para ejecutar el SQL que genera
	 * @uses	SQLAbstrac::pivotSelect()	Lo utiliza para ejecutar el SQL que genera
	 * @uses	SQLAbstrac::batchInsert()	Lo utiliza para ejecutar el SQL que genera
	 * @uses	SQLAbstrac::batchReplace()	Lo utiliza para ejecutar el SQL que genera
	 * @uses	SQLAbstrac::batchUpdate()	Lo utiliza para ejecutar el SQL que genera
	 */
	abstract protected function doQuery($SQL);
	/**
	 * Cuenta los registros debueltos por una consulta
	 * @access	public
	 * @param	resource	$resultset	El resource del cual se quiere el conteo
	 * @return	integer	La cantidad de registros
	 */
	abstract public function count($resultset);
	/**
	 * Asigna el tipo de retorno de una busqueda de registro de una consulta
	 * @access	public
	 * @param	integer	$mode	Tipo de retorno de fetch
	 * @param	string|object	$class	Nombre de la clace u objeto de retorno
	 * @param	array	$ctorargs	Opciones de construccion de clace especifica
	 * @return	mixed	This
	 */
	abstract public function setFetchMode($mode, $class = 'object', $ctorargs = array());
	/**
	 * Obten el registro siguiente del recurso
	 * @access	public
	 * @param	resource	$resultset	El resource del cual se quiere el conteo
	 * @return	mixed	Una variable de tipo de formato segun fetchmode
	 * @uses	SQLAbstract::fetchAll()	Lo utiliza para generar los elementos del array
	 */
	abstract public function fetch($resultset);
	/**
	 * Obten el registro siguiente del recurso
	 * @access	public
	 * @param	resource	$resultset	El resource del cual se quiere el conteo
	 * @return	array	Un array con todos los registros segun fetchmode
	 */
	abstract public function fetchAll($resultset);
	/**
	 * Regreza el apuntador de registros al primero
	 * @param	resource	$resultset	El resource del cual se quiere el conteo
	 * @return	mixed	This
	 * @uses	SQLAbstract::fetchAll()	Lo utiliza para reiniciar el apuntador al primer registro
	 */
	abstract public function resetFetch($resultset);
	/**#@-*/
}