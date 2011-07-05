<?php
namespace core\database\sqlite;
/**
 * Contiene la clase para manejo de datos de SQLite
 *
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, Cuatromedios.com  {@link http://www.cuatromedios.com}
 * @package	Katra.database
 * @since	2011-03-01
 * @subpackage	sqlite
 * @version	$Id$
 */

/**
 * Clase de control de acceso a SQLite
 *
 * @package	Katra.database
 * @subpackage	sqlite
 * @uses	SQLAbsctract	Es utiliado para generar los filtrados de consulta
 */
class Adapter extends \core\database\SQLAbstract {
	/**
	 * Preconfigura el objeto
	 * @param	array|string	$oConfig	Se espera un array asociativo para asignar variables o un URL de acceso [@link http://mx.php.net/manual/es/function.parse-url.php]
	 */
	public function __construct($oConfig = 'sqlite://root:@localhost/test'){
		$this->setConfig($oConfig);
		$this->driver = 'sqlite2';
		try{
			$dns = $this->getDNS();
			$this->_pdo = new \PDO($this->getDNS());
		}catch(Exception $e){
			throw new \Exception('Error: El driver no esta disponible');
		}
	}
	/**
	 * Genera los DNS para una coneccion con PDO
	 * @access	protected
	 * @return	string	El string DNS de coneccion
	 * @uses	SQLAbstract::$pdo	Lo utiliza para generar el PDO [@link http://mx.php.net/manual/es/pdo.construct.php]
	 */
	protected function getDNS(){
		$return = KATRA_PATH
				. '/database/sqlite/data/'
				. $this->dbname
				. '.sq2';
		$return = str_replace('\\', '/', $return);
		if(!is_file($return)){
			$tmp = sqlite_open($return, 0666);
			sqlite_close ($tmp);
		}
		if(is_file($return))
			return $this->driver . ':' . $return;
		else
			throw new \Exception('Error: No se tiene accesso.');
	}
	/**
	 * Obtiene los campos de una consulta
	 * @access	private
	 * @param	resource	$resultset	El resource del cual se quieren los campos
	 * @return	array	Los campos de la consulta
	 */
	protected function getFields($resultset){
		$field = array();
		$fields = $resultset->columnCount();
		for($i = 0; $i < $fields; $i++){
			$f = (object) $resultset->getColumnMeta();
			$field[] = $f->name;
		}
		if(count($field) > 0)
			return $field;
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
	 */
	protected function doQuery($SQL){
		if(KATRA_DEBUG)
			\core\Katra::startTimer();
		$this->query = $SQL;
		$return = null;
		$rs = $this->_pdo->prepare(utf8_encode($SQL));
		$rs->execute();
		if(!(substr_count($SQL,'SELECT ') > 0 || substr_count($SQL,'SHOW ') > 0)){
			if($this->_pdo->lastInsertId() > 0)
				$return = $this->_pdo->lastInsertId(); // Regreza el ultimo id insertado
			$return = $rs->rowCount(); // Regreza la informacion de la ultima consulta
		}
		if($rs->errorCode() != 0){
			$errorMsg = $rs->errorInfo();
			$return = $rs->errorCode() . ': ' . $errorMsg[0] . ': ' . $SQL;
		}else
			$return = $rs;
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
	 * Cuenta los registros debueltos por una consulta
	 * @access	public
	 * @param	resource	$resultset	El resource del cual se quiere el conteo
	 * @return	integer	La cantidad de registros
	 */
	public function count($resultset){
		return $resultset->rowCount();
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
		switch($this->_fetchMode){
			case 6:
			case 5:
			case 4:
				$class = (is_string($this->_fetchObject)) ? $this->_fetchObject : get_class($this->_fetchObject) ;
				$return = $resultset->fetch(5);
				if($class != 'stdClass'){
					$newCalss = new $class();
					foreach($newCalss as $attr => $value)
						$newCalss->$attr = $return->$attr;
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
	 * Obten el registro siguiente del recurso
	 * @access	public
	 * @param	resource	$resultset	El resource del cual se quiere el conteo
	 * @return	array	Un array con todos los registros segun fetchmode
	 */
	public function fetchAll($resultset){
		$return = array();
		if(in_array($this->_fetchMode, array(1,2,3)))
			return $resultset->fetchAll($this->_fetchMode + 1);
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
		$resultset->closeCursor();
		$resultset->execute();
		return $this;
	}
}
