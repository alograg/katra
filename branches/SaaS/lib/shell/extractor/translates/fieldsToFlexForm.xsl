<?xml version="1.0" encoding="iso-8859-1"?>
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
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<xsl:output method="text" encoding="iso-8859-1"/>
<xsl:template match="/resultset/row/field"><xsl:if test="@name='Field'">&lt;mx:FormItem label="<xsl:value-of select="."/>"&gt;
&lt;mx:TextInput id=&quot;input_<xsl:value-of select="."/>&quot; text=&quot;{data.<xsl:value-of select="."/>}&quot;/&gt;
&lt;/mx:FormItem&gt;
</xsl:if>
</xsl:template>
</xsl:stylesheet>