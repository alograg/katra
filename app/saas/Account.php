<?php

/**
 * Lógica de las cuentas de la aplicación.
 *
 * Métodos manejo de cuentas.
 * @author Henry <henry@aquainteractive.com.mx>
 * @copyright Copyright (c) 2012, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package App.SaaS
 * @since Revisión $id$ $date$
 * @subpackage Floyd
 * @version 1.0.0
 */

/**
 * Lógica de las cuentas de la aplicación.
 *
 * Métodos manejo de cuentas.
 * @package App.SaaS
 * @subpackage SaaS
 */
class App_saas_Account extends Mekayotl_database_dal_BusinessAbstract
{

    /**
     * Acceso a los miembros.
     * @var App_SaaS_tables_Account
     */

    protected $_account = NULL;
    /**
     *
     * @var App_saas_tables_ViewMemberAccount
     */
    protected $_memberAccounts = NULL;
    /**
     *
     * @var App_saas_tables_ViewInvoiceBalance
     */
    protected $_viewInvoices = NULL;

    public function __construct()
    {
        $appConfig = App_Saas::singleton()->config;
        if (isset($appConfig['Database'])) {
            $this->_account = new App_saas_tables_Account();
            $this->_memberAccounts = new App_saas_tables_ViewMemberAccount();
            $this->_viewInvoices = new App_saas_tables_ViewInvoiceBalance();
            $this->setAdapter(Mekayotl::adapterFactory('Database'));
        }
        if (!isset($_SESSION['Saas']['accountInfo'])
                && isset($appConfig['SaaS']['account'])) {
            $saasConfig = $appConfig['Saas']['SaaS'];
            $subscription = new App_saas_Subscription();
            $_SESSION['Saas']['accountInfo'] = $subscription
                    ->account($appConfig['SaaS']['account']);
        }
    }

    /**
     * Realiza un ingrso a la aplicacion.
     * @param string $account
     * @return array Informacion de la cuenta ingresado.
     */

    protected function info($account)
    {
        if (is_null($account)) {
            return FALSE;
        }
        $filter = new App_saas_vo_Account();
        $filter->setNull();
        $accounts = $this->_account;
        $filter->account = $account;
        $accounts->read($filter);
        if ($accounts->count() != 1) {
            return FALSE;
        }
        $account = $accounts->current();
        return $account;
    }

    /**
     * Actualiza datos de la cuenta
     * @param array $account
     * @return App_saas_vo_Account
     */

    protected function update($account)
    {
        if (is_null($account)) {
            return FALSE;
        }
        $filter = new App_saas_vo_Account($account);
        $accounts = $this->_account;
        $accounts->update($filter);
        $filter->setNull();
        $filter->account = $account['account'];
        $accounts->read($filter);
        if ($accounts->count() != 1) {
            return FALSE;
        }
        $account = $accounts->current();
        return $account;
    }

    protected function byMember($member)
    {
        return $this->_memberAccounts->readByUser($member)->toArray();
    }

    /**
     * Busca las suscriptiones del usuario de session con la cuenta indicada.
     * @param unknown_type $id
     * @return Mekayotl_database_dal_AccessAbstract
     */

    protected function getSubscriptions($account, $member)
    {
        $services = new App_saas_tables_ViewMemberService();
        $filter = $services->newFilter();
        $filter->member = $member;
        $filter->account = $account;
        $return = array();
        $return['account'] = $this->_account
                ->read(
                        array(
                                'account' => $account
                        ))->current();
        $return['subsrctiptions'] = $services->read($filter)->toArray();
        return $return;
    }

    protected function getInvoices($account)
    {
        $filter = new App_saas_vo_Invoice(array());
        $filter->account = $account;
        $return = $this->_viewInvoices->read($filter, 'createdOn DESC');
        return $return->toArray();
    }

    public function getFullInvoice($invoice, $account)
    {
        $filter = new App_saas_vo_Invoice(array());
        $filter->invoice = $invoice;
        $filter->account = $account;
        $invoice = (array) $this->_viewInvoices
                ->read($filter, 'createdOn DESC')->current();
        $filter->account = NULL;
        $concepts = new App_saas_tables_Concept();
        $invoice['concepts'] = $concepts->read($filter)->toArray();
        $payments = new App_saas_tables_Payment();
        $invoice['payments'] = $payments->read($filter, 'createdAt ASC')
                ->toArray();
        return $invoice;
    }

    public function bill($invoiceId)
    {
        $saasConfig = App_Saas::singleton()->config['SaaS'];
        $apiURL = $saasConfig['api'];
        $apiURL .= 'App/saas/Account/getFullInvoice.json';
        $accountId = $_SESSION['Saas']['accountInfo']['account']['account'];
        $params = array(
                'invoice' => $invoiceId,
                'account' => $accountId,
                'token' => $_SESSION['Saas']['user']['token']
        );
        $saasReturn = Mekayotl_tools_Request::externalRequest($apiURL, $params);
        $invoices = json_decode($saasReturn, FALSE);
        return $invoices;
    }

    /**
     * Listado de facturas.
     * return array
     */

    public function invoices()
    {
        $saasConfig = App_Saas::singleton()->config['SaaS'];
        $apiURL = $saasConfig['api'];
        $apiURL .= 'App/saas/Account/getInvoices.json';
        $accountId = $_SESSION['Saas']['accountInfo']['account']['account'];
        $params = array(
                'account' => $accountId,
                'token' => $_SESSION['Saas']['user']['token']
        );
        $saasReturn = Mekayotl_tools_Request::externalRequest($apiURL, $params);
        $invoices = json_decode($saasReturn, FALSE);
        return $invoices;
    }

    /**
     * La lista de las cuentas de los usuarios corrientes
     * @return Interator
     */

    public function index()
    {
        return $_SESSION['Saas']['accountInfo']['subsrctiptions'];
    }

    /**
     * Visualiza la cuenta para su edicion.
     * @param array $account
     * @return StdClass
     */

    public function view($account)
    {
        $app = App_Saas::singleton();
        $render = $app->classRender;
        if (is_array($account) && $account['account'] > 0) {
            $this->save($account);
        }
        if ($render instanceof Mekayotl_tools_renders_Html) {
            $render->setBodyId('secctionAccountConfig');
        }
        return (object) $_SESSION['Saas']['accountInfo']['account'];
    }
    /**
     * Manda los datos de la cuenta al SaaS para ser actualizados.
     * @param array $account
     */

    private function save(array $account)
    {
        $saasConfig = &App_Saas::singleton()->config['SaaS'];
        $apiURL = $saasConfig['api'] . 'App/saas/Account/update.json';
        $send = array(
                'token' => $_SESSION['Saas']['user']['token']
        );
        $send['account'] = $account;
        $saasAccount = Mekayotl_tools_Request::externalRequest($apiURL, $send);
        $_SESSION['Saas']['accountInfo']['account'] = json_decode(
                $saasAccount, TRUE);
    }

}
