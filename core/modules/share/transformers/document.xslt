<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">

    <xsl:variable name="DOC_PROPS" select="/document/properties/property"/>
    <xsl:variable name="COMPONENTS" select="//component[@name][@module]"/>
    <xsl:variable name="TRANSLATION" select="/document/translations/translation"/>
    <xsl:variable name="ID" select="$DOC_PROPS[@name='ID']"/>
	<xsl:variable name="BASE" select="$DOC_PROPS[@name='base']"/>
	<xsl:variable name="LANG_ID" select="$DOC_PROPS[@name='lang']"/>
	<xsl:variable name="LANG_ABBR" select="$DOC_PROPS[@name='lang']/@abbr"/>
	<xsl:variable name="NBSP"><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></xsl:variable>
    

    <xsl:template match="/" xmlns:nrgn="http://energine.org" xmlns="http://www.w3.org/1999/xhtml">
        <html>
        	<head>
                <title><xsl:call-template name="build_title"/></title>
        		<base href="{$BASE}"/>
                <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon"/>
        		<xsl:choose>
            		<xsl:when test="not($DOC_PROPS[@name='single'])">
            		    <xsl:call-template name="stylesheets"/>
            		</xsl:when>
            		<xsl:otherwise>
                        <link rel="stylesheet" type="text/css" href="stylesheets/singlemode.css"/>
                        <script type="text/javascript">window.singleMode = true;</script>
            		</xsl:otherwise>
        		</xsl:choose>
                <link rel="stylesheet" type="text/css" href="stylesheets/energine.css"/>
                <xsl:if test="$DOC_PROPS[@name='google_verify']">
                    <meta name="google-site-verification" content="{$DOC_PROPS[@name='google_verify']}"/>
                </xsl:if>
                <meta name="keywords" content="{$DOC_PROPS[@name='keywords']}"/>
                <meta name="description" content="{$DOC_PROPS[@name='description']}"/>
                <xsl:choose>
                    <xsl:when test="document/@debug=1">
                        <script type="text/javascript" src="scripts/mootools-debug.js"></script>
                        <script type="text/javascript" src="scripts/mootools-more-debug.js"></script>
                    </xsl:when>
                    <xsl:otherwise>
                        <script type="text/javascript" src="scripts/mootools.js"></script>
                        <script type="text/javascript" src="scripts/mootools-more.js"></script>
                    </xsl:otherwise>
                </xsl:choose>
        		<script type="text/javascript" src="scripts/Energine.js"></script>

                <xsl:call-template name="interface_js"/>

                <script type="text/javascript">
                    window.addEvent('domready', function () {
                   		<xsl:if test="document/@debug=1">
							Energine.debug = true;
		        		</xsl:if>
		        		Energine.base = '<xsl:value-of select="$BASE"/>';
                        try {
                            ScriptLoader.load(<xsl:for-each select="$COMPONENTS/javascript/include | $COMPONENTS/javascript/object[@name!='PageEditor']">'<xsl:value-of select="@name" />.js'<xsl:if test="position() != last()">,</xsl:if></xsl:for-each>);
        				<xsl:if test="$COMPONENTS[@componentAction='showPageToolbar']">
                            var pageToolbar = new <xsl:value-of select="$COMPONENTS[@name='pageToolBar']/javascript/object/@name" />('<xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="$COMPONENTS[@name='pageToolBar']/@single_template" />', <xsl:value-of select="$ID" />, '<xsl:value-of select="$COMPONENTS[@name='pageToolBar']/toolbar/@name"/>');
                            <xsl:for-each select="$COMPONENTS[@name='pageToolBar']/toolbar/control">
                            pageToolbar.appendControl({
                                <xsl:for-each select="@*[name()!='mode']">
                                    '<xsl:value-of select="name()"/>':'<xsl:value-of select="."/>'
                                    <xsl:if test="position()!=last()">,</xsl:if>
                                </xsl:for-each>
                            });	    
          					</xsl:for-each>
                               
                            <xsl:if test="not($COMPONENTS[@class='TextBlock'])">
                                pageToolbar.getControlById('editMode').disable();
                            </xsl:if>
        				</xsl:if>
                        <xsl:for-each select="$COMPONENTS[@componentAction!='showPageToolbar']/javascript/object[@name!='PageEditor']">
                            <xsl:variable name="objectID" select="generate-id(../../recordset[not(@name)])"/>
                            <xsl:value-of select="$objectID"/> = new <xsl:value-of select="@name"/>($('<xsl:value-of select="$objectID"/>'));
        				</xsl:for-each>
                        <xsl:if test="$COMPONENTS/javascript/object[@name='PageEditor']">
                            <xsl:if test="position()=1">
                                ScriptLoader.load('PageEditor.js');
                                <xsl:variable name="objectID" select="generate-id($COMPONENTS[javascript/object[@name='PageEditor']]/recordset)"/>
                                <xsl:value-of select="$objectID"/> = new PageEditor();
                            </xsl:if>
                        </xsl:if>

                        }
                        catch (e) {
                                console.error(e);
                                //alert(e.message);
                        }
                    });
        		</script>
                <xsl:apply-templates select="document/translations"/>
        	</head>
        	<body>
        		<xsl:apply-templates select="document"/>
        	</body>
        </html>
    </xsl:template>

    <xsl:template match="document">
        <div id="container">
            <div id="header">
                <img id="logo" src="images/energine_logo.png" width="246" height="64" alt="Energine"/>
                <xsl:apply-templates select="$COMPONENTS[@class='LangSwitcher']"/>
            </div>
            <xsl:apply-templates select="$COMPONENTS[@class='BreadCrumbs']"/>
            <xsl:apply-templates select="$COMPONENTS[@class='MainMenu']"/>
            <div id="content">
                <h1><xsl:value-of select="$DOC_PROPS[@name='title']"/></h1>
                <xsl:apply-templates select="content" />
            </div>
            <xsl:apply-templates select="$COMPONENTS[@class='LoginForm']"/>
            <div id="footer">
                <xsl:apply-templates select="$COMPONENTS[@name='FooterTextBlock']"/>
            </div>
        </div>
    </xsl:template>

    <!-- Single mode document -->
    <xsl:template match="document[properties/property[@name='single']]">
        <xsl:apply-templates select="//component"/>
    </xsl:template>

    <xsl:template match="layout | content">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="/document/translations"/>

</xsl:stylesheet>