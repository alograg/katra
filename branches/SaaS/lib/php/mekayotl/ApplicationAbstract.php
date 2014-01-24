<?php
//namespace core;
/**
 * Abstract para la creación de aplicaciones
 *
 * Esta clase abstracta contiene los métodos mínimos y loa que se deben
 * generar para que Mekayotl la pueda utilizar como aplicación
 * @author $Author$
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since $Date$
 * @subpackage core
 * @version $Id$
 */

/**
 * Abstract para la creación de aplicaciones
 *
 * Esta clase abstracta contiene los métodos mínimos y los que se deben
 * generar para que Mekayotl la pueda utilizar como aplicación
 * @package Mekayotl
 * @subpackage core
 */
abstract class Mekayotl_ApplicationAbstract
{
    /**#@+
     * @access protected
     * @static
     */
    /**
     * Instancia de Singleton
     * @var ApplicationAbstract
     */

    protected static $_instance;
    /**
     * El path donde se toman los archivos para la implementación y modelo
     * @var string
     */
    protected $_defaulViewPath = NULL;
    /**
     * El path al modelo de la instalación
     * @var string
     */
    protected $_instalationViewPath = NULL;
    /**
     * Valor predeterminado de los permisos al crear un directorio.
     * @var unknown_type
     */
    protected $_chmod = 0775;
    /**#@-*/
    /**#@+
     * @access public
     */
    /**
     * Render de la aplicación
     * @var Mekayotl_tools_renders_Html|Gateway
     */
    public $classRender = NULL;
    /**
     * El llamado para esta aplicación
     * @var Mekayotl_tools_Request
     */
    public $request = NULL;
    /**
     * Configuración del arranque de la aplicación
     * @var array
     */
    public $config;
    public $textFileNotFound = 'File not found';
    /**
     * Construcción de la clase
     * @param array $config Un arreglo asociativo con la configuración de
     * toda la aplicación.
     * @access public
     */

    protected function _initialize(array $config = Null)
    {
        if ($config) {
            $this->config = &$config;
        }
        if (!isset(self::$_instance)) {
            self::$_instance = &$this;
        }
        if (!$config && self::$_instance->config) {
            $this->config = self::$_instance->config;
        }
        if (!isset($this->request)) {
            $this->request = Mekayotl_tools_Request::singleton();
        }
        $contentType = Mekayotl_tools_Request::$headeContentTypes;
        $contentType = $contentType[$this->request->renderAs];
        header('Content-type: ' . $contentType);
        switch ($this->request->renderAs) {
            case 'amf':
            case 'json':
            case 'xmlrpc':
                Mekayotl::callExternal(
                        '/amfphp/core/' . $this->request->renderAs
                                . '/app/Gateway');
                $this->classRender = new Gateway();
                $this->classRender->setBaseClassPath(MEKAYOTL_PATH);
                break;
            case 'csv':
                $this->classRender = new Mekayotl_tools_renders_DownloadFile();
                break;
            case 'boletin':
            case 'htm':
            case 'rss':
                $this->classRender->onlyBody = TRUE;
            case 'html':
                $this->classRender = new Mekayotl_tools_renders_Html();
        }
    }

    /**
     * Destructor de la clase
     */

    public function __destruct()
    {
    }

    /**
     * Allows a class to decide how it will react when it is converted to a
     * string
     * @return string
     */

    public function __toString()
    {
        return $this->getClassName() . ' ' . $this->getVersion();
    }

    /**
     * Triggered when invoking inaccessible methods in an object context
     * @param string $method_name Nombre del método
     * @param array $arguments Argumentos pasados
     * @return variant El valor que regrese el método invocado
     */

    public function __call($methodName, array $arguments)
    {
        switch ($methodName) {
            case 'getClassName':
                return $this->_className;
            case 'getVersion':
                return $this->_version;
        }
        return $this->defaultAction($this->request);
    }

    /**
     * Ejecuta la aplicación
     * @access public
     * @return boolean Si la aplicación se ejecuto
     */

