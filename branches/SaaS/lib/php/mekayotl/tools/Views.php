<?php
//namespace core\tools;
/**
 * Manejador de vistas
 *
 * Esta clase maneja la representación gráfica de una clase o método llamado.
 * @author $Author$
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl
 * @since $Date$
 * @subpackage tools
 * @version $Id$
 */

/**
 * Manejador de vistas
 *
 * Esta clase maneja la representación gráfica de una clase o método llamado.
 * @package Mekayotl
 * @subpackage tools
 * @uses Mekayotl_tools_renders_Html
 */
class Mekayotl_tools_Views extends Mekayotl_tools_renders_Html
{
    /**#@+
     * @access public
     */
    /**
     * Si la plantilla debe generar cache
     * @var boolean
     */

    public $cached = FALSE;
    /**
     * Duración en minutos del cache
     * @var integer
     */
    public $cacheTime = 30;
    /**
     * El archivo de la vista
     * @var string
     */
    public $viewPath;
    /**
     * El archivo de cache
     * @var string
     */
    public $cachedFilePath;
    /**
     * El directorio de cache
     * @var string
     */
    public $cachedBasePath = PUBLIC_PATH;
    /**
     * Si solo renderea el cuerpo del body o con todo el HTML
     * @var boolean
     */
    public $onlyBody = FALSE;
    /**
     * Define si se utiliza los metodos de control de bufer de salida.
     * @var boolean
     */
    public $useOutputBuffering = TRUE;
    /**
     * Construye el objeto
     */

    public function __construct($duplicate = NULL)
    {
        parent::__construct();
        $this->addHttpMeta('imagetoolbar', 'no');
    }

    /**
     * Allows a class to decide how it will react when it is converted to
     * a string
     * @return string
     */

    public function __toString()
    {
        if ($this->onlyBody) {
            return $this->generateDisplay();
        }
        return $this->diplay();
    }

    /**
     * Muestra la vista
     * @param boolean $cached Si debe ser tomado en cuenta el cache. Default:
     * $this->cached
     * @param boolean $override Si sobre escribe el cache
     * @return string El contenido de la vista
     */

    public function diplay($cached = NULL, $override = TRUE)
    {
        if (is_null($cached)) {
            $cached = $this->cached;
        }
        if ($cached) {
            $cacheFile = $this->cachedBasePath . '/' . $this->cachedFilePath;
            if (is_file($cacheFile)) {
                $life = filemtime($cacheFile) + ($this->cacheTime * 60);
                if ($life > time()) {
                    return $this->displayCache($cacheFile);
                }
            }
            $toCache = $this->generateDisplay();
            Mekayotl::generatePath($cacheFile);
            file_put_contents($cacheFile, $toCache);
        } else {
            $this->generateDisplay();
        }
        $return = parent::__toString();
        $this->setHeader('Content-Length', strlen($return));
        return $return;
    }

    /**#@-*/
    /**#@+
     * @access protected
     */
    /**
     * Genera el contenido del cuerpo
     * @return string
     */

    protected function generateDisplay()
    {
        if ($this->useOutputBuffering) {
            ob_start();
        }
        $this->addInherit();
        if (is_file($this->viewPath)
                && !$this->_body
                        ->string) {
            extract($this->data);
            extract($this->_body
                            ->sections, EXTR_PREFIX_SAME, 'section');
            require $this->viewPath;
            $this->setBody(ob_get_contents());
        } elseif (!$this->_body
                ->string) {
            $this->setBody(
                            'Plantilla no disponible <!-- ' . $this->viewPath
                                    . '-->');
        }
        if ($this->useOutputBuffering) {
            ob_end_clean();
        }
        if ($this->onlyBody) {
            return $this->getBodyString();
        }
        return parent::__toString();
    }

    /**#@-*/
    /**#@+
     * @access  protected
     */
    /**
     * Agrega las variables a las secciones
     *  @access  private
     * @return  string
     */

    private function addInherit()
    {
        $this->addIherintData();
        $this->addInherintSecction();
    }

    private function addIherintData()
    {
        $dataVars = array();
        foreach ($this->data as $nom => $itm) {
            if (!$itm instanceof Mekayotl_tools_Views)
                $dataVars[$nom] = $itm;
        }
        foreach ($this->_body
                ->sections as $itm) {
            if ($itm instanceof Mekayotl_tools_Views) {
                foreach ($dataVars as $noVars => $var) {
                    $ret = 'setData' . ucfirst($noVars);
                    $itm->$ret($var);
                }
            }
        }

    }

    private function addInherintSecction()
    {
        $secVars = array();
        foreach ($this->_body
                ->sections as $nom => $itm) {
            if (!$itm instanceof Mekayotl_tools_Views)
                $secVars[$nom] = $itm;
        }
        foreach ($this->_body
                ->sections as $itm) {
            if ($itm instanceof Mekayotl_tools_Views) {
                foreach ($secVars as $noVars => $var) {
                    $ret = 'setSection' . ucfirst($noVars);
                    $itm->$ret($var);
                }
            }
        }
    }

    /**
     * Agarra el contenido del archivo de cache
     * @param string $file La ubicación del cache
     * @return string El contenido del archivo de cache
     */

    protected function displayCache($file)
    {
        $this->addHeader('Content-Length', filesize($file));
        return file_get_contents($file);
    }

    /**#@-*/

}
