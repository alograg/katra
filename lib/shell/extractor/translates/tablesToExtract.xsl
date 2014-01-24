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
<xsl:template match="/resultset/row/field"><xsl:if test="@name='Name'">.\bin\mysql -h %1 -D %2 -u %3 --password=%4 --character-sets-dir=utf8 --default-character-set=utf8 -X -e "SHOW COLUMNS FROM %2.<xsl:value-of select="."/>" &gt; .\%1\%2\<xsl:value-of select="."/>_fields.xml
</xsl:if></xsl:template></xsl:stylesheet>
