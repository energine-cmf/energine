<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        version="1.0">

    <xsl:output method="xml"
            version="1.0"
            encoding="utf-8"
            omit-xml-declaration="no"
            indent="yes" />
    
    <xsl:template match="/">
        <xsl:apply-templates select="//component[@class='GoogleSitemap']"/>
    </xsl:template>

    <xsl:template match="component[@class='GoogleSitemap']">
        <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
            <xsl:apply-templates select="recordset"/>
        </sitemapindex>
    </xsl:template>

    <xsl:template match="component[@class='GoogleSitemap']/recordset">
        <xsl:apply-templates select="record"/>
    </xsl:template>

    <xsl:template match="component[@class='GoogleSitemap']/recordset/record">
            <sitemap>
                <loc>
                    <xsl:value-of select="field[@name='path']"/>
                </loc>
            </sitemap>
    </xsl:template>

</xsl:stylesheet>
