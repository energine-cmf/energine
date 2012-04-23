<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:set="http://exslt.org/sets"
    xmlns="http://www.w3.org/1999/xhtml">
    <xsl:variable name="DOC_PROPS" select="/document/properties/property"/>
    <xsl:variable name="COMPONENTS" select="//component[@name][@module]"/>
    <xsl:variable name="TRANSLATION" select="/document/translations/translation"/>
    <xsl:variable name="ID" select="$DOC_PROPS[@name='ID']"/>
	<xsl:variable name="BASE" select="$DOC_PROPS[@name='base']"/>
    <xsl:variable name="FOLDER" select="$DOC_PROPS[@name='base']/@folder"/>
	<xsl:variable name="LANG_ID" select="$DOC_PROPS[@name='lang']"/>
	<xsl:variable name="LANG_ABBR" select="$DOC_PROPS[@name='lang']/@abbr"/>
	<xsl:variable name="NBSP"><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></xsl:variable>
    <xsl:variable name="STATIC_URL"><xsl:value-of select="$BASE/@static"/></xsl:variable>
    <xsl:variable name="MEDIA_URL"><xsl:value-of select="$BASE/@media"/></xsl:variable>

    <xsl:template match="/" xmlns:nrgn="http://energine.org" xmlns="http://www.w3.org/1999/xhtml">
        <html>
        	<head>
                <title><xsl:call-template name="build_title"/></title>
        		<base href="{$BASE}"/>
                <xsl:call-template name="favicon"/>                
        		<xsl:choose>
            		<xsl:when test="not($DOC_PROPS[@name='single'])">
            		    <xsl:call-template name="stylesheets"/>
            		</xsl:when>
            		<xsl:otherwise>
                        <link rel="stylesheet" type="text/css" href="stylesheets/singlemode.css"/>
                        <script type="text/javascript">window.singleMode = true;</script>
            		</xsl:otherwise>
        		</xsl:choose>
                <link rel="stylesheet" type="text/css" href="{$STATIC_URL}stylesheets/energine.css"/>
                <xsl:if test="$DOC_PROPS[@name='google_verify']">
                    <meta name="google-site-verification" content="{$DOC_PROPS[@name='google_verify']}"/>
                </xsl:if>
                <meta name="keywords" content="{$DOC_PROPS[@name='keywords']}"/>
                <meta name="description" content="{$DOC_PROPS[@name='description']}"/>
                <xsl:if test="$DOC_PROPS[@name='robots']!=''">
                    <meta name="robots" content="{$DOC_PROPS[@name='robots']}"/>
                </xsl:if>
                <xsl:choose>
                    <xsl:when test="document/@debug=1">
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools-debug.js"></script>
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools-more-debug.js"></script>
                    </xsl:when>
                    <xsl:otherwise>
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools.js"></script>
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools-more.js"></script>
                    </xsl:otherwise>
                </xsl:choose>
        		<script type="text/javascript" src="{$STATIC_URL}scripts/Energine.js"></script>


                <xsl:if test="$DOC_PROPS[@name='google_analytics'] and ($DOC_PROPS[@name='google_analytics'] != '')">
                    <xsl:value-of select="$DOC_PROPS[@name='google_analytics']" disable-output-escaping="yes"/>
                </xsl:if>
        	</head>
        	<body>
        		<!--<xsl:apply-templates select="document"/>-->
        	</body>
        </html>
    </xsl:template>



</xsl:stylesheet>
