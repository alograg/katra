<?php
/**
 * Clase repecentativa de registros de la tabla exchange
 *
 * Esta tabla se utiliza para exchange
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Vo
 * @version	2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla exchange
 *
 * Esta tabla se utiliza para exchange
 * @package	App.Saas
 * @subpackage	Vo
 */
class App_saas_vo_Exchange extends Mekayotl_database_dal_ValueAbstract
{

    public $currency = 'MXP';
    public $rate = 1.000000;
    public $editOn = '2012-01-01';
}
