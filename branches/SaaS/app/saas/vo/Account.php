<?php
/**
 * Clase repecentativa de registros de la tabla account
 *
 * Esta tabla se utiliza para account
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Vo
 * @version	2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla account
 *
 * Esta tabla se utiliza para account
 * @package	App.Saas
 * @subpackage	Vo
 */
class App_saas_vo_Account extends Mekayotl_database_dal_ValueAbstract
{

    public $account = NULL;
    public $name = NULL;
    public $firm = NULL;
    public $bussiness = NULL;
    public $street = NULL;
    public $outside = NULL;
    public $inside = NULL;
    public $crosses = NULL;
    public $zip = NULL;
    public $colony = NULL;
    public $city = NULL;
    public $state = NULL;
    public $country = NULL;
    public $phone = NULL;
    public $fax = NULL;
    public $email = NULL;
    public $taxKey = NULL;
    public $status = 'free';
    public $currency = NULL;
    public $configuration = NULL;

}
