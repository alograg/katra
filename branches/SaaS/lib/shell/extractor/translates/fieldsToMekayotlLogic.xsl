<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet  [
    <!ENTITY nbsp   "&#160;">
    <!ENTITY copy   "&#169;">
    <!ENTITY reg    "&#174;">
    <!ENTITY trade  "&#8482;">
    <!ENTITY mdash  "&#8212;">
    <!ENTITY ldquo  "&#8220;">
    <!ENTITY rdquo  "&#8221;">
    <!ENTITY pound  "&#163;">
    <!ENTITY yen    "&#165;">
    <!ENTITY euro   "&#8364;">
]>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" encoding="UTF-8"/>
<xsl:variable name="tableSQL"><xsl:value-of select="substring-after(/resultset/@statement, '.')"/></xsl:variable>
<xsl:variable name="tableName"><xsl:value-of select="normalize-space($tableSQL)"/></xsl:variable>
<xsl:variable name="className"><xsl:value-of select="concat(translate(substring($tableName, 1, 1), 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'), substring($tableName, 2, string-length($tableName)-1))"/></xsl:variable>
<xsl:template match="/resultset">&lt;?php
/**
 * Lógica de manejo de <xsl:value-of select="$tableName"/>.
 *
 * Métodos de manejo de <xsl:value-of select="$tableName"/>
 * @author  Henry Isaac Galvez Thuillier &lt;henry@aquainteractive.com.mx&gt;
 * @copyright   Copyright (c) 2012, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package App.[NameSpace]
 * @since   Revisión $id$ $date$
 * @subpackage  Logic
 * @version 1.0.0
 */

/**
 * Lógica de manejo de <xsl:value-of select="$tableName"/>.
 *
 * Métodos de manejo de <xsl:value-of select="$tableName"/>
 * @package App.[NameSpace]
 * @subpackage  Logic
 */
class App_[NameSpace]_<xsl:value-of select="$className"/> extends Mekayotl_database_dal_BusinessAbstract {
    protected $_<xsl:value-of select="$tableName"/>;
    public function __construct()
    {
        $this->_<xsl:value-of select="$tableName"/> = new App_[NameSpace]_tables_<xsl:value-of select="$className"/>();
        parent::__construct(Mekayotl::adapterFactory('Database'));
    }
}
</xsl:template>
</xsl:stylesheet>
