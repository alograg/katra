<?php

/*
 * Copyright (c) 2003 Jose Solorzano.  All rights reserved.
 * Redistribution of source must retain this copyright notice.
 *
 * Jose Solorzano (http://jexpert.us) is a software consultant.
 *
 * Contributions by:
 * - Leo West (performance improvements)
 */

define("NODE_TYPE_START", 0);
define("NODE_TYPE_ELEMENT", 1);
define("NODE_TYPE_ENDELEMENT", 2);
define("NODE_TYPE_TEXT", 3);
define("NODE_TYPE_COMMENT", 4);
define("NODE_TYPE_DONE", 5);

/**
 * Class HtmlParser.
 * To use, create an instance of the class passing
 * HTML text. Then invoke parse() until it's FALSE.
 * When parse() returns TRUE, $iNodeType, $iNodeName
 * $iNodeValue and $iNodeAttributes are updated.
 *
 * To create an HtmlParser instance you may also
 * use convenience functions HtmlParser_ForFile
 * and HtmlParser_ForURL.
 */
class HtmlParser
{

    /**
     * Field iNodeType.
     * May be one of the NODE_TYPE_* constants above.
     */

    var $iNodeType;

    /**
     * Field iNodeName.
     * For elements, it's the name of the element.
     */
    var $iNodeName = "";

    /**
     * Field iNodeValue.
     * For text nodes, it's the text.
     */
    var $iNodeValue = "";

    /**
     * Field iNodeAttributes.
     * A string-indexed array containing attribute values
     * of the current node. Indexes are always lowercase.
     */
    var $iNodeAttributes;

    // The following fields should be
    // considered private:

    var $iHtmlText;
    var $iHtmlTextLength;
    var $iHtmlTextIndex = 0;
    var $iHtmlCurrentChar;
    var $BOE_ARRAY;
    var $B_ARRAY;
    var $BOS_ARRAY;

    /**
     * Constructor.
     * Constructs an HtmlParser instance with
     * the HTML text given.
     */
    function HtmlParser($aHtmlText)
    {
        $this->iHtmlText = $aHtmlText;
        $this->iHtmlTextLength = strlen($aHtmlText);
        $this->iNodeAttributes = array();
        $this->setTextIndex(0);

        $this->BOE_ARRAY = array(
                " ",
                "\t",
                "\r",
                "\n",
                "="
        );
        $this->B_ARRAY = array(
                " ",
                "\t",
                "\r",
                "\n"
        );
        $this->BOS_ARRAY = array(
                " ",
                "\t",
                "\r",
                "\n",
                "/"
        );
    }

    /**
     * Method parse.
     * Parses the next node. Returns FALSE only if
     * the end of the HTML text has been reached.
     * Updates values of iNode* fields.
     */
    function parse()
    {
        $text = $this->skipToElement();
        if ($text != "") {
            $this->iNodeType = NODE_TYPE_TEXT;
            $this->iNodeName = "Text";
            $this->iNodeValue = $text;
            return TRUE;
        }
        return $this->readTag();
    }

    function clearAttributes()
    {
        $this->iNodeAttributes = array();
    }

    function readTag()
    {
        if ($this->iCurrentChar != "<") {
            $this->iNodeType = NODE_TYPE_DONE;
            return FALSE;
        }
        $this->clearAttributes();
        $this->skipMaxInTag("<", 1);
        if ($this->iCurrentChar == '/') {
            $this->moveNext();
            $name = $this->skipToBlanksInTag();
            $this->iNodeType = NODE_TYPE_ENDELEMENT;
            $this->iNodeName = $name;
            $this->iNodeValue = "";
            $this->skipEndOfTag();
            return TRUE;
        }
        $name = $this->skipToBlanksOrSlashInTag();
        if (!$this->isValidTagIdentifier($name)) {
            $comment = FALSE;
            if (strpos($name, "!--") === 0) {
                $ppos = strpos($name, "--", 3);
                if (strpos($name, "--", 3) === (strlen($name) - 2)) {
                    $this->iNodeType = NODE_TYPE_COMMENT;
                    $this->iNodeName = "Comment";
                    $this->iNodeValue = "<" . $name . ">";
                    $comment = TRUE;
                } else {
                    $rest = $this->skipToStringInTag("-->");
                    if ($rest != "") {
                        $this->iNodeType = NODE_TYPE_COMMENT;
                        $this->iNodeName = "Comment";
                        $this->iNodeValue = "<" . $name . $rest;
                        $comment = TRUE;
                        // Already skipped end of tag
                        return TRUE;
                    }
                }
            }
            if (!$comment) {
                $this->iNodeType = NODE_TYPE_TEXT;
                $this->iNodeName = "Text";
                $this->iNodeValue = "<" . $name;
                return TRUE;
            }
        } else {
            $this->iNodeType = NODE_TYPE_ELEMENT;
            $this->iNodeValue = "";
            $this->iNodeName = $name;
            while ($this->skipBlanksInTag()) {
                $attrName = $this->skipToBlanksOrEqualsInTag();
                if ($attrName != "" && $attrName != "/") {
                    $this->skipBlanksInTag();
                    if ($this->iCurrentChar == "=") {
                        $this->skipEqualsInTag();
                        $this->skipBlanksInTag();
                        $value = $this->readValueInTag();
                        $this->iNodeAttributes[strtolower($attrName)] = $value;
                    } else {
                        $this->iNodeAttributes[strtolower($attrName)] = "";
                    }
                }
            }
        }
        $this->skipEndOfTag();
        return TRUE;
    }

