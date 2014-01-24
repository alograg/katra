<?php
//namespace core\database\dal;
/**
 * Abstracción del acceso a base de datos
 *
 * @author $Author$
 * @copyright Copyright (c) 2011, {@link http://www.aquainteractive.com.mx}
 * @package Mekayotl.database
 * @since 2011-03-01
 * @subpackage dal
 * @version $Id$
 */

/**
 * Abstact del acceso a base de datos
 *
 * Define funciones básicas y funciones que se deben de implementar una tabla.
 * @package Mekayotl.database
 * @subpackage dal
 * @abstract
 */
abstract class Mekayotl_database_dal_AccessAbstract implements Iterator
{
    /**#@+
     * @abstract
     * @access protected
     */
    /**
     * Nombre de la tabla a acceder
     * @var string
     */

    protected $_table;
    /**
     * Definición de campos, como array asociativo.
     * @var array
     */
    protected $_fields;
    /**
     * Define los campos identificadores de la tabla, array de nombres
     * @var array
     */
    protected $_keys;
    /**
     * Define la clase de los objetos que se regresan
     * @var string
     */
    protected $_baseClass;
    /**#@-*/
    /**#@+
     * @access protected
     */
    /**
     * Adaptador de conexión
     * @var Mekayotl_database_SQLAbstract
     */
    protected $_adapter;
    /**
     * Registros de la consulta
     * @var array
     */
    protected $_data = FALSE;
    /**
     * Filtro aplicado a la consulta
     * @var Mekayotl_database_dal_ValueAbstractMekayotl_database_WhereCollection
     */
    protected $_filter = NULL;
    /**
     * Orden de los registros
     * @var string
     */
    protected $_order = NULL;
    /**
     * Limite por pagina
     * @var integer
     */
    protected $_limit = NULL;
    /**
     * Pagina desplegada
     * @var integer
     */
    protected $_page = 0;
    /**
     * Posición del apuntador de elementos
     * @var integer
     */
    protected $_position = 0;
    /**#@-*/
    /**#@+
     * @access public
     */
    public $_explicitType = 'MekayotlArray';
    /**
     * Constructor del objeto
     */

    public function __construct(Mekayotl_database_SQLAbstract $adapter = NULL)
    {
        if ($adapter == NULL) {
            $adapter = Mekayotl::adapterFactory('Database');
        }
        $this->setAdapter($adapter);
    }

    public function itemAs($className)
    {
        if (!class_exists($className)) {
            return FALSE;
        }
        $this->_baseClass = $className;
        $this->_adapter
                ->setFetchMode(Mekayotl_database_SQLAbstract::FETCH_CLASS,
                        $this->_baseClass);
    }

    /**
     * Establece el adaptador a la base de datos.
     * @param Mekayotl_database_SQLAbstract $adapter
     */

    public function setAdapter(Mekayotl_database_SQLAbstract $adapter = NULL)
    {
        if ($adapter == NULL) {
            $adapterClass = 'Mekayotl_database_';
            $adapterClass .= strtolower(
                    $GLOBALS['config']['Database']['driver']);
            $adapterClass .= '_Adapter';
            $adapter = new $adapterClass($GLOBALS['config']['Database'], TRUE);
        }
        $this->_adapter = $adapter;
        $this->itemAs($this->_baseClass);
        return $this;
    }

    /**
     * Obtiene las propiedades protegidas.
     * @param string $name Nombre de la propiedad que se desea.
     */

    public function __get($name)
    {
        $property = '_' . $name;
        return $this->$property;
    }

    /**
     * Establece las propiedades protegidas.
     * @param string $name
     * @param mixed $value
     */

    public function __set($name, $value)
    {
        $property = '_' . $name;
        $this->$property = $value;
    }

    /**
     * Transforma los datos consultados en un JSON
     * @return string
     */

    public function toJSON()
    {
        return json_encode($this->_data);
    }

    /**
     * Convierte un array y objeto a un objeto de la clase predeterminada para
     * valores
     * @param Mekayotl_database_dal_ValueAbstract|array $row Array asociativo o
     * extensión ValueAbstract para la conversion a elemento de tabla
     * @return Mekayotl_database_dal_ValueAbstract Si el objeto ha sido
     * transformado o ya es un elemento de tabla
     */

    public function convertToBase($row)
    {
        if ($row instanceof Mekayotl_database_WhereCollection) {
            return $row;
        } else if (!($row instanceof Mekayotl_database_dal_ValueAbstract)) {
            $class = $this->_baseClass;
            $newObj = new $class((array) $row);
            return $newObj;
        } else {
            return $row;
        }
        return NULL;
    }

    /**
     * Crea un registro de la tabla
     * @param Mekayotl_database_dal_ValueAbstract|array $row Array asociativo o
     * extensión ValueAbstract crear un registro
     * @return mixed Lo que devuelva el adaptador tras agregar un registro
     */

