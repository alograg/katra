<?php

/**
 * Lógica de los registros a la aplicación.
 *
 * Métodos manejo de registros.
 * @author Henry <henry@aquainteractive.com.mx>
 * @copyright Copyright (c) 2012, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package App.SaaS
 * @since Revisión $id$ $date$
 * @subpackage Floyd
 * @version 1.0.0
 */

/**
 * Lógica de los registros a la aplicación.
 *
 * Métodos manejo de registros.
 * @package App.SaaS
 * @subpackage SaaS
 */
class App_saas_Registration extends Mekayotl_database_dal_BusinessAbstract
{
    /**
     * Acceso a los miembros.
     * @var App_saas_tables_Account
     */

    protected $_account = NULL;
    /**
     * Acceso a los miembros.
     * @var App_saas_tables_Subscription
     */
    protected $_subscription = NULL;
    /**
     * Acceso a los miembros.
     * @var App_saas_tables_Package
     */
    protected $_package = NULL;
    /**
     * Acceso a los miembros.
     * @var App_saas_tables_Package
     */
    protected $_member = NULL;

    /**
     * Constructor
     */

    public function __construct()
    {
        $this->_account = new App_saas_tables_Account();
        $this->_subscription = new App_saas_tables_Subscription();
        $this->_package = new App_saas_tables_Package();
        $this->_member = new App_saas_tables_Member();
        $this->setAdapter(Mekayotl::adapterFactory('Database'));
    }

    /**
     * Empisea el registro de la suscripción.
     * @param int $package
     * @return App_saas_vo_Package
     */

    public function join($package)
    {
        $packages = $this->_package;
        $packages->read(array(
                        'package' => $package
                ));
        $package = $packages->current();
        return $package;
    }

    /**
     * Verifica que el nombre sea permitido.
     * @param string $name
     * @return boolean
     */

