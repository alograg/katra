<?php
/**
 * Clase de acceso a la tabla token
 *
 * Esta tabla se utiliza para token
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Tables
 * @version	2.0.0
 */

/**
 * Clase de acceso a la tabla token
 *
 * Esta tabla se utiliza para token
 * @package	App.Saas
 * @subpackage	Tables
 */
class App_saas_tables_Token extends Mekayotl_database_dal_AccessAbstract
{

    /**
     * Configura el objeto.
     */

    public function __construct()
    {
        $this->_table = 'token';
        $this->_baseClass = 'App_saas_vo_Token';
        $this->_keys = array(
                'member',
        );
        parent::__construct();
    }
}