    public function create($row)
    {
        $row = $this->convertToBase($row);
        $return = $this->_adapter->insert($this->_table, $row);
        if (is_array($this->_data)) {
            $this->reload();
        }
        return $return;
    }

    /**
     * Actualiza un registro de la tabla
     * @param Mekayotl_database_dal_ValueAbstract|array $row Array asociativo o
     * extensión ValueAbstract con datos de actualización y referencia
     * @return mixed Lo que devuelva el adaptador tras agregar un registro
     */

    public function update($row)
    {
        $row = $this->convertToBase($row);
        $return = $this->_adapter->update($this->_table, $this->_keys, $row);
        if (is_array($this->_data)) {
            $this->reload();
        }
        return $return;
    }

    /**
     * Remplaza un registro de la tabla
     * @param Mekayotl_database_dal_ValueAbstract|array $row Array asociativo o
     * extensión ValueAbstract con datos de actualización y referencia
     * @return mixed Lo que devuelva el adaptador tras agregar un registro
     */

    public function replace($row)
    {
        $row = $this->convertToBase($row);
        $return = $this->_adapter->replace($this->_table, $row);
        if (is_array($this->_data)) {
            $this->reload();
        }
        return $return;
    }

    /**
     * Cambia la pagina de los registros consultados
     * @param integer $page
     * @return array Los datos de la nueva pagina
     * @uses Mekayotl_database_dal_AccessAbstract::reload()
     */

    public function gotoPage($page)
    {
        if ($this->_limit > 0) {
            $this->_page = $page;
            return $this->reload();
        }
        return $this->_data;
    }

    /**
     * Asigna los valores para una consulta
     * @param Mekayotl_database_dal_ValueAbstract|Mekayotl_database_WhereCollection
     * $filter El filtro de datos
     * @param string $order Un string de orden
     * @param integer $limit La limitación por pagina
     * @param integer $page La pagina a implementar
     * @return Mekayotl_database_dal_AccessAbstract Los datos consultados
     * @uses Mekayotl_database_dal_AccessAbstract::reload()
     */

    public function read($filter = NULL, $order = NULL, $limit = 0, $page = 0)
    {
        $this->_filter = ($this->_filter !== $filter) ? $filter : $this
                ->_filter;
        $this->_order = ($this->_order != $order && !is_null($order)) ? $order
                : $this->_order;
        $this->_limit = ($this->_limit != $limit) ? $limit : $this->_limit;
        $this->_page = ($this->_page != $page) ? $page : $this->_page;
        return $this->reload();
    }

    /**
     * Regreza un cadena de texto con los registros consultados en formato CSV
     * @param array $header Un arreglo asociativo con los nombres de los
     * campos de la base de datos como llaves y los nombres de las columnas
     * como valires.
     * @param Mekayotl_database_dal_ValueAbstract|Mekayotl_database_WhereCollection
     * $filter El filtro de datos
     * @param string $order Un string de orden
     * @param integer $limit La limitación por pagina
     * @param integer $page La pagina a implementar
     * @return string Los datos consultados
     * @uses Mekayotl_database_dal_AccessAbstract::reload()
     */

    public function readAsCSV($header = NULL, $filter = NULL, $order = NULL,
            $limit = 0, $page = 0)
    {
        $this->_filter = ($this->_filter != $filter) ? $filter : $this->_filter;
        $this->_order = ($this->_order != $order) ? $order : $this->_order;
        $this->_limit = ($this->_limit != $limit) ? $limit : $this->_limit;
        $this->_page = ($this->_page != $page) ? $page : $this->_page;
        $rs = $this->reload(TRUE);
        return $this->_adapter->toCSV($rs, $header);
    }

    /**
     * Regreza un cadena de texto con los registros consultados en formato XML
     * @param Mekayotl_database_dal_ValueAbstract|Mekayotl_database_WhereCollection
     * $filter El filtro de datos
     * @param string $order Un string de orden
     * @param integer $limit La limitación por pagina
     * @param integer $page La pagina a implementar
     * @return array Los datos consultados
     * @uses Mekayotl_database_dal_AccessAbstract::reload()
     */

    public function readAsXML($filter = NULL, $order = NULL, $limit = 0,
            $page = 0)
    {
        $this->_filter = ($this->_filter != $filter) ? $filter : $this->_filter;
        $this->_order = ($this->_order != $order) ? $order : $this->_order;
        $this->_limit = ($this->_limit != $limit) ? $limit : $this->_limit;
        $this->_page = ($this->_page != $page) ? $page : $this->_page;
        $rs = $this->reload(TRUE);
        return $this->_adapter->toXML($rs);
    }

