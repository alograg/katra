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
 * Abstact de Valores de base de datos
 *
 * Define funciones basicas y funciones que se deben de implementar una tabla.
 * @package	Katra.database
 * @subpackage	dal
 * @abstract
 */
abstract class ValueAbstract {
	public function asWhere(){
		$return = new \core\database\WhereCollection((array) $this);
		return $return;
	}
	public function __toString(){
		$return = $this->asWhere();
		return (string) $return;
	}
}