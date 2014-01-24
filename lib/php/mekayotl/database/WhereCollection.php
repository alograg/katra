<?php
//namespace core\database;
/**
 * Contiene una colección de propiedades que se pueden transformar en un SQL de
 * filtrado
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
 * Contiene propiedades de tipo Mekayotl_database_WhereElement que se utilizan
 * para generar los
 * filtros
 *
 * @package Mekayotl
 * @subpackage database
 * @uses Mekayotl_database_SQLAbsctract Es utilizado para generar los filtrados
 * de consulta
 */
class Mekayotl_database_WhereCollection extends ArrayObject
{
    /**
     * Contiene el tipo de union de los elementos
     * @var string
     * @uses Mekayotl_database_WhereCollection::setUnion() Para ser guardado
     * @uses Mekayotl_database_WhereCollection::getUnion() Para ser consultado
     */

    private $_union = 'AND';
    /**
     * Construye el objeto
     * @param array $where Un arreglo asociativo con el nombre de los campos y los
     * valores a ser utilizados.
     */

    public function __construct($where = array())
    {
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Proceso los valores asignados para generar la propiedad y el valor de tipo
     * Mekayotl_database_WhereElement
     * @param string $name El nombre de la propiedad
     * @param mixed $value El valor a ser procesado
     */

    public function __set($name, $value)
    {
        $type = gettype($value);
        $parseValue = new Mekayotl_database_WhereElement();
        switch ($type) {
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
                if (array_key_exists('valor', $value)) {
                    $parseValue = new Mekayotl_database_WhereElement($value);
                } else {
                    $parseValue->valor = $value;
                }
                break;
            case 'object':
                if ($value instanceof Mekayotl_database_WhereElement) {
                    $parseValue = $value;
                } else {
                    $parseValue = new Mekayotl_database_WhereElement($value);
                }
                break;
        }
        $parseValue->fieldName = $name;
        $this[$name] = $parseValue;
    }

    /**
     * Retorna variables generadas o privadas
     * @param string $name Nombre de la variable
     * @return mixed Variable solicitada
     */

    public function __get($name)
    {
        return $this[$name];
    }

    /**
     * Procesa y regresa un cadena con formato para ser utilizado en un SQL
     * @return string La sentencia
     */

    public function __toString()
    {
        $aThis = (array) $this;
        unset($aThis['_explicitType']);
        $aThis = array_filter($aThis,
                array(
                        $this,
                        'emptyWhere'
                ));
        return implode("\n" . $this->_union . ' ', $aThis);
    }

    /**
     * Evalúa si el elemento proporcionado es un elemento vació.
     * @param Mekayotl_database_WhereElement $item Elemento
     * @return boolean
     */

    public function emptyWhere($item)
    {
        $return = !is_null($item->valor)
                || (!is_null($item->vacio) || !is_null($item->nulo)
                        || !is_null($item->parametro));
        return $return;
    }

    /**
     * Establece el tipo de union de los elementos
     * @return Mekayotl_database_WhereCollection This
     */

    public function setUnion($union)
    {
        $this->_union = $union;
        return $this;
    }

    /**
     * Lee el tipo de union establecido
     * @return string
     */

    public function getUnion()
    {
        return $this->_union;
    }

}
