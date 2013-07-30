<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml"
                version="1.0"
                encoding="utf-8"
                omit-xml-declaration="yes"
                doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
                doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
                indent="yes"/>
    <xsl:variable name="DOC_PROPS" select="/document/properties/property"/>
    <xsl:variable name="BASE" select="/document/properties/property[@name='base']"/>
    <xsl:variable name="LANG_ABBR" select="/document/properties/property[@name='lang']/@abbr"/>
    <xsl:variable name="TRANSLATION" select="/document/translations/translation"/>
    <xsl:variable name="STATIC_URL"><xsl:value-of select="$BASE/@static"/></xsl:variable>
    <xsl:variable name="ROUTER_URL"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/>programs/p/</xsl:variable>
    <xsl:variable name="MEDIA_URL"><xsl:value-of select="$BASE/@media"/></xsl:variable>
    <xsl:variable name="RESIZER_URL"><xsl:value-of select="$BASE/@resizer"/></xsl:variable>
    <xsl:variable name="FOLDER" select="$DOC_PROPS[@name='base']/@folder"/>
    <xsl:variable name="MAIN_SITE" select="$DOC_PROPS[@name='base']/@default"/>
    <xsl:template match="/">
        <xsl:apply-templates select="document/container"/>
        <xsl:apply-templates select="document/component"/>
    </xsl:template>

</xsl:stylesheet>