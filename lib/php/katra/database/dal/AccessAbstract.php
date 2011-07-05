<?php
namespace core\database\dal;
/**
 * Abstact del acceso a base de datos por Waakun
 *
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://alograg.me}
 * @package	Katra.database
 * @since	2011-03-01
 * @subpackage	dal
 * @version	$Id$
 */

/**
 * Abstact del acceso a base de datos
 *
 * Define funciones basicas y funciones que se deben de implementar una tabla.
 * @package	Katra.database
 * @subpackage	dal
 * @abstract
 */
abstract class AccessAbstract implements \Iterator {
	/**#@+
	 * @abstract
	 * @access	protected
	 */
	/**
	 * Nombre de la tabla a acceder
	 * @var	string
	 */
	abstract protected $table;
	/**
	 * Definicion de campos, como array asociativo.
	 * @var	array
	 */
	abstract protected $fields;
	/**
	 * Define los campos identificadores de la tabla, array de nombres
	 * @var	array
	 */
	abstract protected $keys;
	/**
	 * Define la clace de los objetos que se regrezan
	 * @var unknown_type
	 */
	abstract protected $baseClass;
	/**#@-*/
	/**#@+
	 * @access	protected
	 */
	protected $adapter;
	protected $data = false;
	protected $filter = null;
	protected $order = null;
	protected $limit = null;
	protected $page = 0;
	protected $position = 0;
	/**#@-*/
	/**#@+
	 * @access	public
	 */
	/**
	 * Constructor del objeto
	 */
	public function __construct(){
		$this->adapter = new \core\database\mysql\Adapter($GLOBALS['config']['Database'], true);
		$this->adapter->setFetchMode(
				\core\database\SQLAbstract::FETCH_CLASS,
				$this->baseClass
			);
	}
	/**
	 * Transforma los datos consultados en un JSON
	 * @return	string
	 */
	public function toJSON(){
		return json_encode($this->data);
	}
	/**
	 * Convierte un array y objeto a un objeto de la clase predeterminada para valores
	 * @param	ValueAbstract|array	$row	Array asociativo o extencion ValueAbstract para la conversion a elemento de tabla
	 * @return	ValueAbstract	Si el objeto ha sido tranformado o ya es un elemento de tabla
	 */
	public function convertToBase($row){
		if(!($row instanceof \core\waakun\dal\ValueAbstract)){
			$class = $this->baseClass;
			$newObj = new $class($row);
			return $newObj;
		}else
			return $row;
		return null;
	}
	/**
	 * Crea un registro de la tabla
	 * @param	ValueAbstract|array	$row	Array asociativo o extencion ValueAbstract crear un registro
	 * @return	mixed	Lo que devuelva el adaptador tras agregar un registro
	 */
	public function create($row){
		$row = $this->convertToBase($row);
		$return = $this->adapter->insert($this->table, $row);
		if(is_array($this->data))
			$this->reload();
		return $return;
	}
	/**
	 * Actualiza un registro de la tabla
	 * @param	ValueAbstract|array	$row	Array asociativo o extencion ValueAbstract con datos de actualizacion y referencia
	 * @return	mixed	Lo que devuelva el adaptador tras agregar un registro
	 */
	public function update($row){
		$row = $this->convertToBase($row);
		$return = $this->adapter->update($this->table, $this->keys, $row);
		if(is_array($this->data))
			$this->reload();
		return $return;
	}
	/**
	 * Remplaza un registro de la tabla
	 * @param	ValueAbstract|array	$row	Array asociativo o extencion ValueAbstract con datos de actualizacion y referencia
	 * @return	mixed	Lo que devuelva el adaptador tras agregar un registro
	 */
	public function replace($row){
		$row = $this->convertToBase($row);
		$return = $this->adapter->replace($this->table, $row);
		if(is_array($this->data))
			$this->reload();
		return $return;
	}
	/**
	 * Cambia la pagina de los registros consultados
	 * @param	integer	$page
	 * @return	array	Los datos de la nueva pagina
	 * @uses	AccessAbstract::reload()
	 */
	public function gotoPage($page){
		if($this->limit > 0){
			$this->page = $page;
			return $this->reload();
		}
		return $this->data;
	}
	/**
	 * Asigna los valores para una consulta
	 * @param	ValueAbstract|WhereCollection	$filter	El filtro de datos
	 * @param	string	$order	Un string de orden
	 * @param	integer	$limit	La limitacion por pagina
	 * @param	integer	$page	La pagina a implementar
	 * @return	array	Los datos consultados
	 * @uses	AccessAbstract::reload()
	 */
	public function read($filter = null, $order = null, $limit = 0, $page = 0){
		$this->filter = $filter;
		$this->order = $order;
		$this->limit = $limit;
		$this->page = $page;
		return $this->reload();
	}
	/**
	 * Realiza la consulta en la base de datos con los datos guardados en las variables asignadas
	 * @return	array	Un arrar de elementos de la tabla
	 */
	public function reload(){
		$this->data = $this->adapter->fetchAll($this->adapter->select(
				"*",
				$this->table,
				null,
				$this->filter,
				null,
				$this->order,
				$this->limit,
				$this->page
			));
		return $this;
	}
	/**
	 * Elimina registros de la tabla segun los filtros proporcionados
	 * @param	ValueAbstract|WhereCollection	$filter	El filtro de datos
	 */
	public function remove($filter){
		$filter = $this->convertToBase($filter);
		if($filter){
			$return = $this->adapter->delete($this->table, $filter);
			if(is_array($this->data))
				$this->reload();
			return $return;
		}
		return false;
	}
	/**
	 * Funcion del Iterator para reiniciar la pocicion del apuntador
	 */
	public function rewind() {
		$this->position = 0;
	}
	/**
	 * Funcion del Iterator para obtener el elemento actual
	 */
	public function current() {
		if(is_array($this->data))
			return $this->data[$this->position];
		return false;
	}
	/**
	 * Funcion del Iterator para obtener la posicion del apuintador
	 */
	public function key() {
		return $this->position;
	}
	/**
	 * Funcion del Iterator para mover una pocicion el pauntador
	 */
	public function next() {
		if(is_array($this->data))
			++$this->position;
		return false;
	}
	/**
	 * Funcion del Iterator para validar que existe la poscicion
	 */
	public function valid() {
		if(is_array($this->data))
			return isset($this->data[$this->position]);
		return false;
	}
	/**#@-*/
}