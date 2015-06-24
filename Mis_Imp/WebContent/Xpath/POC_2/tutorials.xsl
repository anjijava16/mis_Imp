<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="/">
<xsl:apply-templates/>
</xsl:template>

<xsl:template match="tutorial">
<xsl:for-each select="url">
<font color='red'><xsl:value-of select="."/><xsl:element name="br"/></font>
</xsl:for-each>

<xsl:for-each select="name">
<xsl:value-of select="."></xsl:value-of>
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>