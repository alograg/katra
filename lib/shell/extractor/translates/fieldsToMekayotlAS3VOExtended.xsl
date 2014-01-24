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
<xsl:template match="/resultset">package com.aquainteractive.[NameSpace].vo
{
/**
 * Clase repecentativa de registros de la tabla <xsl:value-of select="$tableName"/>
 *
 * Esta tabla se utiliza para <xsl:value-of select="$tableName"/>
 * @author  Henry Isaac Galvez Thuillier &lt;henry@aquainteractive.com.mx&gt;
 * @copyright   Copyright (c) 2012, {@link http://www.aquainteractive.com Aqua
 * Interactive}
 * @package com.aquainteractive.[NameSpace]
 * @since   Revisión $id$ $date$
 * @subpackage vo
 * @version 1.0.0
 */

    import com.aquainteractive.mekayotl.MekayotlArray;
    import com.aquainteractive.[NameSpace].Calls;
    import com.aquainteractive.[NameSpace].vo.row.<xsl:value-of select="$className"/>;

    public class <xsl:value-of select="$className"/>Extended extends <xsl:value-of select="$className"/>
    {
        /**
         * Constantes para llamadas a AMF
         * Ej. public static const [CLASS]_[METOTH]:String = 'App_[CLASS].[METOTH]';
         **/

        /**
         * Propiedades extras
         * Ej. [Bindable] public var relacionados:MekayotlArray;
         **/

        public function <xsl:value-of select="$className"/>Extended()
        {
            //TODO: implement function
            super();
        }
    }
}
</xsl:template>
</xsl:stylesheet>
