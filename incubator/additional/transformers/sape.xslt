<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:template match="component[@class='Sape']">
        <xsl:value-of disable-output-escaping="yes" select="recordset/record/field[@name='Links']"/>
    </xsl:template>
</xsl:stylesheet>