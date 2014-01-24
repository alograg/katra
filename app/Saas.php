<?php
//namespace Saas
/**
 * Manejador de servicios SaaS
 * @author $Author$
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package aqua.tools
 * @since $Date$
 * @subpackage Monitor
 * @version $Id$
 */

/**
 * Clase principal de la aplicación
 *
 * Trae el controladores y otras cosas.
 * @package aqua.tools
 * @subpackage Monitor
 */
class App_Saas extends Mekayotl_ApplicationAbstract
{
    /**#@+
     * @access protected
     */

    protected $_className = __CLASS__;
    /**
     * La version de la aplicación
     * @var string
     * @static
     */
    protected static $_version = '2.0.$Id$';
    /**
     * Constructor de la aplicación con sus adaptaciones
     * @param array $config La configuración de la instalación
     * @param boolean $autorun Si se auto ejecuta la aplicación al ser creada
     * @access public
     */

    public function __construct($config = Null)
    {
        Mekayotl_tools_Security::createSession();
        $this->config=array('Database'=>array());
        if (is_array($config) && !$this->config) {
            $this->config = &$config;
        }
        $postToLogin = isset($this->request->post['user'])
                && isset($this->request->post['password']);
        $this->request = Mekayotl_tools_Request::singleton();
        if ($this->request->request['token'] == 'SYSTEM') {
            unset($this->request->request['token']);
        }
        self::$_instance = $this;
        if ($this->request->renderAs != 'html') {
            return;
        }
        if (!isset($this->config['SaaS'])) {
            $allowedClass = array(
                    'App_Saas',
                    'App_ssas_Registration'
            );
            if (!in_array($this->request->className, $allowedClass)) {
                $this->request->className = '';
            }
            return;
        }
        if (!Mekayotl_tools_Security::hasSession(Mekayotl::getAplicationName())
                && !$postToLogin) {
            $exludeMethods = array(
                    'login',
                    'logout'
            );
            if ($this->request->renderAs == 'html'
                    && !in_array($this->request->methodName, $exludeMethods)
                    && !is_null($this->request->methodName)) {
                $_SESSION['lastUrl'] = $this->request->internal;
            }
            if (!in_array($this->request->methodName, $exludeMethods)) {
                $this->request->className = 'App_Saas';
                $this->request->methodName = 'login';
            }
        }
    }

    /**
     * Accion que se ejecuta por default cuando se pide una url para la cual
     * no se tiene un metodo definido.
     * @see core.ApplicationAbstract::defaultAction()
     */

    protected function defaultAction(Mekayotl_tools_Request $request)
    {
        if (!isset($this->config['SaaS'])) {
            return $this->_tryRegistration($request);
        }
        $hasSession = !Mekayotl_tools_Security::hasSession(
                Mekayotl::getAplicationName());
        if ($hasSession) {
            $return = $this->login();
        } else {
            $return = $this->index();
        }
        return $return;
    }

    /**
     * Trata de ejecutar los metodos de registro.
     * @param Mekayotl_tools_Request $request
     * @return mixed
     */

    protected function _tryRegistration(Mekayotl_tools_Request $request)
    {
        $request->className = 'App_saas_Registration';
        $return = $request->asMethod();
        if ($this->classRender instanceof Mekayotl_tools_renders_Html) {
            $viewPath = str_replace(
                    array(
                            '/',
                            'App_Saas',
                            'App_saas_'
                    ), '', $request->className) . '/' . $request->methodName;
            if ($request->methodName == '') {
                $viewPath .= 'index';
            }
            $sectionName = str_replace(' ', '',
                    ucwords(
                            str_replace(
                                    array(
                                            '/',
                                            'App_Saas',
                                            'App_saas_'
                                    ), ' ', $viewPath)));
            $this->classRender->setBodyId('section' . $sectionName);
            $this->changeView($viewPath);
        }
        return $return;
    }

