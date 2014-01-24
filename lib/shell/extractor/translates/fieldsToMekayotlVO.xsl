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
 * Clase repecentativa de registros de la tabla {}
 *
 * Esta tabla se utiliza para {}
 * @author  Henry Isaac Galvez Thuillier &lt;henry@aquainteractive.com.mx&gt;
 * @copyright   Copyright (c) 2012, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package App.[NameSpace]
 * @since   Revisión $id$ $date$
 * @subpackage  Vo
 * @version 1.0.0
 */

/**
 * Clase repecentativa de registros de la tabla {}
 *
 * Esta tabla se utiliza para {}
 * @package App.[NameSpace]
 * @subpackage  Vo
 */
class App_[NameSpace]_vo_<xsl:value-of select="$className"/> extends Mekayotl_database_dal_ValueAbstract {
    <xsl:apply-templates/>
}
</xsl:template>
<xsl:template match="row">
    public $<xsl:value-of select="./field[@name='Field']"/> = <xsl:if test="./field[@name='Default']=''">NULL</xsl:if><xsl:if test="./field[@name='Default']!=''"><xsl:value-of select="./field[@name='Default']"/></xsl:if>;</xsl:template>
</xsl:stylesheet>