    public function run(array &$config = array())
    {
        if (!isset($config['class'])) {
            $config = $GLOBALS['config'];
        }
        $this->_initialize($config);
        $this->_securityCheck();
        $this->_defaultAction($this->request);
        return TRUE;
    }

    /**
     * Verifica la seguridad de la aplicación
     */

    protected function _securityCheck()
    {
        switch ($this->request->renderAs) {
            case 'amf':
                $className = $this->config['class'];
                $this->classRender->actions['security'] = $className
                        . '::amfSecurityCheck';
                break;
            default:
                self::httpSecurityCheck();
        }
    }
    /**
     * Verifica que el usuario tenga acceso al metodo solicitado en su llamada
     * por HTTP
     * @return boolean
     */

    public static function httpSecurityCheck()
    {
        return TRUE;
    }
    /**
     * Verifica que el usuario tenga acceso al metodo solicitado en su llamada
     * por AMF
     * @param MessageBody $amfbody
     */

    public static function amfSecurityCheck(&$amfbody)
    {
        if ($amfbody->noExec) {
            return TRUE;
        }
        return TRUE;
    }

    /**
     * Triggered when invoking inaccessible methods in a static context
     * @param string $method_name Nombre del método
     * @param array $arguments Argumentos pasados
     * @return variant El valor que regrese el método invocado
     * @throws Exception En caso de no existir el método
     */

    public static function __callStatic($methodName, $arguments)
    {
        switch ($methodName) {
            case 'getVersion':
                return self::$_version;
                break;
            case 'getConfig':
                return self::$_instance->config;
                break;
            default:
                throw new Exception('No static method', 0);
        }
    }

    /**
     * Genera la instancia de la clase
     * @return ApplicationAbstract
     */