    /**
     * Configura el render en caso de no ser AMFPHP\Gateway
     * @return ApplicationAbstract
     */

    public function configRender($render = null, $view = 'layout')
    {
        $implementationDirectory = realpath(dirname(__FILE__))
                . '/saas/implementation/';
        $this->useViewRender('default', $implementationDirectory, PUBLIC_PATH);
        $request = $this->request;
        $method = $request->asMethodName();
        if (!($this->classRender instanceof Mekayotl_tools_renders_Html)) {
            return NULL;
        }
        $this->classRender->setTitle('Nort' . (($method) ? ':' . $method : ''));
        parent::configRender();
        if (!$render) {
            $render = &$this->classRender;
            $innerRender = new Mekayotl_tools_Views();
            foreach ($render as $prop => $valor) {
                $innerRender->$prop = $valor;
            }
            $innerRender->onlyBody = TRUE;
            $render->setSectionInner($innerRender);
            $this->changeView($view);
        } else {
            $render->viewPath = $this->getPathFromView($view . '.phtml');
            $render->onlyBody = true;
        }
        $request = $this->request;
        $viewPath = $request->className . '/' . $request->methodName;
        if ($request->methodName == '') {
            $viewPath .= 'index';
        }
        $sectionName = str_replace(' ', '',
                ucwords(
                        str_replace(
                                array(
                                        '/',
                                        'App_Saas',
                                        'App_saas_'
                                ), ' ', $viewPath)));
        $this->classRender->setBodyId('section' . $sectionName);
        $render->addHttpMeta('X-UA-Compatible', 'chrome=IE8');
        $render->renderDocType = 'html5';
        $render->cached = FALSE;
        $render->cachedFilePath = $request->internal . '.' . $request->renderAs;
        $render->addNameMeta('generator', 'Aqua-Nort ' . self::$_version);
        $viewport = array(
                'width=device-width',
                'initial-scale=1.0',
                'maximum-scale=1.0',
                'user-scalable=no'
        );
        $render->addNameMeta('viewport', implode(', ', $viewport));
        $render
                ->setHeadBase(
                        array(
                                '@href' => $request->getBaseURL()
                        ));
        $icoURL = $this->getViewFilePath('nort/img/favicon.ico', true);
        $touchURL = $this
                ->getViewFilePath('nort/img/apple-touch-icon.png', true);
        $render->setAppleTouch($touchURL);
        $render->setFavicon($icoURL);
        $render
                ->addHeadStyles(
                        array(
                                '@media' => "all",
                                '@href' => $this
                                        ->getViewFilePath('nort.css', true)
                        ));
        $ie = array();
        $ie[] = Mekayotl_tools_renders_Html::makeTag('script',
                array(
                        '@src' => $this
                                ->getViewFilePath('html5shiv/html5shiv.js',
                                        true),
                        '@type' => 'text/javascript',
                        0 => ' '
                ))->toString();
        $ie[] = Mekayotl_tools_renders_Html::makeTag('link',
                array(
                        '@href' => $this->getViewFilePath('nort/ie.css', true),
                        '@type' => 'text/css',
                        '@rel' => 'stylesheet'
                ))->toString();
        $render->addIf('lt IE 9', $ie);
        return $render;
    }

    /**
     * Valida un token para el acceso a la aplicación
     * @param string $tokenId
     * @return boolean
     */

    public static function validToken($tokenId)
    {
        if ($tokenId === 'SYSTEM') {
            return TRUE;
        }
        $tokens = new App_saas_tables_Token();
        $token = new App_saas_vo_Token();
        $token->setNull();
        $token->url = Mekayotl_tools_Request::referer();
        $token->token = $tokenId;
        $tokens->read($token);
        if ($tokens->count() == 1) {
            return TRUE;
        }
        $app = self::singleton();
        $render = $app->classRender;
        return FALSE;
    }

