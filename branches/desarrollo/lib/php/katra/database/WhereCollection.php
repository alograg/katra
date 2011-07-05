<?php
namespace core\database;
/**
 * Contiene una coleccion de propiedades que se pueden tranformar en un SQL de filtrado
 *
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://alograg.me}
 * @package	Katra
 * @since	2011-03-01
 * @subpackage	database
 * @version	$Id$
 */

/**
 * Contiene propiedades de tipo WhereElement que se utilizan para generar los filtros
 *
 * @package	Katra
 * @subpackage	database
 * @uses	SQLAbsctract	Es utiliado para generar los filtrados de consulta
 */
class WhereCollection extends ArrayObject {
	/**
	 * Contiene el tipo de union de los elementos
	 * @var	string
	 * @uses	WhereCollection::setUnion()	Para ser guardado
	 * @uses	WhereCollection::getUnion()	Para ser consultado
	 */
	private $_union = 'AND';
	/**
	 * Contrulle el objeto
	 * @param	array	$where	Un array de elementos a definir
	 */
	public function __construct($where = array()){
		if(is_array($where)){
			foreach($where as $key => $value)
				$this->$key = $value;
		}
	}
	/**
	 * Proceso los valores asignados para generar la propiedad y el valor de tipo WhereElement
	 * @param	string	$name	El nombre de la propiedad
	 * @param	mixed	$value	El valor a ser procesado
	 */
	public function __set($name, $value) {
		$type = gettype($value);
		$parseValue = new \core\database\WhereElement();
		switch($type){
			case 'boolean':
			case 'integer':
			case 'double':
			case 'string':
			case 'resource':
			case 'NULL':
			default:
				$parseValue->valor = $value;
				break;
			case 'array':
				if(array_key_exists('valor', $value))
					$parseValue = new core\database\WhereElement($value);
				else
					$parseValue->valor = $value;
				break;
			case 'object':
				if($value instanceof WhereElement)
					$parseValue = $value;
				else
					$parseValue = new core\database\WhereElement($value);
				break;
		}
		$parseValue->fieldName = $name;
		$this[$name] = $parseValue;
	}
	/**
	 * Retorna variables generadas o privadas
	 * @param	string	$name	Nombre de la variable
	 * @return	mixed	Variable solicitada
	 */
	public function __get($name) {
		return $this[$name];
	}
	/**
	 * Procesa y regesa un sctring formateado para ser utilizado en un SQL
	 * @return	string	La sentencia
	 */
	public function __toString() {
		return implode(' ' . $this->_union . "\n", $this);
	}
	/**
	 * Establece el tipo de union de los elementos
	 * @return	WhereCollection	This
	 */
	public function setUnion($union){
		$this->_union = $union;
		return $this;
	}
	/**
	 * Lee el tipo de union establecido
	 * @return	string
	 */
	public function getUnion(){
		return $this->_union = $union;
	}
}