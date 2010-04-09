<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  
    xmlns="http://www.w3.org/1999/xhtml" 
    version="1.0">

    <xsl:output method="xml"
                version="1.0"
                encoding="utf-8"
                omit-xml-declaration="yes"
                doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
                doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
                indent="yes"/>

    <xsl:template name="stylesheets">
        <link href="stylesheets/common.css" rel="stylesheet" type="text/css" media="all" />
		<link href="stylesheets/screen.css" rel="stylesheet" type="text/css" media="Screen, projection" />
 		<link href="stylesheets/print.css" rel="stylesheet" type="text/css" media="print" />
		<link href="stylesheets/handheld.css" rel="stylesheet" type="text/css" media="handheld" />
		<xsl:comment>[if IE]&gt; &lt;style type="text/css" media="Screen, projection"&gt; @import url("stylesheets/ie.css"); /* IE styles import */ &lt;/style&gt; &lt;![endif]</xsl:comment>
    </xsl:template>

	<xsl:include href="../../core/modules/share/transformers/include.xslt"/>
	<xsl:include href="../../core/modules/user/transformers/include.xslt"/>
	<xsl:include href="../../core/modules/image/transformers/include.xslt"/>
	<xsl:include href="../../core/modules/shop/transformers/include.xslt"/>
	<xsl:include href="../../core/modules/hrm/transformers/include.xslt"/>
	<!-- <xsl:include href="../../core/modules/aux/transformers/include.xslt"/> -->

	<xsl:include href="include.xslt"/>

</xsl:stylesheet>
