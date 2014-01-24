<?php
/**
 * Clase repecentativa de registros de la tabla viewAccountInvoice
 *
 * Esta tabla se utiliza para viewAccountInvoice
 * @author    Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright    Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package    App.Saas
 * @since    Revisión $id$ $date$
 * @subpackage    Vo
 * @version    2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla viewAccountInvoice
 *
 * Esta tabla se utiliza para viewAccountInvoice
 * @package    App.Saas
 * @subpackage    Vo
 */
class App_saas_vo_ViewAccountInvoice extends
        Mekayotl_database_dal_ValueAbstract
{

    public $account = NULL;
    public $invoice = 0;
    public $currency = 'MXP';
    public $quantity = 0;
    public $failed = NULL;
    public $successed = NULL;
    public $total = NULL;
}
