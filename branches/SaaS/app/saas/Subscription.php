<?php
/**
 * Lógica de manejo de subscription.
 *
 * Métodos de manejo de subscription
 * @author  Henry Isaac Galvez Thuillier <henry@aquainteractive.com.mx>
 * @copyright   Copyright (c) 2012, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package App.saas
 * @since   Revisión $id$ $date$
 * @subpackage  Logic
 * @version 1.0.0
 */

/**
 * Lógica de manejo de subscription.
 *
 * Métodos de manejo de subscription
 * @package App.saas
 * @subpackage  Logic
 */
class App_saas_Subscription extends Mekayotl_database_dal_BusinessAbstract
{

    /**
     *
     * @var App_saas_tables_Subscription
     */

    protected $_subscription;
    /**
     *
     * @var App_saas_tables_ViewSubscriptionMember
     */
    protected $_viewSubscriptionMember;
    /**
     *
     * @var App_saas_tables_MapSubscriptionMember
     */
    protected $_mapSubscriptionMember;

    /**
     * Constructor
     */

    public function __construct()
    {
        if (isset(App_Saas::singleton()->config['Database'])) {
            $this->_subscription = new App_saas_tables_Subscription();
            $this->_viewSubscriptionMember = new App_saas_tables_ViewSubscriptionMember();
            $this->_mapSubscriptionMember = new App_saas_tables_MapSubscriptionMember();
            parent::__construct(Mekayotl::adapterFactory('Database'));
        }
    }

    /**
     * Realiza un ingrso a la aplicacion.
     * @param integer $subscription
     * @param string $user
     * @param string $password
     * @param boolean $encoded Si la constraseña viene codificada
     * @return array Informacion del usuario ingresado o el problema del acceso.
     */

    public function login($subscription, $user, $password, $encoded = FALSE)
    {
        $return = array(
                'access' => FALSE,
                'message' => 'Los datos de ingreso son incorrectos.'
        );
        if (is_null($subscription) || is_null($user) || is_null($password)) {
            return $return;
        }
        $payments = new App_saas_Payment();
        $debt = $payments->checkMonthPayment($subscription);
        if (!$debt->access) {
            return (array) $debt;
        }
        $members = $this->_viewSubscriptionMember;
        $filter = $members->newFilter();
        if (!$encoded) {
            $password = md5($password);
        }
        $filter->password = $password;
        $filter->nick = $user;
        $filter->subscription = $subscription;
        $filter->status = 'active';
        $members->read($filter);
        if ($members->count() > 1 || $members->count() == 0) {
            return $return;
        }
        $member = $members->current();
        $token = new App_saas_vo_Token();
        $token->member = $member->member;
        $token->url = Mekayotl_tools_Request::referer();
        $token->generateToken();
        $tokens = new App_saas_tables_Token();
        $tokens->replace($token);
        $sesionData = (array) $member;
        $sesionData['access'] = TRUE;
        $sesionData['token'] = $token->token;
        $account = new App_saas_Account();
        $request = Mekayotl_tools_Request::singleton();
        $request->request['token'] = $token->token;
        $sesionData['account'] = $account->info($sesionData['account']);
        unset($sesionData['password']);
        return $sesionData;
    }

    /**
     * Obtine los usuarios de una suscripción.
     * @param integer $subscription
     * @return array
     */

    protected function getUsers($subscription)
    {
        if (is_null($subscription)) {
            return FALSE;
        }
        $members = $this->_viewSubscriptionMember;
        $filter = $members->newFilter();
        $filter->subscription = $subscription;
        $members->read($filter);
        return $members->toArray('member');
    }

    /**
     * Elimina un usuario de la suscripción.
     * @param integer $subscription
     * @param integer $member
     * @return boolean
     */

    protected function removeMember($subscription, $member)
    {
        if (is_null($subscription) || is_null($member)) {
            return FALSE;
        }
        $subscriptions = $this->_mapSubscriptionMember;
        $filter = $subscriptions->newFilter();
        $filter->member = $member;
        $filter->subscription = $subscription;
        return (boolean) $subscriptions->remove($filter);
    }

    /**
     *
     * @param integer $subscription
     * @param array|App_saas_vo_Member $member
     * @return App_saas_vo_MapSubscriptionMember
     */

    protected function addMember($subscription, $member)
    {
        $subscriptions = $this->_mapSubscriptionMember;
        $members = new App_saas_Member();
        $subscription = new App_saas_vo_MapSubscriptionMember(
                array(
                        'subscription' => $subscription
                ));
        $subscription->level = $member['level'];
        $subscription->email = $member['email'];
        $member = $members->save($member);
        $subscription->member = $member->member;
        $subscriptions->replace($subscription);
        $filter = $subscriptions->newFilter();
        $filter->subscription = $subscription->subscription;
        $filter->member = $subscription->member;
        $subscriptions->read($filter);
        return $subscriptions->current();
    }

    /**
     * Busca las suscriptiones del usuario de session con la cuenta indicada.
     * @param unknown_type $id
     * @return Mekayotl_database_dal_AccessAbstract
     */

    public function account($id)
    {
        $saasConfig = App_Saas::singleton()->config['SaaS'];
        $apiURL = $saasConfig['api'];
        $apiURL .= 'App/saas/Account/getSubscriptions.json';
        $params = array(
                'account' => $id,
                'member' => $_SESSION['Saas']['user']['member'],
                'token' => $_SESSION['Saas']['user']['token']
        );
        $saasAccounts = Mekayotl_tools_Request::externalRequest($apiURL,
                $params);
        $subscriptions = json_decode($saasAccounts, TRUE);
        return $subscriptions;
    }

    /**
     * Retorna la configuracion de la subscripcion segun el paquete.
     * @param unknown_type $id
     * @return Mekayotl_database_dal_AccessAbstract
     */

    public function getPackageConf($subscription)
    {
        $ret = $this->_subscription->getPackageConf($subscription);
        $ret = json_decode($ret);
        return (array) $ret;
    }

    /**
     * Retorna true si el numero de usuarios es igual o menor al que tiene
     * asignado en la configuracion segun el paquete al que esta ligado la
     * subscripcion.
     * @param unknown_type $id
     * @return bolean
     */

    public function enoughMembers($subscription)
    {
        $confMembers = $this->getPackageConf($subscription);
        $members = $this->_mapSubscriptionMember->getNumMembers($subscription);
        if ($members < $confMembers['maxMembers'])
            return array(
                    'result' => false
            );
        else
            return array(
                    'result' => true
            );
    }

}
