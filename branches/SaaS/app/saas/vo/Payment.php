<?php
/**
 * Clase repecentativa de registros de la tabla payment
 *
 * Esta tabla se utiliza para payment
 * @author	Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright	Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package	App.Saas
 * @since	Revisión $id$ $date$
 * @subpackage	Vo
 * @version	2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla payment
 *
 * Esta tabla se utiliza para payment
 * @package	App.Saas
 * @subpackage	Vo
 */
class App_saas_vo_Payment extends Mekayotl_database_dal_ValueAbstract
{

    public $invoice = 0;
    public $payment = NULL;
    public $createdAt = NULL;
    public $type = 'failed';
    public $currency = 'MXP';
    public $amount = 0.00;
    public $details = NULL;

    public function __construct(array $values = NULL)
    {
        $this->createdAt = date('Y-m-d H:i:s');
        parent::__construct($values);
    }
}
