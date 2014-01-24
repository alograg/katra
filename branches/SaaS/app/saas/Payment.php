<?php
/**
 * Lógica de manejo de pagos.
 *
 * Métodos de manejo de pagos
 * @author  Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright   Copyright (c) 2012, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package App.saas
 * @since   Revisión $id$ $date$
 * @subpackage  Logic
 * @version 1.0.0
 */

/**
 * Lógica de manejo de pagos.
 *
 * Métodos de manejo de pagos
 * @package App.saas
 * @subpackage  Logic
 */
class App_saas_Payment extends Mekayotl_database_dal_BusinessAbstract
{

    /**
     *
     * @var App_saas_tables_Payment
     */

    protected $_payment;

    /**
     *
     * @var App_saas_tables_ViewAccountInvoice
     */

    protected $_accountInvoice;

    /**
     * Constructor
     */

    public function __construct()
    {
        if (isset(App_Saas::singleton()->config['Database'])) {
            $this->_payment = new App_saas_tables_Payment();
            $this->_accountInvoice = new App_saas_tables_ViewAccountInvoice();
            parent::__construct(Mekayotl::adapterFactory('Database'));
        }
    }

    /**
     * Verifica si la susbcripcion tiene pagos atrazados, si los tiene devuelve
     * un array con un valor falso y un mensaje de descripcion
     * IMOPRTANTE: este metodo se quedo sin desarrollar.
     * @param integer $subscription
     * @return object Informacion del estado del pago. acces y message
     */

    public function checkMonthPayment($subscription)
    {
        $return = (object) array(
                'access' => TRUE,
                'message' => 'Pago en tiempo'
        );
        if (is_null($subscription) || intval($subscription) == 0) {
            $return->access = FALSE;
            $return->message = 'No exite la subscripción';
            return $return;
        }
        $filter = $this->_accountInvoice->newFilter();
        $filter->invoice->fieldName = 'invoice';
        $filter->invoice->operator = ' IN ';
        $filter->invoice->parametro = '(SELECT invoice
            FROM concept
            WHERE concept = ' . intval($subscription) . ')';
        $filter->invoiceStatus = 'late';
        $this->_accountInvoice->read($filter);
        if ($this->_accountInvoice->count() > 0) {
            $return->access = FALSE;
            $return->message = 'Existen problemas con la cuenta,'
                    . ' contacte a su administrador.';
        }
        return $return;
    }
}
