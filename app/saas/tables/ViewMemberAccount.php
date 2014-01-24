<?php
/**
 * Clase de acceso a la tabla viewMemberAccount
 *
 * Esta tabla se utiliza para viewMemberAccount
 * @author    Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright    Copyright (c) 2008',
'{@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package    App.Saas
 * @since    Revisión $id$ $date$
 * @subpackage    Tables
 * @version    2.0.0
 */

/**
 * Clase de acceso a la tabla viewMemberAccount
 *
 * Esta tabla se utiliza para viewMemberAccount
 * @package    App.Saas
 * @subpackage    Tables
 */
class App_saas_tables_ViewMemberAccount extends
        Mekayotl_database_dal_AccessAbstract
{

    /**
     * Configura el objeto.
     */

    public function __construct()
    {
        $this->_table = 'viewMemberAccount';
        $this->_baseClass = 'App_saas_vo_ViewMemberAccount';
        $this->_keys = array();
        parent::__construct();
    }

    public function readByToken($token)
    {
        $this->_filter = new Mekayotl_database_WhereCollection();
        $this->_filter->token = $token;
        $this->_filter->token->fieldName = 'MD5(CONCAT(nick,"_",password))';
        if (func_num_args() > 1) {
            $this->_filter->accountName = func_get_arg(1);
        }
        $this->_order = 'accountName';
        $rs = $this->_adapter
                ->select($this->_table, $fields, $this->_filter, $this->_order,
                        $join, NULL, NULL, $this->_limit, $this->_page);
        $this->_data = $this->_adapter->fetchAll($rs);
        $this->rewind();
        if (count($this->_data) > 0 && !$this->_fields) {
            $this->_fields = array_keys((array) $this->current());
        }
        return $this;
    }

    public function readByUser($member)
    {
        $this->_filter = new Mekayotl_database_WhereCollection();
        $this->_filter->member = $member;
        $this->_order = 'accountName';
        $fields = array(
                'account',
                'accountName',
                'firm',
                'bussiness',
                'street',
                'outside',
                'inside',
                'crosses',
                'zip',
                'colony',
                'city',
                'state',
                'country',
                'phone',
                'fax',
                'accountEmail',
                'taxKey',
                'accountStatus',
                'currency',
                'COUNT(subscription) as subscriptions'
        );
        $oldFecth = $this->_adapter->fetchMode;
        $this->itemAs('stdClass');
        $rs = $this->_adapter
                ->select($this->_table, $fields, $this->_filter, $this->_order,
                        $join, 'account', NULL, $this->_limit, $this->_page);
        $this->_data = $this->_adapter->fetchAll($rs);
        $this->rewind();
        $this->itemAs($this->_baseClass);
        if (count($this->_data) > 0 && !$this->_fields) {
            $this->_fields = array_keys((array) $this->current());
        }
        return $this;
    }
}
