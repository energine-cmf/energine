<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
    <xsl:template match="component[@class='Sape']">
        <xsl:value-of disable-output-escaping="yes" select="recordset/record/field[@name='links']"/>
    </xsl:template>
</xsl:stylesheet>