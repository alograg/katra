<?php
/**
 * Clase de acceso a la tabla member
 *
 * Esta tabla se utiliza para member
 * @author    Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright    Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package    App.Saas
 * @since    Revisión $id$ $date$
 * @subpackage    Tables
 * @version    2.0.0
 */

/**
 * Clase de acceso a la tabla member
 *
 * Esta tabla se utiliza para member
 * @package    App.Saas
 * @subpackage    Tables
 */
class App_saas_tables_Member extends Mekayotl_database_dal_AccessAbstract
{

    /**
     * Configura el objeto.
     */

    public function __construct()
    {
        $this->_table = 'member';
        $this->_baseClass = 'App_saas_vo_Member';
        $this->_keys = array(
                'member',
        );
        parent::__construct();
    }

    public function setPassword($id, $password)
    {
        $this
                ->read(
                        array(
                                'usu_id' => $id
                        ));
        $member = (object) $this->current();
        $query = array();
        $query[] = 'UPDATE ' . $this->table;
        $query[] = 'SET password = MD5("' . $password . '")';
        $query[] = 'WHERE member = ' . $member->member;
        $this->_adapter->query(implode("\n", $query));
    }

    /**
     * @param Mekayotl_database_dal_ValueAbstract|array $row
     * @return mixed|boolean
     */

    public function create($row)
    {
        $row = new App_saas_vo_Member((array) $row);
        $this->_filter = $this->newFilter();
        $nick = new Mekayotl_database_WhereElement();
        $nick->fieldName = 'nick';
        $nick->valor = $row->nick . '%';
        $nick->operator = ' LIKE ';
        $this->_filter->nick = $nick;
        $this->reload();
        if ($this->count() > 0) {
            $row->nick .= '_' . $this->count();
        }
        return parent::create($row);
    }

}
