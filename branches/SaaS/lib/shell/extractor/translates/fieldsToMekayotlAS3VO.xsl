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
<xsl:template match="/resultset">package com.aquainteractive.[NameSpace].vo.row
{
/**
 * Clase repecentativa de registros de la tabla <xsl:value-of select="$tableName"/>
 *
 * Esta tabla se utiliza para <xsl:value-of select="$tableName"/>
 * @author  Henry Isaac Galvez Thuillier &lt;henry@aquainteractive.com.mx&gt;
 * @copyright   Copyright (c) 2012, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package com.aquainteractive.[NameSpace].vo
 * @since   Revisión $id$ $date$
 * @subpackage row
 * @version 1.0.0
 */
    import com.aquainteractive.mekayotl.RowValueObject;

    [RemoteClass(alias="App_[NameSpace]_vo_<xsl:value-of select="$className"/>")]
    public class <xsl:value-of select="$className"/> extends RowValueObject
    {

<xsl:apply-templates/>

        public function <xsl:value-of select="$className"/>()
        {
            //TODO: implement function
            super();
        }
    }
}
</xsl:template>
<xsl:template match="row">
        [Bindable]
        public var <xsl:value-of select="./field[@name='Field']"/>:String = <xsl:if test="./field[@name='Default']=''">null</xsl:if><xsl:if test="./field[@name='Default']!=''"><xsl:value-of select="./field[@name='Default']"/></xsl:if>;</xsl:template>
</xsl:stylesheet>
