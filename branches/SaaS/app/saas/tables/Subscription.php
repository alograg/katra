<?php
/**
 * Clase de acceso a la tabla subscription
 *
 * Esta tabla se utiliza para subscription
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Tables
 * @version	2.0.0
 */

/**
 * Clase de acceso a la tabla subscription
 *
 * Esta tabla se utiliza para subscription
 * @package	App.Saas
 * @subpackage	Tables
 */
class App_saas_tables_Subscription extends Mekayotl_database_dal_AccessAbstract
{

    /**
     * Configura el objeto.
     */

    public function __construct()
    {
        $this->_table = 'subscription';
        $this->_baseClass = 'App_saas_vo_Subscription';
        $this->_keys = array(
                'subscription',
        );
        parent::__construct();
    }

    /**
     * Retorna la configuracion del paquete que tiene la subscripcion.
     */

    public function getPackageConf($subscription)
    {
        $join = array();
        $where = new Mekayotl_database_WhereCollection();
        $join['package'] = 'package.package=subscription.package';
        $where->subscription = $subscription;
        $rs = $this->_adapter
                ->select($this->_table, 'package.configuration as packConf',
                        $where, '', $join);
        $this->_data = $this->_adapter->fetchAll($rs);
        return $this->_data[0]->packConf;
    }
}
