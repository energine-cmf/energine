<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:nrgn="http://energine.org"
        xmlns="http://www.w3.org/1999/xhtml"
        version="1.0">

    <xsl:output method="xml"
                version="1.0"
                encoding="utf-8"
                omit-xml-declaration="yes"
                doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
                doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
                indent="yes"/>

    <xsl:include href="../../../../core/modules/share/transformers/include.xslt"/>
    <xsl:include href="../../../../core/modules/user/transformers/include.xslt"/>
    <xsl:include href="../../../../core/modules/calendar/transformers/include.xslt"/>    
    <xsl:include href="../../../../core/modules/apps/transformers/include.xslt"/>
    <xsl:include href="../../../../core/modules/forms/transformers/include.xslt"/>
    
    <xsl:include href="include.xslt"/>

</xsl:stylesheet>
