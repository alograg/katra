<?php
//namespace core\tools\renders;
/**
 * Esta clase tiene lo necesario para asignar una presentación en HTML
 *
 * Esta clase toma los datos y los presenta en una configuración HTML
 * parametrisada de templates con una marcación adecuada.
 * @author $Author$
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl.tools
 * @since $Date$
 * @subpackage renders
 * @version $Id$
 */

/**
 * Esta clase tiene lo necesario para asignar una presentación en HTML
 *
 * Esta clase toma los datos y los presenta en una configuración HTML
 * parametrisada de templates con una marcación adecuada.
 * @package Mekayotl.tools
 * @subpackage renders
 */
class Mekayotl_tools_renders_Html
{
    /**
     * Constante con el cabezal de tipo de documento HTML 4.01
     * @var string
     */
    const DOCTYPE_HTML4 = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
    /**
     * Constante con el cabezal de tipo de documento HTML 5
     * @var string
     */
    const DOCTYPE_HTML5 = '<!DOCTYPE html>';
    /**
     * Constante con el cabezal de tipo de documento XHTML 1.0
     * @var string
     */
    const DOCTYPE_XHTML = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    /**
     * Cabeceras
     * @var array
     */

    protected $_header = NULL;
    /**
     * Cabecera HTML
     * @var object
     */
    protected $_head = NULL;
    /**
     * Texto y contenido HTML
     * @var object
     */
    protected $_body = NULL;
    /**
     * Datos extras para rellenar en el documento.
     * @var object
     */
    public $data = array();
    /**
     * Tipo de documento que se establecerá.
     * @var string
     */
    public $renderDocType = 'html4';
    /**
     * Id del tag body.
     * @var object
     */
    private $_bodyId = '';
    /**
     * Clases del body.
     * @var object
     */
    private $_bodyClass = '';
    /**
     * Clases del body array.
     * @var object
     */
    private $_bodyClassses = array();
    /**
     * Constructor
     */

    public function __construct()
    {
        $date = new DateTime();
        $date->modify('+1 day');
        //$date -> add(new DateInterval('P1D'));
        $this->_header = array(
                'Expires' => $date->format('r')
        );
        $this->_head = (object) array(
                'httpmeta' => array(),
                'title' => '',
                'namemeta' => array(),
                'base' => array(
                        '@href' => '/'
                ),
                'link' => array(
                        array(
                                '@rel' => 'apple-touch-icon',
                                '@href' => 'apple-touch-icon.png'
                        ),
                        array(
                                '@rel' => 'shortcut icon',
                                '@href' => 'favicon.ico',
                                '@type' => 'image/x-icon'
                        ),
                ),
                'iftag' => array(),
                'styles' => array(),
                'scripts' => array()
        );
        $this->_body = (object) array(
                'string' => '',
                'sections' => array()
        );
    }

    /**
     * Agrega una etiqueta meta equivalente a una cabecera http
     * @param string $equiv Equivalencia http
     * @param string $content Valor
     */

    public function addHttpMeta($equiv, $content)
    {
        $this->addHeadHttpMeta(
                        array(
                                '@http-equiv' => $equiv,
                                '@content' => $content
                        ));
    }

    /**
     * Agrega una etiqueta meta de tipo descriptivo de documento.
     * @param string $name Nombre
     * @param string $content Valor
     */

    public function addNameMeta($name, $content)
    {
        $this->addHeadNameMeta(
                        array(
                                '@name' => $name,
                                '@content' => $content
                        ));
    }

    public function addIf($condition, $inner)
    {
        /*
        <!--[if lt IE 9]>
        <![endif]-->
         */
        $ifTag = array(
                '<!--[if ' . $condition . ']>'
        );
        if (is_array($inner)) {
            foreach ($inner as $tag) {
                $ifTag[] = $tag;
            }
        } else {
            $ifTag[] = $inner;
        }
        $ifTag[] = '<![endif]-->';
        $this->_head
                ->iftag[] = implode('', $ifTag);
    }

    /**
     * Establece la ubicacion base para los enlaces.
     * @param string $baseURL
     */

    public function setBase($baseURL = NULL)
    {
        if ($baseURL) {
            $this->_head
                    ->base = array(
                    '@href' => $baseURL
            );
        }
        return $this->_head
                ->base;
    }

