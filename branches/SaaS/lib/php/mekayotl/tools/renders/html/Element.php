<?php
//namespace core\tools\renders;
/**
 * Esta clase tiene lo necesario para generar una marcacion de HTML
 *
 * Esta clase toma los datos y los presenta en unformato
 * HTML valido.
 * @author $Author$
 * @copyright Copyright (c) 2008, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package Mekayotl.tools.render
 * @since $Date$
 * @subpackage html
 * @version $Id$
 */

/**
 * Esta clase tiene lo necesario para asignar una presentación en HTML
 *
 * Esta clase toma los datos y los presenta en una configuración HTML
 * parametrisada de templates con una marcación adecuada.
 * @package Mekayotl.tools.render
 * @subpackage html
 */
class Mekayotl_tools_renders_html_Element extends SimpleXMLElement
{
    /**
     *ej: $xml->addProcessingInstruction('xml-stylesheet', 'type="text/xsl"
     * href="xsl/xsl.xsl"');
     */

    public function addProcessingInstruction($name, $value)
    {
        // Create a DomElement from this simpleXML object
        $domSxe = dom_import_simplexml($this->xml);
        // Create a handle to the owner doc of this xml
        $domParent = $domSxe->ownerDocument;
        // Find the topmost element of the domDocument
        $xpath = new DOMXPath($domParent);
        $firstElement = $xpath->evaluate('/*[1]')
                ->item(0);
        // Add the processing instruction before the topmost element
        $pi = $domParent->createProcessingInstruction($name, $value);
        $domParent->insertBefore($pi, $firstElement);
    }

    /**
     * Agrega un elemento al objeto
     * @param Mekayotl_tools_renders_html_Element|SimpleXMLElement $append
     * Objeto a agregar
     */

    public function append($append)
    {
        self::appendTo($this, $append);
    }

    /**
     * Agrega un XML al objeto XML indicado.
     * @param Mekayotl_tools_renders_html_Element|SimpleXMLElement $root Objeto
     * objetivo de la operacion.
     * @param Mekayotl_tools_renders_html_Element|SimpleXMLElement $append
     * Objeto a agregar
     */

    public static function appendTo($root, $append)
    {
        if ($append) {
            if (strlen(trim((string) $append)) == 0) {
                $xml = $root->addChild($append->getName());
                foreach ($append->children() as $child) {
                    self::appendTo($xml, $child);
                }
            } else {
                switch (get_class($append)) {
                    case 'Mekayotl_tools_renders_html_Element':
                        $value = (string) $append;
                        break;
                    case 'SimpleXMLElement':
                        $value = (string) $append;
                        break;
                }
                $xml = $root->addChild($append->getName(), $value);
            }
            foreach ($append->attributes() as $n => $v) {
                $xml->addAttribute($n, $v);
            }
        }
    }

    /**
     * Allows a class to decide how it will react when it is converted to a
     * string
     * @return string
     */

    public function __toString()
    {
        return trim(
                str_replace(
                        array(
                                '<?xml version="1.0"?>',
                                '<0>',
                                '</0>'
                        ), '', $this->asXML()));
    }

    /**
     * Convierte el objeto en una cadena de texto.
     * @return string Cadena con formato XML.
     */

    public function toString()
    {
        return $this->__toString();
    }

    /**
     * Convierte el objeto en una cadena de texto.
     * @return string Cadena con formato XML.
     */

    public function xmlString()
    {
        return parent::__toString();
    }

    /**
     *
     * Enter description here ...
     * @param array $data Arreglo con llaves con arroba para nombre de
     * atributos y sus valores.
     * @return Mekayotl_tools_renders_html_Element This
     */

    public function fill($data = NULL)
    {
        if (is_array($data)) {
            foreach ($data as $elementName => $vValue) {
                if (substr_count($elementName, '@')) {
                    if (is_bool($vValue)) {
                        $vValue = ($vValue) ? 'TRUE' : 'false';
                    }
                    if ($vValue) {
                        $this->addAttribute(substr($elementName, 1),
                                        (string) $vValue);
                    }
                }
            }
        }
        return $this;
    }

}
