<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
 <xsl:output method="text" encoding="UTF-8" />
 <xsl:template match="/resultset/row/field">
  <xsl:apply-templates/>
 </xsl:template>
 <!-- standard copy template -->
 <xsl:template match="@*|node()">
  <xsl:copy>
   <xsl:apply-templates select="@*"/>
  </xsl:copy>
 </xsl:template>
</xsl:stylesheet>
