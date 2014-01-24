<?php
/**
 * Clase repecentativa de registros de la tabla concept
 *
 * Esta tabla se utiliza para concept
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Vo
 * @version	2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla concept
 *
 * Esta tabla se utiliza para concept
 * @package	App.Saas
 * @subpackage	Vo
 */
class App_saas_vo_Concept extends Mekayotl_database_dal_ValueAbstract
{

    public $invoice = 0;
    public $concept = NULL;
    public $description = NULL;
    public $quantity = 1;
    public $price = 1.00;

}
