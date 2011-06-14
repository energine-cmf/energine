<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="html"
                version="1.0"
                encoding="utf-8"
                omit-xml-declaration="yes"
                doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
                doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
                indent="yes" />

    <xsl:variable name="BASE" select="/errors/@base" />
    <xsl:variable name="LANG_ABBR" select="'ru'" />
    <xsl:variable name="IN_DEBUG_MODE"><xsl:value-of select="/errors/@debug"/></xsl:variable>

    <xsl:template match="/errors">
        <html>
        	<head>
                <title>Errors</title>
        		<base href="{$BASE}" />
                <link href="stylesheets/common.css" rel="stylesheet" type="text/css" media="all" />
				<link href="stylesheets/screen.css" rel="stylesheet" type="text/css" media="Screen" />
				<link href="stylesheets/print.css" rel="stylesheet" type="text/css" media="print" />
				<link href="stylesheets/handheld.css" rel="stylesheet" type="text/css" media="handheld" />
                <META HTTP-EQUIV="refresh" CONTENT="10;URL={$BASE}"/>
        	</head>
        	<body>
        	    <div id="container">
            	    <div id="header">
            		    <h1>Energine</h1>
            	    </div>
					<div id="main">
						<div id="content">
							<xsl:apply-templates select="error" />
						</div>
						<div id="sidebar">
							<ul style="margin-top: 10px;">
								<li><a href="{$BASE}{$LANG_ABBR}/main/">Главная страница</a></li>
							</ul>
						</div>
					</div>
					<div id="footer">Copyright &#169; 2007 ColoCall<br />All rights reserved.</div>
            	</div>
        	</body>
        </html>
    </xsl:template>

    <xsl:template match="error">
        <h2 id="page_title" style="font-size: 2em; color: #F00;"><xsl:value-of select="message" disable-output-escaping="yes" /></h2>

        <xsl:if test="$IN_DEBUG_MODE = 1">        
            <p>
				<strong>File: </strong> <xsl:value-of select="@file"/><br />
				<strong>Line: </strong><xsl:value-of select="@line"/>
			</p>
            <xsl:apply-templates select="customMessages"/>
        </xsl:if>
    </xsl:template>

<xsl:template match="customMessages">
    <ol>
        <xsl:apply-templates />
    </ol>
</xsl:template>

<xsl:template match="customMessage">
    <li><xsl:value-of select="."/></li>
</xsl:template>

</xsl:stylesheet>