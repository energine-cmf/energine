<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="text"
            version="1.0"
            encoding="utf-8"
            omit-xml-declaration="no"
            indent="no" />

    <xsl:template match="/">
        <xsl:apply-templates select="//component[@class='RobotsTxt']"></xsl:apply-templates>
    </xsl:template>

    <xsl:template match="component[@class='RobotsTxt']">
<xsl:text>
User-agent: *
Allow: /
</xsl:text>
            <xsl:call-template name="SITEMAP_PATH"></xsl:call-template>
    </xsl:template>

    <xsl:template name="SITEMAP_PATH">
        <xsl:for-each select="recordset/record">
            <xsl:variable name="RECORD" select="." />
<xsl:text>Sitemap: </xsl:text> <xsl:value-of select="$RECORD/field[@name='path']"/>
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>
