<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <xsl:variable name="DOC_PROPS" select="/document/properties/property" />

    <xsl:variable name="ID" select="$DOC_PROPS[@name='ID']" />
    <xsl:variable name="BASE" select="$DOC_PROPS[@name='base']" />
    <xsl:variable name="LANG_ABBR" select="$DOC_PROPS[@name='lang']/@abbr" />

    <xsl:template match="component[@class='GoogleSitemap']">
        <urlset>
            <xsl:attribute name="xmlns">http://www.sitemaps.org/schemas/sitemap/0.9</xsl:attribute>
            <xsl:call-template name="TREE_BUILDER"></xsl:call-template>
        </urlset>
    </xsl:template>
    
    <xsl:template name="TREE_BUILDER">
        <xsl:for-each select="recordset/record">
                <url>
                    <loc>
                        <xsl:value-of select="$BASE"/>
                        <xsl:value-of select="$LANG_ABBR"/>
                        <xsl:value-of select="field[@name='Segment']"/>
                    </loc>
                </url>
                <xsl:if test="recordset">
                    <xsl:call-template name="TREE_BUILDER"></xsl:call-template>
                </xsl:if>
            </xsl:for-each>
    </xsl:template>
    
</xsl:stylesheet>
