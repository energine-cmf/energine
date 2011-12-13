<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml"
            version="1.0"
            encoding="utf-8"
            omit-xml-declaration="no"
            indent="no" />
    
    <xsl:variable name="LANG" select="/document/properties/property[@name='lang']" />
    <xsl:variable name="ID" select="/document/properties/property[@name='ID']" />

    <xsl:template match="/">
        <xsl:apply-templates select="//component[@class='GoogleVideoSitemap']"></xsl:apply-templates>
    </xsl:template>

    <xsl:template match="component[@class='GoogleVideoSitemap']">
        <sitemapindex>
            <xsl:attribute name="xmlns">http://www.sitemaps.org/schemas/sitemap/0.9</xsl:attribute>
            <xsl:call-template name="SITEMAP_PATH"></xsl:call-template>
        </sitemapindex>
    </xsl:template>

    <xsl:template name="SITEMAP_PATH">
        <xsl:for-each select="recordset/record">
            <xsl:variable name="RECORD" select="." />
                    <sitemap>
                        <loc>
                            <xsl:value-of select="$RECORD/field[@name='path']"/>
                        </loc>
                    </sitemap>
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>
