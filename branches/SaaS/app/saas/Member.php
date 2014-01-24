<?php

/**
 * Lógica de los miembros de la aplicación.
 *
 * Métodos manejo de miembros.
 * @author Henry <henry@aquainteractive.com.mx>
 * @copyright Copyright (c) 2012, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package App.SaaS
 * @since Revisión $id$ $date$
 * @subpackage Floyd
 * @version 1.0.0
 */

/**
 * Lógica de los miembros de la aplicación.
 *
 * Métodos manejo de miembros.
 * @package App.SaaS
 * @subpackage SaaS
 */
class App_saas_Member extends Mekayotl_database_dal_BusinessAbstract
{

    /**
     * Acceso a los miembros.
     * @var App_SaaS_tables_Member
     */

    protected $_member = NULL;

    public function __construct()
    {
        $this->_member = new App_saas_tables_Member();
        $this->setAdapter(Mekayotl::adapterFactory('Database'));
    }

    /**
     * Realiza un ingrso a la aplicacion.
     * @param string $user
     * @param string $password
     * @return boolean|array Informacion del usuario ingresado.
     */

    public function login($user, $password)
    {
        if (is_null($user) || is_null($password)) {
            return FALSE;
        }
        $filter = new App_saas_vo_Member();
        $members = $this->_member;
        $filter->language = NULL;
        $filter->password = md5($password);
        $filter->nick = $user;
        $members->read($filter);
        if ($members->count() > 1 || $members->count() == 0) {
            return FALSE;
        }
        $member = $members->current();
        $token = new App_saas_vo_Token();
        $token->member = $member->member;
        $token->url = Mekayotl_tools_Request::referer();
        $token->token = md5($token->url . '_' . $member->member);
        $tokens = new App_saas_tables_Token();
        $tokens->replace($token);
        $sesionData = (array) $member;
        $sesionData['token'] = $token->token;
        unset($sesionData['password']);
        return $sesionData;
    }

    /**
     * Guarda (nuevo o actualización) un miembro.
     * @param array|App_saas_vo_Member $member
     * @return App_saas_vo_Member
     */

    protected function save($member)
    {
        $member = new App_saas_vo_Member($member);
        if (is_string($member->password)) {
            $member->password = md5($member->password);
        }
        $member->member = intval($member->member);
        $members = $this->_member;
        if ($member->member == 0) {
            $member->member = NULL;
            $member->member = $members->create($member);
            if ($member->member == -1) {
                throw new Exception('Duplicate Nick', E_USER_ERROR);
            }
        } else {
            $members->update($member);
        }
        $members
                ->read(
                        array(
                                'member' => $member->member
                        ));
        return $members->current();
    }

    /**
     * Retorna los detalles de un miembro.
     *
     */

    public function details($id)
    {
        return $this->_member
                ->read(
                        array(
                                'member' => $id
                        ))->current();
    }

    /**
     *
     *
     */

    public function edit($id)
    {
        return $this->details($id);
    }
}
