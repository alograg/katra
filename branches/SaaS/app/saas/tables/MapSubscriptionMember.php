<?php
/**
 * Clase de acceso a la tabla mapSubscriptionMember
 *
 * Esta tabla se utiliza para mapSubscriptionMember
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Tables
 * @version	2.0.0
 */

/**
 * Clase de acceso a la tabla mapSubscriptionMember
 *
 * Esta tabla se utiliza para mapSubscriptionMember
 * @package	App.Saas
 * @subpackage	Tables
 */
class App_saas_tables_MapSubscriptionMember extends
        Mekayotl_database_dal_AccessAbstract
{

    /**
     * Configura el objeto.
     */

    public function __construct()
    {
        $this->_table = 'mapSubscriptionMember';
        $this->_baseClass = 'App_saas_vo_MapSubscriptionMember';
        $this->_keys = array(
                'subscription',
                'member',
        );
        parent::__construct();
    }

    /**
     * Retorna el numero de usuarios por subscripcion.
     */

    public function getNumMembers($subscription)
    {
        $where = new Mekayotl_database_WhereCollection();
        $where->subscription = $subscription;
        $rs = $this->_adapter
                ->select($this->_table, 'count(*) as subsMembers', $where);
        $this->_data = $this->_adapter->fetchAll($rs);
        return $this->_data[0]->subsMembers;
    }
}
