<?php
/**
 * Clase repecentativa de registros de la tabla package
 *
 * Esta tabla se utiliza para package
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Vo
 * @version	2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla package
 *
 * Esta tabla se utiliza para package
 * @package	App.Saas
 * @subpackage	Vo
 */
class App_saas_vo_Package extends Mekayotl_database_dal_ValueAbstract
{

    public $package = NULL;
    public $service = NULL;
    public $name = NULL;
    public $description = NULL;
    public $units = 1.00;
    public $configuration = NULL;
    public $recurrence = 'month';
}
