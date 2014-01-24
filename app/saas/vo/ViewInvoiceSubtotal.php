<?php
/**
 * Clase repecentativa de registros de la tabla viewInvoiceSubtotal
 *
 * Esta tabla se utiliza para viewInvoiceSubtotal
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Vo
 * @version	2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla viewInvoiceSubtotal
 *
 * Esta tabla se utiliza para viewInvoiceSubtotal
 * @package	App.Saas
 * @subpackage	Vo
 */

class App_saas_vo_ViewInvoiceSubtotal extends
        Mekayotl_database_dal_ValueAbstract
{

    public $invoice = 0;
    public $quantity = 0;
    public $subtotal = NULL;
}
