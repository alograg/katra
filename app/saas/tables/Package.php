<?php
/**
 * Clase de acceso a la tabla package
 *
 * Esta tabla se utiliza para package
 * @author    Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright    Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package    App.Saas
 * @since    Revisión $id$ $date$
 * @subpackage    Tables
 * @version    2.0.0
 */

/**
 * Clase de acceso a la tabla package
 *
 * Esta tabla se utiliza para package
 * @package    App.Saas
 * @subpackage    Tables
 */
class App_saas_tables_Package extends Mekayotl_database_dal_AccessAbstract
{

    /**
     * Configura el objeto.
     */

    public function __construct()
    {
        $this->_table = 'package';
        $this->_baseClass = 'App_saas_vo_Package';
        $this->_keys = array(
                'package',
        );
        parent::__construct();
    }

    /**
     * @param resulset $asResulset
     * @return Mekayotl_database_dal_AccessAbstract
     */

    public function reload($asResulset = FALSE)
    {
        if (is_array($this->_filter)) {
            $this->_filter = new Mekayotl_database_WhereCollection(
                    $this->_filter);
        }
        $fields = array(
                $this->_table . '.*'
        );
        $fields[] = 'service.name AS serviceName';
        $fields[] = 'service.sqlCreation';
        $join = array(
                'service' => 'USING(service)'
        );
        $rs = $this->_adapter
                ->select($this->_table, $fields, $this->_filter, $this->_order,
                        $join, NULL, NULL, $this->_limit, $this->_page);
        if ($asResulset) {
            return $rs;
        }
        $this->_data = $this->_adapter->fetchAll($rs);
        $this->rewind();
        if (count($this->_data) > 0 && !$this->_fields) {
            $this->_fields = array_keys((array) $this->current());
        }
        return $this;
    }

}