    public static function singleton()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }

    /**#@-*/

    /**#@+
     * @access protected
     */
    /**
     * Este método es la acción predeterminada a la ejecución de un request
     * @param Mekayotl_tools_Request $request La solicitud
     * @access protected
     */

    protected function _defaultAction(Mekayotl_tools_Request $request)
    {
        $class = $request->asClassName();
        if (!$class) {
            $request->className = $this->getClassName();
        }
        $method = $request->asMethodName();
        $useOutputBuffering = TRUE;
        if (isset($this->config['html']['control_buffer'])) {
            $useOutputBuffering = $this->config['html']['control_buffer'];
        }
        if ($this->classRender instanceof Gateway) {
            $this->classRender->service();
            Mekayotl::end();
        } elseif ($this->classRender instanceof Mekayotl_tools_renders_Html) {
            $this->classRender
                    ->setTitle(
                            $request->className
                                    . (($method) ? ':' . $method : ''));
            $this->configRender();
            if ($class) {
                $methodReturn = $request->asMethod();
                if ($methodReturn instanceof Exception) {
                    $methodReturn = $this->return404();
                }
            } else {
                if (method_exists($this, $method)) {
                    $methodReturn = call_user_func_array(
                            array(
                                    $this,
                                    $method
                            ), $request->params);
                } else {
                    $methodReturn = $this->defaultAction($request);
                }
            }
            $this->classRender->setSectionReturn($methodReturn);
            if (MEKAYOTL_DEBUG) {
                if (isset($this->classRender->viewPath)) {
                    $this->classRender
                            ->addHeader('Mekayotl-View',
                                    $this->classRender->viewPath);
                }
            }
            if ($this->classRender instanceof Mekayotl_tools_Views) {
                $this->classRender->useOutputBuffering = $useOutputBuffering;
            }
            if ($useOutputBuffering) {
                Mekayotl_Debug::trace('PreOptup: ' . ob_get_clean());
                ob_start();
            }
            echo $this->classRender;
            if ($useOutputBuffering) {
                ob_end_flush();
            }
            Mekayotl::end();
        } elseif ($this->classRender instanceof Mekayotl_tools_renders_DownloadFile) {
            if ($useOutputBuffering) {
                ob_start();
            }
            if ($class) {
                $methodReturn = $request->asMethod();
            } else {
                if (method_exists($this, $method)) {
                    $methodReturn = call_user_func_array(
                            array(
                                    $this,
                                    $method
                            ), $request->params);
                } else {
                    $methodReturn = $this->defaultAction($request);
                }
            }
            $this->classRender->setContent($methodReturn);
            echo $this->classRender;
            if ($useOutputBuffering) {
                ob_end_flush();
            }
            Mekayotl::end();
        }
        $this->configRender();
        if ($this->isViewFile()) {
            $this->viewFile();
        }
        $this->return404();
    }

    /**
     * Configura el render en caso de no ser AMFPHP\Gateway
     * @return ApplicationAbstract
     */

    protected function configRender()
    {
        if ($this->classRender instanceof Mekayotl_tools_renders_Html) {
            $request = $this->request;
            if ($this->classRender instanceof Mekayotl_tools_Views) {
                $class = explode('_', strtolower($request->asClassName()));
                $thisClassName = explode('_', strtolower($this->_className));
                $finalClass = implode('/', array_diff($class, $thisClassName))
                        . '/';
                $method = $request->asMethodName();
                if ($finalClass == '/') {
                    $finalClass = '';
                }
                if ($method == '') {
                    $method = 'index';
                }
                $this->changeView($finalClass . $method);
            } else {
                $this->classRender->setBody('{return}');
            }
            $this->classRender->setBase($request->getBaseURL());
        }
        return $this;
    }

    /**
     * Establece el uso de vistas.
     * @param string $viewPath La ruta relativa a la instalación donde se
     * encuentran las vistas
     * @param string $frameWorkBase Ubicación de las plantillas
     * predeterminadas si no se encuentran en la instalación.
     * @param string $instalationBase Ubicación de las plantillas de
     * la instalación.
     */

    protected function useViewRender($viewPath, $frameWorkBase = MEKAYOTL_PATH,
            $instalationBase = PUBLIC_PATH)
    {
        $this->setViewPath($viewPath, $frameWorkBase, $instalationBase);
        if ($this->classRender instanceof Mekayotl_tools_renders_Html) {
            $this->classRender = new Mekayotl_tools_Views();
        }
    }

    /**
     * Establece el path de la vista.
     * @param string $viewPath La ruta relativa a la instalación donde se
     * encuentran las vistas
     * @param string $frameWorkBase Ubicación de las plantillas
     * predeterminadas si no se encuentran en la instalación.
     * @param string $instalationBase Ubicación de las plantillas de
     * la instalación.
     */

    protected function setViewPath($viewPath, $frameWorkBase = MEKAYOTL_PATH,
            $instalationBase = PUBLIC_PATH)
    {
        $viewPath = array(
                $frameWorkBase,
                $viewPath
        );
        $this->_defaulViewPath = str_replace(
                array(
                        '//',
                        DIRECTORY_SEPARATOR
                ), '/', strtolower(implode('/', $viewPath)));
        $viewPath[0] = $instalationBase;
        $this->_instalationViewPath = str_replace(
                array(
                        '//',
                        DIRECTORY_SEPARATOR
                ), '/', strtolower(implode('/', $viewPath)));
    }

    /**
     * Verifica si un archivo solicitado es un archivo dentro de las plantillas.
     * @return boolean
     */

    protected function isViewFile()
    {
        $request = $this->request;
        $toBrowser = $this->getViewFilePath($request->parsed->path->basename);
        $internal = $request->internal . '.' . $request->renderAs;
        if ($toBrowser == $internal) {
            return TRUE;
        }
        $original = explode('/', $toBrowser);
        $base = explode('/', $internal);
        $diff = array_diff($base, $original);
        $diff[] = $request->parsed->path->basename;
        $toBrowser = $this->getViewFilePath(implode('/', $diff));
        return $toBrowser == $internal;
    }

    /**
     * Obtiene la ubicación del archivo indicado dentro de la instalación.
     * @param string $filePath Ruta de archivo solicitado, relativa a
     * la instalación.
     * @param boolean $toBrowser Si la ruta sera< regresada al navegador
     * para eliminar datos de servidor.
     * @return string Ruta al archivo
     */

    public function getViewFilePath($filePath, $toBrowser = TRUE)
    {
        $filePath = pathinfo($filePath);
        $request = $this->request;
        $dir = NULL;
        switch ($filePath['extension']) {
            case 'css':
            case 'gif':
            case 'jpg':
            case 'png':
            case 'ico':
                $dir = 'style';
                break;
            case 'js':
                $dir = 'js';
                break;
            case 'tmx':
                $dir = 'languages';
                break;
            case 'phtml':
            default:
                $dir = 'html';
        }
        if ($filePath['dirname'] == '\\' || $filePath['dirname'] == '.') {
            $filePath['dirname'] = NULL;
        }
        $aReturnPath = array(
                $this->_instalationViewPath,
                $dir,
                $filePath['dirname'],
                $filePath['basename']
        );
        $aReturnPath = array_filter($aReturnPath);
        if (!$toBrowser) {
            return $aReturnPath;
        }
        $publicBasePath = str_replace(
                array(
                        '//',
                        DIRECTORY_SEPARATOR
                ), '/', strtolower(PUBLIC_PATH));
        $aReturnPath = implode('/', $aReturnPath);
        $aReturnPath = str_replace($publicBasePath . '/', '', $aReturnPath);
        return $aReturnPath;
    }

    /**
     * Regresa una pagina y cabecera 404 indicando que el archivo no existe.
     * @return string
     */

    protected function return404()
    {
        header("HTTP/1.0 404 Not Found");
        $return = '<h1>404</h1>';
        $return .= "<p>" . $this->textFileNotFound . '</p>';
        return $return;
    }

    /**
     * Verifica que el archivo se encuentre en la libreria.
     * @param string $uri El url que se solicita.
     */

    protected function _isInLibs($uri)
    {
        $searchStyle = substr_count($uri, 'style/');
        $searchJS = substr_count($uri, 'js/');
        $source = LIBS_PATH . '/';
        if ($searchStyle) {
            $source .= str_replace('style/', 'css/', strstr($uri, 'style/'));
        } elseif ($searchJS) {
            $source .= strstr($uri, 'js/');
        }
        if (is_file($source)) {
            return $source;
        }
        $optionExternal = str_replace(
                array(
                        'css/',
                        'js/'
                ),
                array(
                        'css/externals/',
                        'js/externals/'
                ), $source);
        if (is_file($optionExternal)) {
            return $optionExternal;
        }
        $optionMekayotl = str_replace(
                array(
                        'css/',
                        'js/'
                ),
                array(
                        'css/mekayotl/',
                        'js/mekayotl/'
                ), $source);
        if (is_file($optionMekayotl)) {
            return $optionMekayotl;
        }
        return FALSE;
    }

    /**
     * Regresa un archivo solicitado de las vista al navegador.
     * @throws Exception Si el directorio/archivo no puede ser creado en la
     * instalación desde la base de la aplicación.
     * @return mixed El contenido del archivo solicitado
     */

    protected function viewFile()
    {
        $request = $this->request;
        $uri = $request->getOriginalURI();
        $searchStyle = substr_count($uri, 'style/');
        $searchJS = substr_count($uri, 'js/');
        if (($searchStyle + $searchJS) == 0) {
            return $this->return404();
        }
        $destino = dirname($this->_instalationViewPath) . '/'
                . $request->internal . '.' . $request->renderAs;
        $source = $this->_isInLibs($uri);
        if (!$source) {
            $source = dirname($this->_defaulViewPath) . '/'
                    . $request->internal . '.' . $request->renderAs;
        }
        if (!is_file($source)) {
            $this->return404();
        }
        if ($source != $destino) {
            if (!Mekayotl::generatePath($destino)) {
                throw new Exception(
                        'Can\'t generate path: ' . dirname($destino), 0);
            }
            if (!copy($source, $destino)) {
                throw new Exception(
                        'Can\'t generate file: ' . $destino . ' from '
                                . $source, 0);
            }
        }
        if (is_file($destino)) {
            chmod($destino, $this->_chmod);
        }
        ob_clean();
        $size = filesize($destino);
        header("Content-Length: " . $size);
        flush();
        readfile($destino);
        Mekayotl::end();
    }

    /**
     * Obtiene el path al archivo del modelo de la instalación
     *
     * Si este archivo no se encuentra lo toma de la implementación de
     * la instalación dentro de la librería
     * Entre sus actividades analiza donde puede estar el archivo utilizando la
     * extensión del archivo y la extensión del request
     * @param string $filePath Nombre/Path al archivo del modelo
     * @throws Exception En caso de no poder instalar el archivo de
     * la biblioteca
     * @return string El path completo al archivo
     */

    public function getPathFromView($filePath)
    {
        $pathInfo = pathinfo($filePath);
        $allowedExtensions = array(
                'css',
                'gif',
                'jpg',
                'js',
                'phtml',
                'png',
                'tmx'
        );
        if (!in_array($pathInfo['extension'], $allowedExtensions)) {
            throw new Exception('Extension not alowed', 0);
        }
        $generate = $pathInfo['extension'] == 'phtml';
        $aPath = $this->getViewFilePath($filePath, !$generate);
        if ($generate) {
            $this->generateViewFile($aPath);
        }
        if (is_array($aPath)) {
            $aPath = implode('/', $aPath);
        }
        return strtolower($aPath);
    }

    /**
     * Crea el archivo de vista en la instalación en base al archivo base de la
     * aplicación o uno limpio en la aplicación y la instalación.
     * @param array $aPath Un arreglo con las priesas de la ruta
     * al archivo, donde 0 es la base relativa a la instalación.
     * @throws Exception Si el directorio/archivo no puede ser creado en la
     * instalación desde la base de la aplicación.
     * @return boolean Si el archivo fue generado.
     */

    protected function generateViewFile(array $aPath)
    {
        $path = strtolower(implode('/', $aPath));
        if (!is_file($path)) {
            $aPath[0] = $this->_defaulViewPath;
            $source = str_replace($correctPath, '/',
                    strtolower(implode('/', $aPath)));
            if (!is_file($source)) {
                Mekayotl::generatePath($source);
                sleep(1);
                file_put_contents($source,
                        ((MEKAYOTL_DEBUG) ? "\n<!--\nSOURCE: " . $source
                                        . "\n<?php print __FILE__;?>\n-->\n"
                                : ''));
            } elseif (is_file($source)) {
                chmod($source, $this->_chmod);
            }
            if ($source != $path) {
                if (!Mekayotl::generatePath($path)) {
                    throw new Exception(
                            'Can\'t generate path: ' . dirname($path), 0);
                }
                sleep(1);
                if (!copy($source, $path)) {
                    throw new Exception('Can\'t generate file: ' . $path, 0);
                }
                sleep(1);
            }
        }
        if (is_file($path)) {
            chmod($path, $this->_chmod);
        }
        return is_file($path);
    }

    /**
     * Cambia la vista establecida
     * @param string $viewPath Nombre del archivo
     * @return core.Waakun
     */

    protected function changeView($viewPath = 'index')
    {
        if ($this->classRender instanceof Mekayotl_tools_Views) {
            $this->classRender->viewPath = $this
                    ->getPathFromView($viewPath . '.phtml');
            $sectionName = str_replace(' ', '',
                    ucwords(str_replace('/', ' ', $viewPath)));
            $this->classRender->setBodyId('section' . $sectionName);
        }
        return $this;
    }

    /**
     * Despliega información PHP del servidor.
     */

    protected function info()
    {
        if (MEKAYOTL_DEBUG) {
            phpinfo();
            Mekayotl::end();
        }
    }

    /**
     * Acción predeterminada cuando el request no pudo ejecutarse en esta u
     * otra clase
     * @param Mekayotl_tools_Request $request
     * @abstract
     */

    abstract protected function defaultAction(Mekayotl_tools_Request $request);
    /**#@-*/

}
