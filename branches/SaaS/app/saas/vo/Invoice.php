<?php
/**
 * Clase repecentativa de registros de la tabla invoice
 *
 * Esta tabla se utiliza para invoice
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Vo
 * @version	2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla invoice
 *
 * Esta tabla se utiliza para invoice
 * @package	App.Saas
 * @subpackage	Vo
 */
class App_saas_vo_Invoice extends Mekayotl_database_dal_ValueAbstract
{

    public $invoice = NULL;
    public $createdOn = '2012-01-01';
    public $account = 0;
    public $status = 'current';
    public $currency = 'MXP';
    public $taxes = 0.00;
    public $data = NULL;
}