    /**
     * Pagina de ingreso
     * @param string $user Usuario
     * @param string $password Contraseña
     */

    public function login($user = NULL, $password = NULL, $remeber = FALSE)
    {
        $app = App_Saas::singleton();
        $render = $app->classRender;
        $request = Mekayotl_tools_Request::singleton();
        $return = $this->_login($user, $password, $remeber);
        if ($render instanceof Mekayotl_tools_renders_Html) {
            if ($return) {
                $urlLogin = (string) $_SESSION['lastUrl'];
                unset($_SESSION['lastUrl']);
                $urlLogin = (substr_count($urlLogin, 'login') > 0
                        || $urlLogin == '') ? 'index' : $urlLogin;
                $urlLogin .= '.html';
                $here = Mekayotl_tools_Request::parseURI();
                $subdomain = array_pop($here->label);
                $member = $_SESSION['Saas']['user']['member'];
                if (isset($app->config['Database'])) {
                    $accounts = new App_saas_tables_ViewMemberAccount();
                    $accounts->readByUser($member);
                    $accountsCount = $accounts->count();
                    $account = $accounts->current();
                } else {
                    $saasConfig = $app->config['SaaS'];
                    $apiURL = $saasConfig['api'];
                    $apiURL .= 'App/saas/tables/ViewMemberAccount/readByUser.json';
                    $params['member'] = $member;
                    $saasLogin = Mekayotl_tools_Request::externalRequest(
                            $apiURL, $member);
                    $accounts = json_decode($saasLogin, TRUE);
                    $accounts = $accounts['rows'];
                    $accountsCount = count($accounts);
                    $account = (object) $accounts[0];
                }
                if ($accountsCount == 1 && $account->accountName != $subdomain) {
                    $token = md5($user . '_' . md5($password));
                    $saasConfig = App_Saas::singleton()->config['SaaS'];
                    $apiURL = Mekayotl_tools_Request::parseURI(
                            $saasConfig['api']);
                    $gotoURL = array_reverse($apiURL->label);
                    $gotoURL[0] = $account->accountName;
                    $gotoURL = implode('.', $gotoURL) . '/'
                            . $apiURL->path->basename;
                    Mekayotl_tools_Security::setCookie('Saas[token]', $token,
                            '+10 minute', $gotoURL);
                    $urlLogin = $gotoURL;
                }
                Mekayotl_tools_Request::redirect($urlLogin);
            }
            $ie = array();
            $scriptTag = array(
                    '@src' => 'http://goo.gl/9SDpP',
                    '@type' => 'text/javascript',
                    0 => ' '
            );
            $tag = Mekayotl_tools_renders_Html::makeTag('script', $scriptTag);
            $ie[] = $tag->toString();
            $tag->attributes()->src = $this
                    ->getViewFilePath('mootools/1.4.5/core/compressed.js', TRUE);
            $ie[] = $tag->toString();
            $tag->attributes()->src = $this
                    ->getViewFilePath('chromeframeinstall.js', TRUE);
            $ie[] = $tag->toString();
            $render->addIf('lt IE 9', $ie);
            $render->viewPath = $this->getPathFromView('login.phtml');
            if (isset($user)) {
                $render->setDataUser($user);
                if (!$return) {
                    $render->setDataAlert(TRUE);
                }
            }
        }
        return $return;
    }

    /**
     * Login de esta aplicación
     * @param string $user
     * @param string $password
     * @param bolean $remember
     * @return boolean
     */

