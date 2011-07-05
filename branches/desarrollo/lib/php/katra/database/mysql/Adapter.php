<?php
namespace core\database\mysql;
/**
 * Contiene la clase para manejo de datos de MySQL
 *
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright(c) 2008, {@link http://alograg.me}
 * @package	Katra.database
 * @since	2011-03-01
 * @subpackage	mysql
 * @version	$Id$
 */

/**
 * Clase de control de acceso a MySQL
 *
 * @package	Katra.database
 * @subpackage	mysql
 * @uses	SQLAbsctract	Es utiliado para generar los filtrados de consulta
 */
class Adapter extends \core\database\SQLAbstract {
	/**
	 * Preconfigura el objeto
	 * @param	array|string	$oConfig	Se espera un array asociativo para asignar variables o un URL de acceso [@link http://mx.php.net/manual/es/function.parse-url.php]
	 */
	public function __construct($oConfig = 'mysql://root:@localhost/test', $useMySQLi = false){
		$this->setConfig($oConfig);
		$this->driver = 'mysql';
		$this->enambleMySQLi($useMySQLi);
	}
	/**
	 * Genera los DNS para una coneccion con PDO
	 * @access	protected
	 * @return	string	El string DNS de coneccion
	 * @uses	SQLAbstract::$pdo	Lo utiliza para generar el PDO [@link http://mx.php.net/manual/es/pdo.construct.php]
	 */
	protected function getDNS(){
		$s = 'mysql:';
		$s .= 'host=' . $this->server . ';';
		$s .= 'dbname=' . $this->server;
		return $s;
	}
	/**
	 * Obtiene los campos de una consulta
	 * @access	private
	 * @param	resource	$resultset	El resource del cual se quieren los campos
	 * @return	array	Los campos de la consulta
	 */
	protected function getFields($resultset){
		if($resultset instanceof \mysqli_result){
			$fields = $resultset->fetch_fields();
			for($i = 0; $i < count($fields); $i++)
				$field[] = $fields[$i]->name;
			return $field;
		}elseif(get_resource_type($resultset) == 'mysql result'){
			$fields = mysql_num_fields($result);
			for($i = 0; $i < $fields; $i++)
				$field[] = mysql_field_name($resultset, $i);
			return $field;
		}
		return false;
	}
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
	 * @uses	SQLAbstrac::query()	Lo utiliza para ejecutar el SQL
	 */
	protected function doQuery($SQL){
		if(KATRA_DEBUG)
			\core\Katra::startTimer();
		$this->query = $SQL;
		$return = null;
		$rs = ($this->driver == 'mysqli') ?
					$this->useMySQLi($SQL) :
					$this->useMySQL($SQL);
		if(!(substr_count($SQL,'SELECT ') > 0 || substr_count($SQL,'SHOW ') > 0)){
			if($this->getLastInsertId($rs)>0)
				$return = $this->getLastInsertId($rs); // Regreza el ultimo id insertado
			$return = $this->getInfo($rs); // Regreza la informacion de la ultima consulta
			if(!$return)
				$return = $this->getAfectedRows($rs); // Regreza la informacion de la ultima consulta
		}
		$return = $this->getError($rs, $return);
		if(KATRA_DEBUG){
			$time = \core\Katra::stopTimer();
			$logQuery = '- [' . get_class($this) . '] - time: ' . $time
							. "s -\r\n" . $SQL . "\r\n"
							. get_class($rs);
			\core\Katra::trace($logQuery);
			\core\Katra::addToLogFile('query', $logQuery, "YmdHi");
		}
		return $return;
	}
	/**
	 * Establece el uso de la libreria MySQLi
	 * @param	bollean	$b
	 * @return	Adapter	This
	 */
	public function enambleMySQLi($b){
		$this->driver = ($b) ? 'mysqli' : 'mysql';
		return $this;
	}
	/**
	 * Realiza la consulta utilizando la libreria tradicional de MySQL
	 * @param	string	$sql	La consulta
	 * @return	resource	El resultado de {@link http://mx2.php.net/manual/es/function.mysql-query.php mysql_query}
	 */
	private function useMySQL($sql){
		mysql_connect($this->server, $this->user, $this->password);
		mysql_select_db($this->dbname);
		mysql_set_charset('utf8');
		return mysql_query($sql);
	}
	/**
	 * Realiza la consulta utilizando la libreria tradicional de MySQLi
	 * @param	string	$sql	La consulta
	 * @return	\mysqli_stmt	El {@link http://mx2.php.net/manual/es/mysqli.prepare.php objeto stmt} de la consulto
	 */
	private function useMySQLi($sql){
		$mysqli = new \mysqli($this->server, $this->user, $this->password, $this->dbname);
		$mysqli->set_charset("utf8");
		if(substr_count($sql,'SELECT ') > 0 || substr_count($SQL,'SHOW ') > 0){
			$result = $mysqli->query($sql);
			if($result)
				return $result;
		}else{
			$stmt = $mysqli->prepare($sql);
			if($stmt){
				$stmt->execute();
				return $stmt;
			}
		}
		return $mysqli;
	}
	/**
	 * Obtiene el ultimo id agregado
	 * @param	\mysqli_stmt|resource	$resultset	El objeto a ser analisado
	 * @return	integer	El identificador del registro agregado
	 */
	private function getLastInsertId($resultset){
		return ($this->driver == 'mysqli') ?
				$resultset->insert_id :
				mysql_insert_id();
	}
	/**
	 * Obtiene la informacion de la consulta
	 * @param	\mysqli_stmt|resource	$resultset	El objeto a ser analisado
	 * @return	string	El resultado de la consulta, {@link http://mx2.php.net/manual/es/function.mysql-info.php MySQL} o {@link http://mx2.php.net/manual/es/mysqli.info.php MySQLi}
	 */
	private function getInfo($resultset){
		return ($this->driver == 'mysqli') ?
				$resultset->info :
				mysql_info($resultset);
	}
	/**
	 * Obtiene la cantidad de registros afectados
	 * @param	\mysqli_stmt|resource	$resultset	El objeto a ser analisado
	 * @return	integer	El resultado de la consulta, {@link http://mx2.php.net/manual/es/function.mysql-affected-rows.php MySQL} o {@link http://mx2.php.net/manual/es/mysqli.affected-rows.php MySQLi}
	 */
	private function getAfectedRows($resultset){
		return ($this->driver == 'mysqli') ?
				$resultset->affected_rows :
				mysql_affected_rows($resultset);
	}
	/**
	 * Obtiene la cantidad de registros afectados
	 * @param	\mysqli_stmt|resource	$resultset	El objeto a ser analisado
	 * @param	mixed	$return	El valor a regresar si no contiene errores el $resultset
	 * @return	mixed	Una descripcion completa del error, el valor de return si existe o $resultset
	 */
	private function getError($resultset, $return){
		$error = ($this->driver == 'mysqli') ?
					$resultset->errno :
					mysql_errno();
		if($error == 0)
			return ($return) ? $return : $resultset;
		return ($this->driver == 'mysqli') ?
				$resultset->errno . ': ' . $resultset->error :
				mysql_errno() . ': ' . mysql_error();
	}
	/**
	 * Cuenta los registros debueltos por una consulta
	 * @access	public
	 * @param	resource	$resultset	El resource del cual se quiere el conteo
	 * @return	integer	La cantidad de registros
	 */
	public function count($resultset){
		return ($this->driver == 'mysqli') ?
				$resultset->num_rows :
				mysql_num_rows($resultset);
	}
	/**
	 * Asigna el tipo de retorno de una busqueda de registro de una consulta
	 * @access	public
	 * @param	integer	$mode	Tipo de retorno de fetch
	 * @param	string|object	$class	Nombre de la clace u objeto de retorno
	 * @param	array	$ctorargs	Opciones de construccion de clace especifica
	 * @return	mixed	This
	 */
	public function setFetchMode($mode, $class = 'stdClass', $ctorargs = array()){
		$this->_fetchMode = $mode;
		$this->_fetchObject = $class;
		return $this;
	}
	/**
	 * Obten el registro siguiente del recurso
	 * @access	public
	 * @param	resource	$resultset	El resource del cual se quiere el conteo
	 * @return	mixed	Una variable de tipo de formato segun fetchmode
	 * @uses	SQLAbstract::fetchAll()	Lo utiliza para generar los elementos del array
	 */
	public function fetch($resultset){
		$return = false;
		if($resultset instanceof \mysqli_result){
			switch($this->_fetchMode){
				case 6:
				case 5:
				case 4:
					$class = (is_string($this->_fetchObject)) ? $this->_fetchObject : get_class($this->_fetchObject) ;
					$return = $resultset->fetch_object($class);
					break;
				case 3:
				case 2:
				case 1:
				default:
					$return = $resultset->fetch_array($this->_fetchMode);
			}
		}else{
			switch($this->_fetchMode){
				case 6:
				case 5:
				case 4:
					$class = (is_string($this->_fetchObject)) ? $this->_fetchObject : get_class($this->_fetchObject) ;
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
	 * Obten el registro siguiente del recurso
	 * @access	public
	 * @param	resource	$resultset	El resource del cual se quiere el conteo
	 * @return	array	Un array con todos los registros segun fetchmode
	 */
	public function fetchAll($resultset){
		$return = array();
		if($this->driver == 'mysqli'
			&& $resultset instanceof \mysqli_result
			&& in_array($this->_fetchMode, array(1,2,3)))
			return $resultset->fetch_all($this->_fetchMode);
		while($row = $this->fetch($resultset))
			$return[] = $row;
		$this->resetFetch($resultset);
		return $return;
	}
	/**
	 * Regreza el apuntador de registros al primero
	 * @param	resource	$resultset	El resource del cual se quiere el conteo
	 * @return	mixed	This
	 * @uses	SQLAbstract::fetchAll()	Lo utiliza para reiniciar el apuntador al primer registro
	 */
	public function resetFetch($resultset){
		$error = ($this->driver == 'mysqli') ?
					$resultset->data_seek(0) :
					mysql_data_seek($resultset, 0);
		return $this;
	}
}
