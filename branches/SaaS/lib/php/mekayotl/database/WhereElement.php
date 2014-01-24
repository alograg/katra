<?php
//namespace core\database;
/**
 * Este objeto es utilizado para transformar un objeto con parámetros en un
 * cadena de texto para un filtrado
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
 * Esta clase guarda los parámetros y valores para la generación de una
 * sentencia SQL de filtro
 *
 * @package Mekayotl
 * @subpackage database
 * @uses Mekayotl_database_WhereCollection Es utilizado para los elementos de la
 * colección
 */
class Mekayotl_database_WhereElement
{
    /**#@
     * Propiedades
     * @access public
     */
    /**
     * Nombre del campo
     * @var string
     */

    public $fieldName = NULL;
    /**
     * Tipo de union de valores
     * @var string Tipo AND|OR
     */
    public $union = 'AND';
    /**
     * Tipo de operador, cualquier operador aceptado por SQL
     * @var string
     */
    public $operator = '=';
    /**
     * Los valores de filtro
     * @var midex
     */
    public $valor = NULL;
    /**
     * Parámetros o funciones SQL en ves de utilizar valores
     * @var string
     */
    public $parametro = NULL;
    /**
     * Para realizar búsquedas en sets, el nombre del campo set
     * @var string
     */
    public $inset = NULL;
    /**
     * Si no contiene valor ni parámetros ni inset, esta propiedad dice si debe
     * buscar valores vacíos
     * @var boolean
     */
    public $vacio = NULL;
    /**
     * Si no contiene ni valor ni parámetros ni inset, esta propiedad dice si
     * debe buscar valores vacíos
     * @var boolean
     */
    public $nulo = NULL;
    /**#@-*/
    /**
     * Construye el objeto
     * @param array $where
     */

    public function __construct($where = array())
    {
        if (is_array($where)) {
            foreach ($this as $key => $value)
                $this->$key = $value;
        } elseif (is_string($where)) {
            $this->fieldName = $where;
        }
    }

    protected function _withInSet(&$where)
    {
        $field = $this->fieldName;
        if (is_array($this->valor)) {
            foreach ($this->valor as $key => $value) {
                $where[] = 'FIND_IN_SET("'
                        . Mekayotl_database_SQLAbstract::clean($value) . '",'
                        . $this->inset . ')';
            }
        } else {
            $where[] = 'FIND_IN_SET("'
                    . Mekayotl_database_SQLAbstract::clean($this->valor) . '",'
                    . $this->inset . ')';
        }
    }

    protected function _withParameters(&$where)
    {
        $field = $this->fieldName;
        if (is_array($this->parametro)) {
            if (is_array($this->operator)) {
                foreach ($this->parametro as $key => $parametro) {
                    $where[] = $this->fieldName . " " . $this->operator[$key]
                            . " " . $parametro;
                }
            } else {
                foreach ($this->parametro as $parametro) {
                    $where[] = $this->fieldName . " " . $this->operator . " "
                            . $parametro;
                }
            }
        } else {
            $where[] = $field . $this->operator . $this->parametro;
        }
    }

    protected function _withOperators(&$where)
    {
        $field = $this->fieldName;
        if (is_array($this->valor)) {
            if (is_array($this->operator)) {
                foreach ($this->valor as $key => $value) {
                    $where[] = $field . " " . $this->operator[$key] . " '"
                            . Mekayotl_database_SQLAbstract::clean($value)
                            . "'";
                }
            } else {
                if ($this->operator == '=') {
                    $where[] = $field . " IN ('"
                            . implode("', '",
                                    Mekayotl_database_SQLAbstract::clean(
                                            $this->valor)) . "')";
                } else {
                    $where[] = $field . " " . $this->operator . " '"
                            . Mekayotl_database_SQLAbstract::clean($this->valor)
                            . "'";
                }
            }
        } else {
            $where[] = $field . " " . $this->operator . " '"
                    . Mekayotl_database_SQLAbstract::clean($this->valor) . "'";
        }
    }

    protected function _withNull(&$where)
    {
        $field = $this->fieldName;
        if (!is_null($this->vacio)) {
            $where[] = $field . (($this->vacio) ? ' ' : ' !') . "= ''";
        }
        if (!is_null($this->nulo)) {
            $where[] = (($this->nulo) ? '' : '!') . "ISNULL(" . $field . ")";
        }
    }

    /**
     * Procesa y regresa una cadena con formato para ser utilizado en un SQL
     * @return string La sentencia
     */

    public function __toString()
    {
        $where = array();
        if (!is_null($this->inset)) {
            $this->_withInSet($where);
        } elseif (!is_null($this->parametro)) {
            $this->_withParameters($where);
        } elseif (!is_null($this->valor)) {
            $this->_withOperators($where);
        }
        $this->_withNull($where);
        return '(' . implode(' ' . $this->union . ' ', $where) . ")";
    }

}
