<?php
/**
 * Clase repecentativa de registros de la tabla viewInvoiceBalance
 *
 * Esta tabla se utiliza para viewInvoiceBalance
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Vo
 * @version	2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla viewInvoiceBalance
 *
 * Esta tabla se utiliza para viewInvoiceBalance
 * @package	App.Saas
 * @subpackage	Vo
 */
class App_saas_vo_ViewInvoiceBalance extends Mekayotl_database_dal_ValueAbstract
{

    public $invoice = 0;
    public $createdOn = '2012-01-01';
    public $account = 0;
    public $status = 'current';
    public $currency = 'MXP';
    public $taxes = 0.00;
    public $data = NULL;
    public $quantityOfConcepts = 0;
    public $subtotal = NULL;
    public $quantityOfPayments = 0;
    public $failed = NULL;
    public $successed = NULL;
    public $total = NULL;
    public $underpayment = NULL;
}