    /**
     * Asigna los valores para una consulta
     * @param string|array $extrafields Un string con los campos SQL extras.
     * @param Mekayotl_database_dal_ValueAbstract|Mekayotl_database_WhereCollection
     * $filter El filtro de datos
     * @param string $order Un string de orden
     * @param integer $limit La limitación por pagina
     * @param integer $page La pagina a implementar
     * @return array Los datos consultados
     * @uses Mekayotl_database_dal_AccessAbstract::reload()
     */

    public function personalizedRead($extrafields, $filter = NULL,
            $order = NULL, $limit = 0, $page = 0)
    {
        $this->_filter = ($this->_filter != $filter) ? $filter : $this->_filter;
        $this->_order = ($this->_order != $order) ? $order : $this->_order;
        $this->_limit = ($this->_limit != $limit) ? $limit : $this->_limit;
        $this->_page = ($this->_page != $page) ? $page : $this->_page;
        if (is_array($extrafields)) {
            $extrafields = '*,' . implode(',', $extrafields);
        } elseif (!is_string($extrafields)) {
            $extrafields = "*";
        }
        $fields = $this->_table . '.' . $extrafields;
        $rs = $this->_adapter
                ->select($this->_table, $fields, $this->_filter, $this->_order,
                        NULL, NULL, NULL, $this->_limit, $this->_page);
        $this->_data = $this->_adapter->fetchAll($rs);
        $this->rewind();
        if (count($this->_data) > 0 && !$this->_fields) {
            $this->_fields = array_keys((array) $this->current());
        }
        return $this;
    }

    /**
     * Realiza la consulta en la base de datos con los datos guardados en las
     * variables asignadas
     * @return array Un arreglo de elementos de la tabla
     */

    public function reload($asResulset = FALSE)
    {
        if (is_array($this->_filter)) {
            $this->_filter = new Mekayotl_database_WhereCollection(
                    $this->_filter);
        }
        $rs = $this->_adapter
                ->select($this->_table, "*", $this->_filter, $this->_order,
                        NULL, NULL, NULL, $this->_limit, $this->_page);
        if ($asResulset) {
            return $rs;
        }
        $this->itemAs($this->_baseClass);
        $this->_data = $this->_adapter->fetchAll($rs);
        $this->rewind();
        if (count($this->_data) > 0 && !$this->_fields) {
            $this->_fields = array_keys((array) $this->current());
        }
        return $this;
    }

    /**
     * Elimina registros de la tabla según los filtros proporcionados
     * @param Mekayotl_database_dal_ValueAbstract|Mekayotl_database_WhereCollection
     * $filter El filtro de datos
     */

    public function remove($filter)
    {
        $filter = $this->convertToBase($filter);
        if ($filter) {
            $return = $this->_adapter->delete($this->_table, $filter);
            if (is_array($this->_data)) {
                $this->reload();
            }
            return $return;
        }
        return FALSE;
    }

    /**
     * Función del Iterator para reiniciar la posición del apuntador
     */

    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * Función del Iterator para obtener el elemento actual
     */

    public function current()
    {
        if (is_array($this->_data)) {
            return $this->_data[$this->_position];
        }
        return FALSE;
    }

    /**
     * Función del Iterator para obtener la posición del apuntador
     */

    public function key()
    {
        return $this->_position;
    }

    /**
     * Función del Iterator para mover una posición el apuntador
     */

    public function next()
    {
        if (is_array($this->_data)) {
            ++$this->_position;
        }
        return FALSE;
    }

    /**
     * Función del Iterator para validar que existe la possición
     */

    public function valid()
    {
        if (is_array($this->_data)) {
            return isset($this->_data[$this->_position]);
        }
        return FALSE;
    }

    /**
     * Retorna los datos como un array
     * @param string|array $key El campo o campos que seran tomados como llave de los elementos
     * @return array
     */

    public function toArray($key = NULL)
    {
        if (is_null($key)) {
            return $this->_data;
        }
        $return = array();
        foreach ($this as $item) {
            $itemAssoc = (array) $item;
            if (is_string($key)) {
                $itemKey = $itemAssoc[$key];
            } else if (is_array($key)) {
                $itemKey = array();
                foreach ($key as $name) {
                    $itemKey[] = $itemAssoc[$key];
                }
                $itemKey = implode('_', $itemKey);
            }
            $return[$itemKey] = $item;
        }
        return $return;
    }

    /**
     * Cuenta los elementos en el iterator actual.
     * @return integer
     */

    public function count()
    {
        return count($this->_data);
    }

    /**
     * Genera un objeto de filtro.
     * @return Mekayotl_database_WhereCollection|mixed
     */

    public function newFilter()
    {
        $className = $this->_baseClass;
        if (!is_null($className) && !class_exists($className)) {
            $className = 'Mekayotl_database_WhereCollection';
        }
        $object = new $className();
        if ($object instanceof Mekayotl_database_dal_ValueAbstract) {
            $object->setNull();
            $object = $object->asWhere();
        }
        return $object;
    }

    /**#@-*/
}
