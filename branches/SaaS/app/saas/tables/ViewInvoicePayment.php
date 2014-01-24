<?php
/**
 * Clase de acceso a la tabla viewInvoicePayment
 *
 * Esta tabla se utiliza para viewInvoicePayment
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Tables
 * @version	2.0.0
 */

/**
 * Clase de acceso a la tabla viewInvoicePayment
 *
 * Esta tabla se utiliza para viewInvoicePayment
 * @package	App.Saas
 * @subpackage	Tables
 */
class App_saas_tables_ViewInvoicePayment extends
        Mekayotl_database_dal_AccessAbstract
{

    /**
     * Configura el objeto.
     */

    public function __construct()
    {
        $this->_table = 'viewInvoicePayment';
        $this->_baseClass = 'App_saas_vo_ViewInvoicePayment';
        $this->_keys = array();
        parent::__construct();
    }
}
