<?php
namespace core\database;
/**
 * Este objeto es utilizado para tranformar un objeto con parametros en un string para un filtrado
 *
 * @author	Henry I. Galvez T. <alograg@alograg.me>
 * @copyright	Copyright (c) 2008, {@link http://alograg.me}
 * @package	Katra
 * @since	2011-03-01
 * @subpackage	database
 * @version	$Id$
 */

/**
 * Esta clace guarda los parametros y valores para la generacion de una sentecia SQL de filtro
 *
 * @package	Katra
 * @subpackage	database
 * @uses	WhereCollection	Es utiliado para los elementos de la collecion
 */
class WhereElement {
	/**#@
	 * Propiedades
	 * @access public
	 */
	/**
	 * Nombre del campo
	 * @var	string
	 */
	public $fieldName = null;
	/**
	 * Tipo de union de valores
	 * @var	string	Tipo AND|OR
	 */
	public $union = 'AND';
	/**
	 * Tipo de operador, cualquier operador aceptado por SQL
	 * @var	string
	 */
	public $operator = '=';
	/**
	 * Los valores de filtro
	 * @var	midex
	 */
	public $valor = null;
	/**
	 * Parametros o funciones SQL en ves de utilizar valores
	 * @var	string
	 */
	public $parametro = null;
	/**
	 * Para lealizar busquedas en sets, el nombre dle campo set
	 * @var	string
	 */
	public $inset = null;
	/**
	 * Si no contiene ni valor ni parametros ni inset, esta propiedad dice si debe buscar valoers vacios
	 * @var	boolean
	 */
	public $vacio = false;
	/**
	 * Si no contiene ni valor ni parametros ni inset, esta propiedad dice si debe buscar valoers vacios
	 * @var	boolean
	 */
	public $nulo = false;
	/**#@-*/
	/**
	 * Contrulle el objeto
	 * @param unknown_type $where
	 */
	public function __construct($where = array()){
		if(is_array($where)){
			foreach($this as $key => $value)
				$this->$key = $value;
		}
	}
	/**
	 * Procesa y regesa un sctring formateado para ser utilizado en un SQL
	 * @return	string	La sentencia
	 */
	public function __toString() {
		$Where = array();
		if(!is_null($this->inset)){
			if(is_array($this->valor)){
				foreach($this->valor as $key => $value)
					$Where[]='FIND_IN_SET("' . $value . '",' . $this->inset . ')';
			}else
				$Where[] = 'FIND_IN_SET("' . $this->valor . '",' . $this->inset . ')';
		}elseif(!is_null($this->parametro)){
			if(is_array($this->parametro)){
				if(is_array($this->operator)){
					foreach($this->parametro as $key => $parametro)
						$Where[] = $this->fieldName . " " . $this->operator[$key] . " " . $parametro;
				}else{
					foreach($this->parametro as $parametro)
						$Where[] = $this->fieldName . " " . $this->operator . " " . $parametro;
				}
			}else
				$Where[] = $field . $this->operator . $this->parametro;
		}elseif(!is_null($this->valor)){
			if(is_array($this->valor)){
				if(is_array($this->operator)){
					foreach($this->valor as $key => $value)
						$Where[] = $field . " " . $this->operator[$key] . " '"
									. core\database\SQLAbsctract::clean($value) . "'";
				}else{
					if($this->operator == '=')
						 $Where[] = $field . " IN ('"
						 			. implode("', '",core\database\SQLAbsctract::clean($sValue))
						 			. "')";
					else
						$Where[] = $field . " " . $this->operator . " '"
								. core\database\SQLAbsctract::clean($sValue) . "'";
				}
			}else
				$Where[] = $field . " " . $this->operator . " '"
							. core\database\SQLAbsctract::clean($sValue) . "'";
		}else{
			if($this->vacio)
				$Where[] = $field . " = ''";
			else
				$Where[] = $field . " = ''";
			if($this->nulo)
				$Where[] = "ISNULL(" . $field . ")";
			else
				$Where[] = "!ISNULL(" . $field . ")";
			$Where = array('(' . implode(' ' . $this->union . ' ', $Where) . ")\n");
		}
		return '(' . implode(' ' . $this->union . ' ', $Where) . ")\n";
	}
}