<?php
/**
 * Clase repecentativa de registros de la tabla viewInvoicePayment
 *
 * Esta tabla se utiliza para viewInvoicePayment
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Vo
 * @version	2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla viewInvoicePayment
 *
 * Esta tabla se utiliza para viewInvoicePayment
 * @package	App.Saas
 * @subpackage	Vo
 */

class App_saas_vo_ViewInvoicePayment extends Mekayotl_database_dal_ValueAbstract
{

    public $invoice = 0;
    public $currency = 'MXP';
    public $quantity = 0;
    public $failed = NULL;
    public $successed = NULL;
    public $total = NULL;
}