    public function verifyName($name)
    {
        $excludeNicks = array(
                'develop',
                'www',
                'aqua',
                'demo',
                'aquaman'
        );
        if (in_array($name, $excludeNicks)) {
            return FALSE;
        }
        $account = new App_saas_vo_Account();
        $account->name = $name;
        $this->_account->read($account);
        if ($this->_account->count() > 0) {
            $_SESSION['registration']['error'] = 'La cuenta ya existe.';
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Realiza el registro y la instalacion.
     * @param array $registration
     * @param array $password
     * @return boolean|multitype:App_saas_vo_Account App_saas_vo_Subscription App_saas_vo_Package
     */

    public function doRegistration($registration = NULL, $password = NULL)
    {
        $return = array(
                'registration' => $this->wait($registration, $password),
                'session' => $_SESSION['registration']
        );
        $app = App_Saas::singleton();
        $instalactionURL = array_reverse($app->request->parsed->label);
        $instalactionURL[0] = $return['registration']['account']->name;
        $instalactionURL = implode('.', $instalactionURL);
        $instalactionURL .= '/'
                . strtolower(
                        $_SESSION['registration']['package']['serviceName'])
                . '/';
        $return['url'] = $instalactionURL;
        return $return;
    }

    /**
     * Realiza el registro y la instalacion.
     * @param array $registration
     * @param array $password
     * @return boolean|multitype:App_saas_vo_Account App_saas_vo_Subscription App_saas_vo_Package
     */

    public function wait($registration = NULL, $password = NULL)
    {
        if (!is_array($registration)) {
            $_SESSION['registration']['error'] = 'No proporciono datos correctos';
            return FALSE;
        }
        if (!$this->verifyName($registration['name'])) {
            return FALSE;
        }
        $_SESSION['registration'] = $registration;
        $_SESSION['registration']['package'] = (array) $this
                ->join($registration['package']);
        $registration['nick'] = Mekayotl::getFriendly($registration['nick']);
        $password = array_unique($password);
        if (count($password) != 1) {
            $_SESSION['registration']['error'] = 'Problemas con la contrase&ntilde;a';
            return FALSE;
        }
        $registration['password'] = $password[0];
        $configurationTemplate = '{"database":"mysql://{name}:{password}@localhost/{name}_%"}';
        $account = new App_saas_vo_Account();
        $account->name = $registration['name'];
        $account->firm = $registration['firm'];
        $account->email = $registration['email'];
        $account->configuration = Mekayotl_tools_utils_Conversion::substitute(
                $registration, $configurationTemplate);
        $account->account = $this->_account->create($account);
        $subscription = new App_saas_vo_Subscription($registration);
        $subscription->account = $account->account;
        $subscription->package = $registration['package'];
        $subscription->status = 'active';
        $subscription->createdOn = date('Y-m-d');
        $subscription->subscription = $this->_subscription
                ->create($subscription);
        $member = new App_saas_vo_Member();
        $member->email = $registration['email'];
        $member->fullName = implode(' ', $registration['userName']);
        $member->nick = $registration['adminNick'];
        $member->password = $registration['password'];
        $member->level = 'admin';
        $request = &Mekayotl_tools_Request::singleton();
        $request->request['token'] = 'SYSTEM';
        $bmSubscription = new App_saas_Subscription();
        $relation = $bmSubscription
                ->__call('addMember',
                        array(
                                $subscription->subscription,
                                (array) $member
                        ));
        $member = $this->_member
                ->read(
                        array(
                                'member' => $relation->member
                        ))->current();
        $token = md5($member->nick . '_' . $member->password);
        $host = array_reverse(Mekayotl_tools_Request::parseURI()->label);
        $member->password = $registration['password'];
        $return = array(
                'account' => $account,
                'subscription' => $subscription,
                'member' => $member
        );
        $cmd = APPLICATION_PATH . '/saas/instalations/install.sh';
        $params = array(
                implode('.', $host),
                strtolower($_SESSION['registration']['package']['serviceName']),
                $_SESSION['registration']['package']['sqlCreation'],
                $subscription->subscription,
                $account->name,
                $registration['password'],
                $account->account
        );
        $cmd .= ' ' . implode(' ', $params);
        $logFile = 'log_' . $subscription->subscription . '.txt';
        //$cmd .= ' 0>>' . $logFile. ' 2>>' . $logFile. ' 1>>' . $logFile;
        $cmdReturn = shell_exec($cmd);
        $defaultDataSqlFile = APPLICATION_PATH
                . '/saas/instalations/databases/' . $params[1]
                . '/mysql/data/default.sql';
        $sql = implode("\n", file($defaultDataSqlFile));
        $sql = Mekayotl_tools_utils_Conversion::substitute((array) $member,
                $sql);
        $accountDsn = json_decode(
                str_replace('%', $params[1], $account->configuration));
        $adapter = Mekayotl::adapterFactory($accountDsn->database);
        $adapter->query($sql);
        $host[0] = $registration['name'];
        Mekayotl_tools_Security::setCookie('Floyd[token]', $token, '+2 hour',
                implode('.', $host));
        $return['mail'] = $this
                ->sendInvitation($return, $registration,
                        $_SESSION['registration']);
        return $return;
    }

    public function sendInvitation($return, $registration, $session)
    {
        $app = App_Floyd::singleton();
        $app->configRender();
        $bodyOb = new Mekayotl_tools_Views();
        $bodyOb->viewPath = $app->getPathFromView('mails/welcome.phtml');
        $instalactionURL = array_reverse($app->request->parsed->label);
        $instalactionURL[0] = $return['account']->name;
        $instalactionURL = implode('.', $instalactionURL);
        $instalactionURL .= '/'
                . strtolower(
                        $_SESSION['registration']['package']['serviceName'])
                . '/';
        $return['url'] = $instalactionURL;
        $bodyOb->setSectionReturn($return);
        $bodyOb->setSectionRegistration($registration);
        $bodyOb->setSectionSession($session);
        $bodyOb->onlyBody = TRUE;
        $mail = new Mekayotl_tools_Mailer();
        $subject = 'NORT te da la bienvenida';
        $emails = array();
        $emails[] = array(
                'email' => $return['member']->email,
                'name' => $return['member']->fullName
        );
        $emails[] = array(
                'email' => 'henry@aquainteractive.com',
                'name' => 'Henry (testing Nort)'
        );
        return $mail->sendGridMailList($emails, $subject, (string) $bodyOb);
    }

}
