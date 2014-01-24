<?php
/**
 * Clase repecentativa de registros de la tabla token
 *
 * Esta tabla se utiliza para token
 * @author Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package App.Saas
 * @since Revisión $id$ $date$
 * @subpackage Vo
 * @version 2.0.0
 */

/**
 * Clase repecentativa de registros de la tabla token
 *
 * Esta tabla se utiliza para token
 * @package App.Saas
 * @subpackage Vo
 */
class App_saas_vo_Token extends Mekayotl_database_dal_ValueAbstract
{

    public $member = 0;
    public $token = NULL;
    public $loginAt = NULL;
    public $url = NULL;

    public function __construct(array $values = NULL)
    {
        $this->loginAt = date('Y-m-d H:i:s');
        parent::__construct($values);
    }

    public function generateToken()
    {
        $this->token = md5($this->url . date('Ymd') . '/' . $this->member);
        return $this->token;
    }
}