    function isValidTagIdentifier($name)
    {
        return ereg("^[A-Za-z0-9_\\-]+$", $name);
    }

    function skipBlanksInTag()
    {
        return "" != ($this->skipInTag($this->B_ARRAY));
    }

    function skipToBlanksOrEqualsInTag()
    {
        return $this->skipToInTag($this->BOE_ARRAY);
    }

    function skipToBlanksInTag()
    {
        return $this->skipToInTag($this->B_ARRAY);
    }

    function skipToBlanksOrSlashInTag()
    {
        return $this->skipToInTag($this->BOS_ARRAY);
    }

    function skipEqualsInTag()
    {
        return $this->skipMaxInTag("=", 1);
    }

    function readValueInTag()
    {
        $ch = $this->iCurrentChar;
        $value = "";
        if ($ch == "\"") {
            $this->skipMaxInTag("\"", 1);
            $value = $this->skipToInTag("\"");
            $this->skipMaxInTag("\"", 1);
        } else if ($ch == "'") {
            $this->skipMaxInTag("'", 1);
            $value = $this->skipToInTag("'");
            $this->skipMaxInTag("'", 1);
        } else {
            $value = $this->skipToBlanksInTag();
        }
        return $value;
    }

    function setTextIndex($index)
    {
        $this->iHtmlTextIndex = $index;
        if ($index >= $this->iHtmlTextLength) {
            $this->iCurrentChar = -1;
        } else {
            $this->iCurrentChar = $this->iHtmlText{$index};
        }
    }

    function moveNext()
    {
        if ($this->iHtmlTextIndex < $this->iHtmlTextLength) {
            $this->setTextIndex($this->iHtmlTextIndex + 1);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function skipEndOfTag()
    {
        while (($ch = $this->iCurrentChar) !== -1) {
            if ($ch == ">") {
                $this->moveNext();
                return;
            }
            $this->moveNext();
        }
    }

    function skipInTag($chars)
    {
        $sb = "";
        while (($ch = $this->iCurrentChar) !== -1) {
            if ($ch == ">") {
                return $sb;
            } else {
                $match = FALSE;
                for ($idx = 0; $idx < count($chars); $idx++) {
                    if ($ch == $chars[$idx]) {
                        $match = TRUE;
                        break;
                    }
                }
                if (!$match) {
                    return $sb;
                }
                $sb .= $ch;
                $this->moveNext();
            }
        }
        return $sb;
    }

    function skipMaxInTag($chars, $maxChars)
    {
        $sb = "";
        $count = 0;
        while (($ch = $this->iCurrentChar) !== -1 && $count++ < $maxChars) {
            if ($ch == ">") {
                return $sb;
            } else {
                $match = FALSE;
                for ($idx = 0; $idx < count($chars); $idx++) {
                    if ($ch == $chars[$idx]) {
                        $match = TRUE;
                        break;
                    }
                }
                if (!$match) {
                    return $sb;
                }
                $sb .= $ch;
                $this->moveNext();
            }
        }
        return $sb;
    }

    function skipToInTag($chars)
    {
        $sb = "";
        while (($ch = $this->iCurrentChar) !== -1) {
            $match = $ch == ">";
            if (!$match) {
                for ($idx = 0; $idx < count($chars); $idx++) {
                    if ($ch == $chars[$idx]) {
                        $match = TRUE;
                        break;
                    }
                }
            }
            if ($match) {
                return $sb;
            }
            $sb .= $ch;
            $this->moveNext();
        }
        return $sb;
    }

    function skipToElement()
    {
        $sb = "";
        while (($ch = $this->iCurrentChar) !== -1) {
            if ($ch == "<") {
                return $sb;
            }
            $sb .= $ch;
            $this->moveNext();
        }
        return $sb;
    }

    /**
     * Returns text between current position and $needle,
     * inclusive, or "" if not found. The current index is moved to a point
     * after the location of $needle, or not moved at all
     * if nothing is found.
     */
    function skipToStringInTag($needle)
    {
        $pos = strpos($this->iHtmlText, $needle, $this->iHtmlTextIndex);
        if ($pos === FALSE) {
            return "";
        }
        $top = $pos + strlen($needle);
        $retvalue = substr($this->iHtmlText, $this->iHtmlTextIndex,
                $top - $this->iHtmlTextIndex);
        $this->setTextIndex($top);
        return $retvalue;
    }
}

function HtmlParser_ForFile($fileName)
{
    return HtmlParser_ForURL($fileName);
}

function HtmlParser_ForURL($url)
{
    $fp = fopen($url, "r");
    $content = "";
    while (TRUE) {
        $data = fread($fp, 8192);
        if (strlen($data) == 0) {
            break;
        }
        $content .= $data;
    }
    fclose($fp);
    return new HtmlParser($content);
}

php
?>