    private function _login($user, $password, $remember = FALSE)
    {
        $loginByPost = !is_null($user) && !is_null($password);
        $token = Mekayotl_tools_Security::getAppCookie('token');
        $saasConfig = App_Saas::singleton()->config['SaaS'];
        $apiURL = $saasConfig['api'];
        if ($loginByPost) {
            $apiURL .= 'App/saas/Member/login.json';
            $params['user'] = $user;
            $params['password'] = $password;
            if ($remember) {
                $token = md5($user . '_' . md5($password));
                Mekayotl_tools_Security::setAppCookie('token', $token,
                        '+1 week');
            }
        } elseif ($token) {
            $params = array(
                    'token' => $token
            );
            $apiURL .= 'App/Saas/tokenLogin.json';
        } else {
            return FALSE;
        }
        $saasLogin = Mekayotl_tools_Request::externalRequest($apiURL, $params);
        $userData = json_decode($saasLogin, TRUE);
        if (!$userData) {
            return FALSE;
        }
        $userData['id'] = $userData['member'];
        $userData['type'] = $userData['level'];
        Mekayotl_tools_Security::createSession(Mekayotl::getAplicationName(),
                $userData);
        if ($token) {
            Mekayotl_tools_Security::setAppCookie('token', $token, '+1 week');
        }
        return TRUE;
    }

    /**
     * Salir de la session.
     * @param string $token
     */

    public function logout($token = NULL)
    {
        $logoutUser = $_SESSION[Mekayotl::getAplicationName()]['user']['nick'];
        Mekayotl_tools_Security::deleteSession(Mekayotl::getAplicationName());
        if ($forget) {
            $_GET['forget'] = TRUE;
            Mekayotl_tools_Security::removeAppCookie('token');
        }
        $_SESSION['logout']['user'] = $logoutUser;
        $render = App_Saas::singleton()->classRender;
        if ($render instanceof Mekayotl_tools_renders_Html) {
            $render->viewPath = $this->getPathFromView('logout.phtml');
            $render->setDataUser($_SESSION['logout']['user']);
        }
        return TRUE;
    }

    /**
     * Perimte el acceso con un token
     * @param string $token
     * @return boolean|object
     */

    public function tokenLogin($token)
    {
        $from = Mekayotl_tools_Request::referer();
        if (!$from) {
            return FALSE;
        }
        $from = Mekayotl_tools_Request::parseURI('api://' . $from);
        $here = Mekayotl_tools_Request::parseURI();
        $subdomain = array_pop(array_diff($from->label, $here->label));
        $accounts = new App_saas_tables_ViewMemberAccount();
        $accounts->readByToken($token, $subdomain);
        if ($accounts->count() == 0) {
            return FALSE;
        }
        if ($accounts->count() == 1) {
            $memberAccount = $accounts->current();
            $subsrciptions = new App_saas_Subscription();
            $subscription = $memberAccount->subscription;
            $user = $memberAccount->nick;
            $password = $memberAccount->password;
            return $subsrciptions->login($subscription, $user, $password, TRUE);
        }
        return eval(Mekayotl_tools_Request::getBaseURL());
    }

    /**
     * El inidice redirecciona al indice de cuentas
     */

    public function index()
    {
        Mekayotl_tools_Request::redirect('Account/index.html');
    }

    /**
     * Obtiene el logo de la suscripción.
     * @param unknown_type $params
     */

    public static function suscriptionLogo($params = '_1000x64_crop')
    {
        $logoDir = '/logos/';
        $findFiles = MEDIA_PATH . $logoDir . 'logo.*';
        $files = glob($findFiles);
        if (!is_file($files[0])) {
            $app = App_Saas::singleton();
            return $app->getViewFilePath('nort/img/logo.png', TRUE);
        }
        $file = pathinfo($files[0]);
        $logoOriginal = $logoDir . $file['basename'];
        $logoTransformed = $logoDir . $file['filename'] . $params . '.'
                . $file['extension'];
        if (!is_file(MEDIA_PATH . $logoTransformed)
                || filemtime(MEDIA_PATH . $logoOriginal)
                        > filemtime(MEDIA_PATH . $logoTransformed)) {
            Mekayotl_tools_Image::mediaTransform($logoOriginal,
                    $logoTransformed);
        }
        return 'medias' . $logoTransformed;
    }

    /**#@-*/

}
