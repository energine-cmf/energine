<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="xml"
        version="1.0"
        encoding="utf-8"
        omit-xml-declaration="no"
        media-type="application/rss+xml"
        indent="yes" />

        <xsl:variable name="DOC_PROPS" select="/document/properties/property" />
        <xsl:variable name="ID" select="$DOC_PROPS[@name='ID']" />
        <xsl:variable name="BASE" select="$DOC_PROPS[@name='base']" />
        <xsl:variable name="LANG_ID" select="$DOC_PROPS[@name='lang']" />
        <xsl:variable name="LANG_ABBR" select="$DOC_PROPS[@name='lang']/@abbr" />
    
    <xsl:template match="/">
        <rss version="2.0">
            <xsl:apply-templates />
        </rss>
    </xsl:template>

    <xsl:template match="document">
        <channel>
            <title><xsl:value-of select="$DOC_PROPS[@name='title']"/></title>
            <link><xsl:value-of select="$DOC_PROPS[@name='base']"/></link>
            <description><xsl:value-of select="$DOC_PROPS[@name='channelDescription']"/></description>
            <pubDate><xsl:value-of select="properties/property[@name='pubDate']"/></pubDate>
            <language><xsl:value-of select="properties/property[@name='lang']/@real_abbr"/></language>
            <xsl:apply-templates/>
          </channel>
    </xsl:template>

    <xsl:template match="component | layout | properties" />
</xsl:stylesheet>