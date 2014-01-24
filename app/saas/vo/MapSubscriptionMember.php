<?php
/**
 * Clase repecentativa de registros de la tabla mapSubscriptionMember
 *
 * Esta tabla se utiliza para mapSubscriptionMember
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Vo
 * @version	2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla mapSubscriptionMember
 *
 * Esta tabla se utiliza para mapSubscriptionMember
 * @package	App.Saas
 * @subpackage	Vo
 */
class App_saas_vo_MapSubscriptionMember extends
        Mekayotl_database_dal_ValueAbstract
{

    public $subscription = NULL;
    public $member = NULL;
    public $level = 'user';
    public $email = NULL;
}
