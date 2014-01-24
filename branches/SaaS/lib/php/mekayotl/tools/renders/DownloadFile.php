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
class Mekayotl_tools_renders_DownloadFile
{
    /**
     * Cabeceras a enviar
     * @var array
     */

    protected $_header = array();
    /**
     * Contenido a enviar
     * @var mixed
     */
    private $_content = NULL;
    /**
     * Nombre del archivo a ser guardado.
     * @var string
     */
    public $saveName = NULL;

    /**
     * Constructor
     */

    public function __construct()
    {
        $this->_header = array();
    }

    /**
     * Agrega/sobre escribe una cabecera a ser enviada.
     * @param string $head Cabecera
     * @param string $value Valor
     * @return Mekayotl_tools_renders_DownloadFile this
     */

    public function addHeader($head, $value)
    {
        $this->_header[$head] = $value;
        return $this;
    }

    /**
     * Establece el contenido a ser enviado.
     * @param mixed $content El contenido que tendra el archivo.
     */

    public function setContent($content)
    {
        if (is_file($content)) {
            $this->_content = file_get_contents($content);
        } else {
            $this->_content = $content;
        }
        $this->addHeader('Content-Length', strlen($content));
        return $this;
    }

    /**
     * Establece el nombre del archivo.
     * @param string $title Nombre del archivo
     */

    public function setTitle($title)
    {
        $this->saveName = $title;
        return $this;
    }

    /**
     * Allows a class to decide how it will react when it is converted to
     * a string
     * @return string
     */

    public function __toString()
    {
        $this->addHeader('Content-Description', 'File Transfer');
        $this->addHeader('Content-Transfer-Encoding', 'binary');
        if ($this->saveName) {
            $pathInfo = pathinfo($this->saveName);
            $this->addHeader('Content-Disposition',
                            'attachment; filename=' . $this->saveName);
            $contentType = Mekayotl_tools_Request::$headeContentTypes;
            $this->addHeader('Content-Type',
                            $contentType[$pathInfo['extension']]);
        }
        $this->sendHeaders();
        return $this->_content;
    }

    /**
     * Envía las cabeceras
     * @return Mekayotl_tools_renders_DownloadFile this
     */

    private function sendHeaders()
    {
        foreach ($this->_header as $key => $value) {
            header($key . ': ' . $value);
        }
        return $this;
    }

}
