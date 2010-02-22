<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml"
            version="1.0"
            encoding="utf-8"
            omit-xml-declaration="no"
            indent="yes" />
    <xsl:include href="../../core/modules/share/transformers/google_sitemap.xslt" />
    
    <xsl:template match="/">
        <xsl:apply-templates select="//component[@class='GoogleSitemap']"></xsl:apply-templates>
    </xsl:template>
</xsl:stylesheet>