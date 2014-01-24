<?php
//namespace core\database\dal;
/**
 * Abstracción del acceso a registro de una tabla de base de datos
 *
 * @author Henry I. Galvez T. <alograg@alograg.me>
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl.database
 * @since 2011-03-01
 * @subpackage dal
 * @version $Id$
 */

/**
 * Abstracción del acceso a registro de una tabla de base de datos
 *
 * Define funciones básicas y funciones que se deben de implementar un registro
 * de una tabla.
 * @package Mekayotl.database
 * @subpackage dal
 * @abstract
 */
abstract class Mekayotl_database_dal_ValueAbstract
{
    /**
     * Constructor
     * @param array $values Valores predeterminados para el objeto
     */

    public function __construct(array $values = NULL)
    {
        if (is_array($values)) {
            $assoc = !isset($values[0]);
            if ($assoc) {
                foreach ($this as $key => $value) {
                    $this->$key = isset($values[$key]) ? $values[$key] : NULL;
                }
            } else {
                $this->fillFromArray($values);
            }
        }
    }

    /**
     * Establece los valores del objeto en vase a un arreglo no asociativo.
     * @param array $values Valores para colocar en el objeto.
     */

    public function fillFromArray(array $values)
    {
        $arrayThis = (array) $this;
        $fields = array_keys($arrayThis);
        foreach ($fields as $key => $value) {
            $this->$value = $values[$key];
        }
        $this->clean();
        return $this;
    }

    /**
     * Transforma el objeto en un {@link Mekayotl_database_WhereCollection} para ser
     * utilizado como filtro.
     * @return Mekayotl_database_WhereCollection
     */

    public function asWhere()
    {
        $this->clean();
        $return = new Mekayotl_database_WhereCollection((array) $this);
        return $return;
    }

    public function setNull()
    {
        foreach ($this as $key => $value) {
            $this->$key = NULL;
        }
        return $this;
    }

    public function clean()
    {
        $new = get_class($this);
        $new = new $new();
        $reverse = new ReflectionObject($new);
        foreach ($this as $key => $value) {
            if (!$reverse->hasProperty($key)) {
                unset($this->$key);
            }
        }
        return $this;
    }

    /**
     * Precenta el objeto como una sentencia de filtrado SQL.
     * @return string Sentencia SQL
     */

    public function __toString()
    {
        $return = $this->asWhere();
        return (string) $return;
    }

}
