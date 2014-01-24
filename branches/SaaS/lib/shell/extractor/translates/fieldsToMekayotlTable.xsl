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
 * Clase de acceso a la tabla <xsl:value-of select="$tableName"/>
 *
 * Esta tabla se utiliza para <xsl:value-of select="$tableName"/>
 * @author  Henry Isaac Galvez Thuillier &lt;henry@aquainteractive.com.mx&gt;
 * @copyright   Copyright (c) 2012, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package App.[NameSpace]
 * @since   Revisión $id$ $date$
 * @subpackage  Tables
 * @version 1.0.0
 */

/**
 * Clase de acceso a la tabla <xsl:value-of select="$tableName"/>
 *
 * Esta tabla se utiliza para <xsl:value-of select="$tableName"/>
 * @package App.[NameSpace]
 * @subpackage  Tables
 */
class App_[NameSpace]_tables_<xsl:value-of select="$className"/> extends Mekayotl_database_dal_AccessAbstract {
    public function __construct() {
        $this -> _table = '<xsl:value-of select="$tableName"/>';
        $this -> _baseClass = 'App_[NameSpace]_vo_<xsl:value-of select="$className"/>';
        $this -> _keys = array(<xsl:apply-templates/>);
        parent::__construct();
    }
}
</xsl:template>
<xsl:template match="row"><xsl:if test="./field[@name='Key']='PRI'">'<xsl:value-of select="./field[@name='Field']"/>'<xsl:if test="position()!=last()">, </xsl:if></xsl:if></xsl:template>
</xsl:stylesheet>
