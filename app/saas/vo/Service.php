<?php
/**
 * Clase repecentativa de registros de la tabla service
 *
 * Esta tabla se utiliza para service
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Vo
 * @version	2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla service
 *
 * Esta tabla se utiliza para service
 * @package	App.Saas
 * @subpackage	Vo
 */
class App_saas_vo_Service extends Mekayotl_database_dal_ValueAbstract
{

    public $service = NULL;
    public $name = NULL;
    public $sqlCreation = NULL;
    public $status = 'off';
}
