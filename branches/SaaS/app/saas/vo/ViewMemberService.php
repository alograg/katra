<?php
/**
 * Clase repecentativa de registros de la tabla viewMemberService
 *
 * Esta tabla se utiliza para viewMemberService
 * @author    Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright    Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package    App.Saas
 * @since    Revisión $id$ $date$
 * @subpackage    Vo
 * @version    2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla viewMemberService
 *
 * Esta tabla se utiliza para viewMemberService
 * @package    App.Saas
 * @subpackage    Vo
 */
class App_saas_vo_ViewMemberService extends Mekayotl_database_dal_ValueAbstract
{

    public $subscription = NULL;
    public $member = NULL;
    public $level = user;
    public $email = NULL;
    public $package = 0;
    public $account = 0;
    public $status = 'blocked';
    public $createdOn = '2012-01-01';
    public $nick = NULL;
    public $password = NULL;
    public $fullName = NULL;
    public $language = 'es';
    public $service = NULL;
    public $packageName = NULL;
    public $packageDescription = NULL;
    public $serviceName = NULL;
    public $serviceSatatus = 'off';
}
