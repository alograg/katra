<?php
/**
 * Clase repecentativa de registros de la tabla member
 *
 * Esta tabla se utiliza para member
 * @author    Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright    Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package    App.Saas
 * @since    Revisión $id$ $date$
 * @subpackage    Vo
 * @version    2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla member
 *
 * Esta tabla se utiliza para member
 * @package    App.Saas
 * @subpackage    Vo
 */
class App_saas_vo_Member extends Mekayotl_database_dal_ValueAbstract
{

    public $member = NULL;
    public $nick = NULL;
    public $email = NULL;
    public $password = NULL;
    public $fullName = NULL;
    public $language = 'es';
}
