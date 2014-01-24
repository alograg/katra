<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="text" encoding="UTF-8" />
    <xsl:key name="kElsByGroup" match="field[@name='account']" use="." />
<xsl:template match="resultset">-- SQL completo
<xsl:apply-templates /></xsl:template>
<xsl:template match="row"><xsl:apply-templates
            select="field[generate-id() = generate-id(key('kElsByGroup', ../field[@name='account'])[1])]" /></xsl:template>
<xsl:template match="field"><xsl:variable name="currentGroup" select="../field[@name='account']" /><xsl:variable name="currentNode" select=".."/>-- SQL para cuenta <xsl:value-of select="$currentGroup" />
INSERT INTO invoice (createdOn, account) VALUES ('<xsl:value-of select="../field[@name='creationOn']" />', <xsl:value-of select="$currentGroup" />);
SET @LAST_INVOICE=LAST_INSERT_ID();
INSERT INTO concept (invoice, description, price) VALUES<xsl:for-each select="key('kElsByGroup', $currentGroup)">
    (@LAST_INVOICE, '<xsl:value-of select="../field[@name='descriotion']"/>', <xsl:value-of select="../field[@name='price']"/>)<xsl:if test="position()!=last()">,</xsl:if>
</xsl:for-each>;</xsl:template>
</xsl:stylesheet>
