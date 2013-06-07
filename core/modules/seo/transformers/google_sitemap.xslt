<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        version="1.0">

    <xsl:output method="xml"
            version="1.0"
            encoding="utf-8"
            omit-xml-declaration="no"
            indent="no" />
    
    <xsl:variable name="LANG" select="/document/properties/property[@name='lang']" />
    <xsl:variable name="ID" select="/document/properties/property[@name='ID']" />

    <xsl:template match="/">
        <xsl:apply-templates select="//component[@class='GoogleSitemap']"/>
    </xsl:template>

    <xsl:template match="component[@class='GoogleSitemap']">
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
            <xsl:call-template name="TREE_BUILDER"/>
        </urlset>
    </xsl:template>
    
    <xsl:template name="TREE_BUILDER">
        <xsl:for-each select="recordset/record">
            <xsl:variable name="RECORD" select="." />
            <xsl:for-each select="/document/layout/component[@class='LangSwitcher']/recordset/record">
                <xsl:if test="$RECORD/field[@name='Id']!=$ID">
                    <url>
                        <loc>
                            <xsl:value-of select="$RECORD/field[@name='Site']"/>
                            <xsl:if test="$LANG/@abbr != field[@name='lang_abbr']">
                            <xsl:value-of select="field[@name='lang_abbr']"/>/</xsl:if><xsl:value-of select="$RECORD/field[@name='Segment']"/>
                        </loc>
                    </url>
                </xsl:if>
                </xsl:for-each>
                <xsl:if test="recordset">
                    <xsl:call-template name="TREE_BUILDER"/>
                </xsl:if>
        </xsl:for-each>
    </xsl:template>
    
</xsl:stylesheet>