    private function doGet($block, $section)
    {
        $get = NULL;
        $headProperties = array_keys((array) $this->_head);
        if ($block == 'head' && in_array($section, $headProperties)) {
            $get = $this->_head
                    ->$section;
        } elseif ($block == 'head' && $section == 'er') {
            $get = $this->_header;
        } elseif ($block == 'title') {
            $get = $this->_head
                    ->title;
        } elseif ($block == 'body' && $block == $section) {
            $get = $this->getBodyString();
        } elseif (in_array($block,
                array(
                        'body',
                        'section'
                ))) {
            $get = $this->_body
                    ->sections[$section];
        } else {
            $get = $this->data[$section];
        }
        return $get;
    }

    private function doSet($block, $section, $arguments)
    {
        $headProperties = array_keys((array) $this->_head);
        if ($block == 'head' && in_array($section, $headProperties)) {
            $this->_head
                    ->$section = $arguments[0];
        } elseif ($block == 'head' && $section == 'er') {
            $this->_header = $arguments[0];
        } elseif ($block == 'title') {
            $this->_head
                    ->title = (string) $arguments[0];
        } elseif ($block == 'body' && $block == $section) {
            $this->_body
                    ->string = (string) $arguments[0];
        } elseif (in_array($block,
                array(
                        'body',
                        'section'
                ))) {
            $this->_body
                    ->sections[$section] = $arguments[0];
        } else {
            $this->data[$section] = $arguments[0];
        }
    }

    private function doPend($arguments, $action, $block, $section)
    {
        $headProperties = array_keys((array) $this->_head);
        $data = array(
                $arguments[0]
        );
        if (count($arguments) == 2) {
            $data = array(
                    $arguments[0] => $arguments[1]
            );
        }
        if (is_array($arguments[0])) {
            $data = $arguments[0];
        }
        if ($block == 'head' && in_array($section, $headProperties)) {
            $toTarget = Mekayotl_tools_Utils::grab(
                    $this->_head
                            ->$section, $data, $action);
            $this->_head
                    ->$section = $toTarget;
        } elseif ($block == 'head' && $section == 'er') {
            $this->_header[$arguments[0]] = $arguments[1];
        } elseif (in_array($block,
                array(
                        'body',
                        'section'
                ))) {
            $this->_body
                    ->sections = Mekayotl_tools_Utils::grab(
                    $this->_body
                            ->sections, $data, $action);
        } else {
            $this->data = Mekayotl_tools_Utils::grab($this->data, $data,
                    $action);
        }
    }

    /**
     * Triggered when invoking inaccessible methods in an object context
     * @param string $methodName Nombre del método
     * @param array $arguments Argumentos pasados
     * @return variant El valor que regrese el método invocado
     */

    public function __call($methodName, array $arguments)
    {
        $regexp = '/^(?P<action>call|get|set|(ap|pre)pend|add)'
                . '(?P<block>Header|Head|Title|Body|Section|Data)'
                . '(?P<section>\w+)$/';
        if (!preg_match($regexp, $methodName, $matches)) {
            $regexp = '/^(?P<action>call|get|set|(ap|pre)pend)'
                    . '(?P<block>Header|Title|Body|Section|Data|\w+)$/';
            preg_match($regexp, $methodName, $matches);
        }
        if (!($matches['action'] && $matches['block'])) {
            return FALSE;
        }
        $action = strtolower($matches['action']);
        $block = strtolower($matches['block']);
        $section = strtolower(
                (isset($matches['section'])) ? $matches['section'] : $block);
        $headProperties = array_keys((array) $this->_head);
        switch ($action) {
            case 'call':
                if (method_exists($this, $methodName)) {
                    return call_user_func_array(
                            array(
                                    $this,
                                    $methodName
                            ), $arguments);
                }
                return NULL;
                break;
            case 'get':
                return $this->doGet($block, $section);
                break;
            case 'set':
                $this->doSet($block, $section, $arguments);
                break;
            case 'add':
                $action = 'append';
            case 'append':
            case 'prepend':
                $this->doPend($arguments, $action, $block, $section);
                break;
            default:
                $this->data[$section] = $arguments[0];
                break;
        }
        return $this;
    }

    /**
     * Este método remplaza el contenido del body con datos de sección y
     * datos del render
     * @access public
     */

    public function getBodyString()
    {
        return $this->_body
                ->string;
    }

    /**
     * Allows a class to decide how it will react when it is converted to a
     * string
     * @return string
     */

