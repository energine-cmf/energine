<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    >

    <xsl:output method="html"
                version="1.0"
                encoding="utf-8"
                omit-xml-declaration="yes"
                doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
                doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
                indent="yes" />

    <xsl:variable name="BASE" select="/document/properties/property[@name='base']"/>
    <xsl:variable name="STATIC_URL" select="$BASE"/>
    <xsl:variable name="FOLDER" select="$BASE/@folder"></xsl:variable>
    <xsl:variable name="LANG_ABBR" select="/document/properties/property[@name='lang']/@abbr"/>
    <xsl:variable name="IN_DEBUG_MODE"><xsl:value-of select="/document/@debug"/></xsl:variable>

    <xsl:template match="/document">
        <html>
        	<head>
                <title>Errors</title>
        		<base href="{$BASE}"/>
                <link href="{$STATIC_URL}images/energine.ico" rel="shortcut icon" type="image/x-icon"/>
                <link href="{$STATIC_URL}stylesheets/{$FOLDER}/main.css" rel="stylesheet" type="text/css" media="Screen, projection"/>
				<xsl:text disable-output-escaping="yes">&lt;!--[if IE]&gt;</xsl:text>
                    <link href="{$STATIC_URL}stylesheets/{$FOLDER}/ie.css" rel="stylesheet" type="text/css" media="Screen, projection"/>
                <xsl:text disable-output-escaping="yes">&lt;![endif]--&gt;</xsl:text>
                <link href="{$STATIC_URL}stylesheets/{$FOLDER}/print.css" rel="stylesheet" type="text/css" media="print"/>
                <link href="{$STATIC_URL}stylesheets/{$FOLDER}/handheld.css" rel="stylesheet" type="text/css" media="handheld"/>
        	</head>
        	<body class="error_page">

                <div class="base">
                    <div class="header">
                        <h1 class="logo">
                            <a href="{$BASE}{$LANG_ABBR}"><img src="{$STATIC_URL}images/{$FOLDER}/energine_logo.png" width="246" height="64" alt="Energine"/></a>
                        </h1>
                    </div>
                    <div class="main">
                        <xsl:apply-templates select="errors"/>
                        <div class="go_back"><a href="{$BASE}{$LANG_ABBR}">Вернуться на главную</a></div>
                    </div>
                    <div class="footer">
                        2014
                    </div>
                </div>

        	</body>
        </html>
    </xsl:template>

    <xsl:template match="errors">
        <div class="error_list">
            <xsl:apply-templates/>
        </div>
    </xsl:template>

    <xsl:template match="error">
        <div class="error_item">
            <h1 class="error_name">
                <xsl:value-of select="message" disable-output-escaping="yes"/>
            </h1>
            <xsl:if test="$IN_DEBUG_MODE = 1">
                <div class="error_text">
                    <div><strong>File: </strong><xsl:value-of select="@file"/></div>
                    <div><strong>Line: </strong><xsl:value-of select="@line"/></div>
                </div>
                <xsl:if test="customMessage">
                    <ul>
                        <xsl:apply-templates select="customMessage"/>
                    </ul>
                </xsl:if>

            </xsl:if>
        </div>
    </xsl:template>

    <xsl:template match="customMessage">
        <li><pre><xsl:value-of select="."/></pre></li>
    </xsl:template>

    <xsl:template match="backtrace">
        <ol>
            <xsl:apply-templates />
        </ol>
    </xsl:template>

    <xsl:template match="backtrace/call">
        <li>
            <div><strong><xsl:value-of select="file"/>(<xsl:value-of select="line"/>)</strong></div>
            <div>
                <xsl:value-of select="class"/><xsl:value-of select="type"/><xsl:value-of select="function"/>(<xsl:value-of
                    select="args"/>)
            </div>
        </li>
    </xsl:template>

</xsl:stylesheet>
        