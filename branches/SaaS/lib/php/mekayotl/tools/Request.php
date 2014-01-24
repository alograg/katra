<?php
//namespace core\tools;
/**
 * Clase encargada de manejar las solicitudes
 *
 * Esta clase se encarga de manejar las solicitudes que llega por apache
 * @author $Author$
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since $Date$
 * @subpackage tools
 * @version $Id$
 */

/**
 * Clase encargada de manejar las solicitudes
 *
 * Esta clase se encarga de manejar las solicitudes que llega por apache
 * @package Mekayotl
 * @subpackage tools
 */
class Mekayotl_tools_Request
{
    /**#@+
     * @access private
     * @static
     */
    /**
     * Instancia del singleton
     * @var core\tools\Request
     */

    private static $_instance;
    /**
     * URL original solicitado
     *
     * Ejemplo:
     * http://www.example.com/carpeta/aplication/controller/action/param1/
     * param2.html?string=string&array[]=1&array[]=2
     * @var string
     */
    private static $_original = NULL;
    /**
     * URL base del llamado
     *
     * Ejemplo:
     * http://www.example.com/carpeta
     * @var string
     */
    private static $_baseURL = NULL;
    /**#@-*/
    /**#@+
     * @access public
     */
    /**
     * Variables enviadas por GET
     *
     * Ejemplo:
     * array(
     *  'string' => 'string',
     *  'array' => array(
     *   0 => 1,
     *   1 => 2
     * );
     * @var array
     */
    public $get = array();
    /**
     * Variables enviadas por POST
     * @var array
     */
    public $post = array();
    /**
     * Variables enviadas en el REQUEST
     * @var array
     */
    public $request = array();
    /**
     * El URL parseado en un objeto
     *
     * Descripción del objeto
     * <dl>
     * <dt>scheme</dt>
     * <dd>(string) http|https</dd>
     * <dt>host</dt>
     * <dd>(string) www.example.com</dd>
     * <dt>path</dt>
     * <dd>(object)
     * <dl>
     * <dt>dirname</dt>
     * <dd>(string) /carpeta/aplication/controller/action/param1</dd>
     * <dt>basename</dt>
     * <dd>(string) param2.html</dd>
     * <dt>extension</dt>
     * <dd>(string) html</dd>
     * <dt>filename</dt>
     * <dd>(string) param2</dd>
     * </dl>
     * </dd>
     * <dt>query</dt>
     * <dd>(object) Las variables pasadas por GET convertidas en objeto</dd>
     * </dd>
     *
     * @var object
     */
    public $parsed;
    /**
     * Definición de que salida se le va a dar a los datos
     * @var string
     */
    public $renderAs = 'html';
    /**
     * Path predeterminado en caso de no tener un URL solo del host
     * @var string
     */
    public $internal = 'index';
    /**
     * Deducción del nombre de clase según el URL solicitado
     * @var string
     */
    public $className = NULL;
    /**
     * Deducción del nombre de método según el URL solicitado
     * @var string
     */
    public $methodName = NULL;
    /**
     * Deducción de los parámetros para el método según el URL solicitado
     * @var array
     */
    public $params = NULL;
    /**
     * Content Type para la salida solicitada
     * @var array
     */
    public static $headeContentTypes = array(
            'amf' => 'application/x-amf',
            'asc' => 'text/plain',
            'awk' => 'text/plain',
            'bash' => 'text/plain',
            'bsh' => 'text/plain',
            'bz2' => 'application/octet-stream',
            'c' => 'text/plain',
            'chm' => 'application/octet-stream',
            'csh' => 'text/plain',
            'css' => 'text/css; charset=UTF-8',
            'csv' => 'application/vnd.ms-excel',
            'docx' => 'application/vnd.openxmlformats',
            'dotx' => 'application/vnd.openxmlformats',
            'exe' => 'application/octet-stream',
            'flv' => 'video/x-flv',
            'gawk' => 'text/plain',
            'gif' => 'image/gif',
            'h' => 'text/plain',
            'html' => 'text/html; charset=UTF-8',
            'ico' => 'image/x-icon',
            'ico' => 'image/x-icon',
            'in' => 'text/plain',
            'ini' => 'text/plain',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'js' => 'text/javascript; charset=UTF-8',
            'json' => 'application/json',
            'md5' => 'text/plain',
            'msi' => 'application/octet-stream',
            'nawk' => 'text/plain',
            'pdb' => 'application/x-pilot',
            'pdf' => 'application/octet-stream',
            'phps' => 'application/x-httpd-php-source',
            'png' => 'image/png',
            'potx' => 'application/vnd.openxmlformats',
            'ppsx' => 'application/vnd.openxmlformats',
            'pptx' => 'application/vnd.openxmlformats',
            'prc' => 'application/x-pilot',
            'rar' => 'application/octet-stream',
            'rdf' => 'application/rdf+xml; charset=UTF-8',
            'rss' => 'application/rss+xml; charset=UTF-8',
            'sh' => 'text/plain',
            'sh1' => 'text/plain',
            'sha' => 'text/plain',
            'svg' => 'image/svg+xml',
            'swf' => 'application/x-shockwave-flash',
            'tgz' => 'application/octet-stream',
            'tif' => 'image/tiff',
            'var' => 'text/plain',
            'wmv' => 'video/x-ms-wmv',
            'xhtml' => 'application/xhtml+xml; charset=UTF-8',
            'xlsx' => 'application/vnd.openxmlformats',
            'xltm' => 'application/vnd.openxmlformats',
            'xltx' => 'application/vnd.openxmlformats',
            'xml' => 'text/xml',
            'xmlrpc' => 'application/xml',
            'xrdf' => 'application/xrds+xml'
    );
    /**#@-*/
    /**#@+
     * @access public
     */
    /**
     * Constructor del objeto
     *
     * Realiza las operaciones necesarios para establecer las variables
     */

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->request = $_REQUEST;
        $parseURL = self::parseURI();
        $this->parsed = $parseURL;
        if ($this->parsed->path->extension) {
            $this->renderAs = $this->parsed->path->extension;
        }
    }

    /**
     * Analiza un URL
     * @param string $uri
     * @return object
     */

    public static function parseURI($uri = NULL)
    {
        if (is_null($uri)) {
            $uri = self::getOriginalURI();
        }
        $parseURL = (object) parse_url($uri);
        if ($parseURL->host) {
            $parseURL->label = array_reverse(explode('.', $parseURL->host));
        }
        $parseURL->path = (object) pathinfo($parseURL->path);
        if ($parseURL->path->dirname == '\\') {
            $parseURL->path->dirname = '/';
        }
        switch ($parseURL->path->filename) {
            case 'index':
            case 'default':
            case '':
                $path = explode('/', $parseURL->path->dirname);
                $last = array_pop($path);
                $parseURL->path->filename = $last;
                $parseURL->path->dirname = implode('/', $path);
                break;
        }
        if (isset($parseURL->query)) {
            parse_str($parseURL->query, $queryParsed);
            $parseURL->query = (object) $queryParsed;
        } else {
            $parseURL->query = NULL;
        }
        return $parseURL;
    }

    /**
     * Destructor de la clase
     */

    public function __destruct()
    {
    }

    /**
     * Regresa la la variable parsed {@see Meakayotl_tools_Request::parsed}
     * como un string JSON
     * @return string
     */

    public function __toString()
    {
        return json_encode($this->parsed);
    }

    /**
     * Regresa el nombre deducido de la clase
     * @return string
     */

    public function asClassName()
    {
        if (!$this->className) {
            $internal = explode('/', $this->internal);
            while ($last = array_pop($internal)) {
                $internal[] = ucfirst(array_pop($internal));
                $className = implode('_', $internal);
                if (class_exists($className)) {
                    $this->className = $className;
                    break;
                } else {
                    $appSubClass = ucfirst(
                            strtolower($GLOBALS['config']['class']));
                    $appSubClass .= '_';
                    $appSubClass .= $className;
                    if (class_exists($appSubClass)) {
                        $this->className = $appSubClass;
                        break;
                    }
                }
            }
        }
        return $this->className;
    }

    /**
     * Analisa los parametros pasados por URL y los completa con datos pasador
     * por request.
     * @param array $urlParams
     */

    private function analyzeParameters()
    {
        if (class_exists($this->className)
                && method_exists($this->className, $this->methodName)) {
            $reflector = new ReflectionMethod($this->className,
                    $this->methodName);
            $methodParameters = $reflector->getParameters();
            $requestParams = $this->request;
            $analyzedParameters = (array) $this->params;
            $countParameters = count($methodParameters);
            for ($i = count($analyzedParameters); $i < $countParameters; $i++) {
                $parameter = $methodParameters[$i];
                $itemName = $parameter->name;
                $value = $requestParams[$itemName];
                $analyzedParameters[] = $value;
            }
            $this->params = $analyzedParameters;
        }
    }

    /**
     * Regresa el nombre deducido del método y establece los parámetros
     * deducidos
     * @uses asClassName
     * @return string
     */

    public function asMethodName()
    {
        if (!$this->methodName) {
            $internal = explode('/', $this->internal);
            $method = explode('_', $this->asClassName());
            $params = array_diff($internal, $method);
            $this->methodName = array_shift($params);
            $this->params = $params;
        }
        $this->analyzeParameters();
        return $this->methodName;
    }

    /**
     * Regresa la clase deducida
     * @uses asClassName
     * @return variant La clase llamada
     * @throws Exception Cuando la clase es nula o no existe.
     */

    public function asClass()
    {
        $className = $this->asClassName();
        if (class_exists($className)) {
            $parentClass = get_parent_class($className);
            if ($parentClass == 'Mekayotl_ApplicationAbstract') {
                return call_user_func($className . '::singleton');
            }
            return new $className();
        }
        //throw new Exception('No class on request', 0);
    }

    /**
     * Regresa el resultado del método llamado de la clase deducida
     * @uses asClass
     * @uses asMethodName
     * @return variant El resultado del método
     * @throws Exception Cuando no puede ser llamado el método.
     */

    public function asMethod()
    {
        //try {
        $class = $this->asClass();
        $method = $this->asMethodName();
        if (!method_exists($class, $method)) {
            return new Exception('No method on class request', 0);
        }
        if (is_array($this->params) && count($this->params) > 0) {
            return call_user_func_array(
                    array(
                            &$class,
                            $method
                    ), $this->params);
        }
        return $class->$method();
        /*} catch ( Exception $e ) {
        //var_dump($e);
        //PHP >5.3 throw new Exception("Can't call the method", 0, $e);
        }*/
    }

    /**
     * Clase de prueba para el request
     *
     * Ej: http://www.example.com/Mekayotl/tools/Request/test/param.html?get=1
     *
     * @param variant $arg1
     */

    public function test($argOne)
    {
        if (MEKAYOTL_DEBUG) {
            $args = func_get_args();
            $return = __METHOD__ . '(' . json_encode($args) . ')' . '['
                    . __FILE__ . ':' . __LINE__ . ']';
        }
        return $return;
    }

    /**#@-*/
    /**#@+
     * @access public
     * @static
     */
    /**
     * Llama la instancia de la clase
     * @return Meakayotl_tools_Request
     */

    public static function singleton()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
            self::getBaseURL();
            $_original = explode('/', self::$_original);
            $base = explode('/', self::$_baseURL);
            $minI = count($_original);
            for ($i = 0; $i < $minI; $i++) {
                if ($_original[$i] == $base[$i]) {
                    unset($_original[$i]);
                } else {
                    break;
                }
            }
            //$internal = pathinfo(implode('/', array_diff($_original, $base)));
            $internal = pathinfo(implode('/', $_original));
            $internal['dirname'] = str_replace('_', '/', $internal['dirname']);
            $internal = str_replace(
                    array(
                            '//',
                            './',
                            '/./'
                    ), '', $internal['dirname'] . '/' . $internal['filename']);
            if ($internal != '') {
                self::$_instance->internal = $internal;
            }
            self::$_instance->asClassName();
            self::$_instance->asMethodName();
        }
        return self::$_instance;
    }

    /**
     * Regresa el URI original
     * @return string
     */

    public static function getOriginalURI()
    {
        if (self::$_original != NULL) {
            return self::$_original;
        }
        $requestUri = 'http://' . $_SERVER["HTTP_HOST"];
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])
                || isset($_SERVER['ORIG_PATH_INFO'])) {
            $requestUri .= self::getFromIis();
        } elseif (isset($_SERVER['REDIRECT_URL'])) {
            // Check if using mod_rewrite
            $requestUri .= $_SERVER['REDIRECT_URL'];
            $requestUri .= (isset($_SERVER['REDIRECT_QUERY_STRING'])) ? '?'
                            . $_SERVER['REDIRECT_QUERY_STRING'] : '';
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri .= $_SERVER['REQUEST_URI'];
        }
        self::$_original = $requestUri;
        return self::$_original;
    }

    /**
     * Obtiene las cabeseras del llamado
     * {@see http://www.php.net/manual/en/function.apache-request-headers.php}
     * @return array
     */

    public static function getHeaders()
    {
        return apache_request_headers();
    }

    /**
     * Obtiene el navegador
     * {@see http://www.php.net/manual/en/function.get-browser.php}
     * @return object
     */

    /*public static function getBrowser()
    {
        //return get_browser();
    }*/

    /**
     * Evalua las variables de servidor para retornal el url invocado
     * del servidor.
     */

    private static function getFromIis()
    {
        $requestUri = '';
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            // check this first so IIS will catch
            $requestUri .= $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            // IIS 5.0, PHP as CGI
            $requestUri .= $_SERVER['ORIG_PATH_INFO']
                    . (!empty($_SERVER['QUERY_STRING'])) ? '?'
                            . $_SERVER['QUERY_STRING'] : '';
        }
        return $requestUri;
    }

    /**
     * Regresa el URL base de la aplicación
     * @return string
     */

    public static function getBaseURL()
    {
        if (self::$_baseURL == NULL) {
            $baseURI = array(
                    'http://' . $_SERVER["HTTP_HOST"]
            );
            $request = self::singleton();
            $uri = explode('/', $request->parsed->path->dirname);
            $uri[] = $request->parsed->path->filename;
            $file = explode(DIRECTORY_SEPARATOR, PUBLIC_PATH);
            $baseURI += array_intersect($uri, $file);
            $baseURI[] = '';
            if ($baseURI[1] == '' && count($baseURI) > 2) {
                unset($baseURI[1]);
            }
            self::$_baseURL = implode('/', $baseURI);
        }
        return self::$_baseURL;
    }

    /**
     * Realiza una llamada a un URL externo y le lo que retorna.
     * @param string $url URL a consultar
     * @param array $postdata Arreglo de datos a enviar
     * @param array $files Archivos a enviar.
     * @throws Exception
     */

    public static function externalRequest($url, $postdata = array(),
            $files = array())
    {
        //$saasLogin = new HttpRequest($apiURL, HttpRequest::METH_POST);
        //$saasLogin -> addPostFields($request -> post);
        //$var = json_decode($saasLogin -> send() -> getBody());
        $data = "";
        $boundary = "---------------------"
                . substr(md5(rand(0, 32000)), 0, 10);
        foreach ($postdata as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $subkey => $subval) {
                    $data .= "--" . $boundary . "\n";
                    $data .= "Content-Disposition: form-data; name=\"" . $key
                            . '[' . $subkey . ']' . "\"\n\n" . $subval . "\n";
                }
            } else {
                $data .= "--" . $boundary . "\n";
                $data .= "Content-Disposition: form-data; name=\"" . $key
                        . "\"\n\n" . $val . "\n";
            }
        }
        if (count($files) > 0) {
            $data .= "--" . $boundary . "\n";
            foreach ($files as $key => $file) {
                $fileContents = file_get_contents($file['tmp_name']);
                $data .= "Content-Disposition: form-data; name=\"" . $key
                        . "\"; filename=\"" . $file['name'] . "\"\n";
                $data .= "Content-Type: image/jpeg\n";
                $data .= "Content-Transfer-Encoding: binary\n\n";
                $data .= $fileContents . "\n";
                $data .= "--" . $boundary . "\n";
            }
        }
        $header = array();
        $header[] = 'From: ' . self::$_baseURL;
        $header[] = 'Content-Type: ' . 'multipart/form-data; boundary='
                . $boundary;
        $params = array(
                'http' => array(
                        'method' => 'POST',
                        'header' => implode("\r\n", $header),
                        'content' => $data
                )
        );
        /* /
        var_dump($url);
        Mekayotl_Debug::stop(urldecode(http_build_query($postdata)));
        /* */
        $ctx = stream_context_create($params);
        $fp = fopen($url, 'rb', FALSE, $ctx);
        $response = stream_get_contents($fp);
        return $response;
    }

    /**
     * Obtiene la url de donde fue el llamado.
     * @return string
     */

    public static function fromUrl()
    {
        $backURL = 'http://' . self::referer();
        if (!$backURL) {
            return false;
        }
        $_baseURL = self::$_baseURL;
        return str_replace($_baseURL, '', $backURL);
    }

    /**
     * De donde se esta llamando ($_SERVER['HTTP_REFERER'] o
     * $_SERVER['HTTP_REFERER'])
     * @return boolean|string
     */

    public static function referer()
    {
        if (!isset($_SERVER['HTTP_FROM']) && !isset($_SERVER['HTTP_REFERER'])) {
            return false;
        }
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = parse_url($_SERVER['HTTP_REFERER']);
            if (!isset($referer['scheme']) && isset($referer['path'])) {
                $referer = parse_url('api://' . $_SERVER['HTTP_REFERER']);
            }
        }
        if ((is_null($referer['host']) || $referer['host'] == '')
                && isset($_SERVER['HTTP_FROM'])) {
            $referer = parse_url($_SERVER['HTTP_FROM']);
            if (!isset($referer['scheme'])) {
                $referer = parse_url('api://' . $_SERVER['HTTP_FROM']);
            }
        }
        return $referer['host'] . $referer['path'];
    }

    /**
     *Redirige el navegador.
     * @param strung $url
     * @param boolean $onBase
     */

    public static function redirect($url, $onBase = TRUE)
    {
        if ($onBase) {
            $url = self::getBaseURL() . $url;
        }
        header('location: ' . $url);
        Mekayotl::end();
    }

    /**#@-*/

}