    public function __toString()
    {
        $return = constant('self::DOCTYPE_' . strtoupper($this->renderDocType));
        $htmlObject = array();
        $htmlObject['head'] = (array) $this->_head;
        switch ($this->renderDocType) {
            case 'xhtml':
                $htmlObject['@xmlns'] = 'http://www.w3.org/1999/xhtml';
                $htmlObject['@lang'] = 'es';
                array_unshift($htmlObject['head']['httpmeta'],
                        array(
                                '@http-equiv' => 'Content-Type',
                                '@content' => 'text/html; charset=utf-8'
                        ));
                break;
            case 'html5':
                $htmlObject['@lang'] = 'es';
                array_unshift($htmlObject['head']['httpmeta'],
                        array(
                                '@charset' => 'utf-8'
                        ));
                break;
            case 'html4':
            default:
                array_unshift($htmlObject['head']['httpmeta'],
                        array(
                                '@http-equiv' => 'Content-Type',
                                '@content' => 'text/html; charset=utf-8'
                        ));
        }
        $htmlObject['body'] = '';
        $htmlObject['head']['scripts'] = array();
        $htmlObject['head']['styles'] = array();
        $htmlObject['head']['iftag'] = array();
        $styles = array();
        foreach ($this->_head
                ->styles as $style) {
            if (is_array($style)) {
                $style['@type'] = 'text/css';
                $style['@rel'] = 'stylesheet';
                $htmlObject['head']['link'][] = $style;
            } else {
                $xmlTag = new SimpleXMLElement('<style/>');
                $xmlTag->addAttribute('type', "text/css");
                $styles[] = Mekayotl_tools_utils_Conversion::toXML($style,
                        $xmlTag, TRUE);
            }
        }
        $scripts = array();
        foreach ($this->_head
                ->scripts as $script) {
            if (!is_array($script) && is_string($script)) {
                $script['emptyContent'] = $script;
            } else {
                $script['emptyContent'] = '';
            }
            if (!isset($script['@type'])) {
                $script['@type'] = 'text/javascript';
            }
            $remove = array(
                    'emptyContent'
            );
            $scripts[] = Mekayotl_tools_utils_Conversion::toXML($script,
                    'script', TRUE, $remove);
        }
        $html = Mekayotl_tools_utils_Conversion::toXML($htmlObject, 'html',
                TRUE);
        $html = str_replace(
                array(
                        '<styles/>',
                        '<scripts/>',
                        '<iftag/>',
                        'httpmeta',
                        'namemeta'
                ),
                array(
                        implode('', $styles),
                        implode('', $scripts),
                        implode('',
                                $this->_head
                                        ->iftag),
                        'meta',
                        'meta'
                ), $html);
        $html = str_replace('<meta/>', '', $html);
        if ($this->renderDocType == 'html4') {
            $html = str_replace('/>', '>', $html);
        }
        $return .= str_replace('<body></body>',
                '<body' . $this->_bodyId . $this->_bodyClass . '>'
                        . $this->getBodyString() . '</body>', $html);
        $this->sendHeaders();
        return $return;
    }

    /**
     * Envía las cabeceras
     * @return Mekayotl_tools_renders_DownloadFile this
     */

    public function sendHeaders()
    {
        foreach ($this->_header as $key => $value) {
            header($key . ': ' . $value);
        }
        return $this;
    }

    /**
     * Crea un objeto de etiqueta
     * @param string $name Nombre de la etiqueta
     * @param string|array $definition Arreglo con definición de propiedades y
     * contenido o cadena de texto con el contenido.
     */

    public static function makeTag($name, $definition = NULL)
    {
        $tag = '<' . $name . '/>';
        if (isset($definition[0]) || is_string($definition)) {
            $tag = str_replace('/>',
                    '>'
                            . ((is_string($definition)) ? $definition
                                    : $definition[0]) . '</' . $name . '>',
                    $tag);
        }
        $return = new Mekayotl_tools_renders_html_Element($tag);
        $return->fill($definition);
        return $return;
    }

    /**
     * Asigna un id al tag de body
     * @param   string  $idName   Nombre del id
     */

    public function setBodyId($idName)
    {
        if ($idName != '') {
            $this->_bodyId = ' id="' . $idName . '"';
        }
    }

    /**
     * Agrega una clase al body
     * @param   string  $idName   Nombre del id
     */

    public function addBodyClass($sClassName)
    {
        if ($sClassName != '') {
            $this->_bodyClassses[] = $sClassName;
            $this->_bodyClass = ' class="' . implode(' ', $this->aBodyClass)
                    . '"';
        }
    }

    public function setFavicon($url)
    {
        $this->_head
                ->link[1]['@href'] = $url;
        return $this;
    }

    public function setAppleTouch($url)
    {
        $this->_head
                ->link[0]['@href'] = $url;
        return $this;
    }

}
