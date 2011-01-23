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
                <link rel="shortcut icon" href="{$BASE}images/favicon.ico" type="image/x-icon"/>
        		<xsl:choose>
            		<xsl:when test="not($DOC_PROPS[@name='single'])">
            		    <xsl:call-template name="stylesheets"/>
            		</xsl:when>
            		<xsl:otherwise>
                        <link rel="stylesheet" type="text/css" href="stylesheets/singlemode.css"/>
                        <script type="text/javascript">window.singleMode = true;</script>
            		</xsl:otherwise>
        		</xsl:choose>
			<link rel="stylesheet" type="text/css" href="{$STATIC_URL}/stylesheets/energine.css"/>
                <xsl:if test="$DOC_PROPS[@name='google_verify']">
                    <meta name="google-site-verification" content="{$DOC_PROPS[@name='google_verify']}"/>
                </xsl:if>
                <meta name="keywords" content="{$DOC_PROPS[@name='keywords']}"/>
                <meta name="description" content="{$DOC_PROPS[@name='description']}"/>
                <xsl:choose>
                    <xsl:when test="document/@debug=1">
                        <script type="text/javascript" src="{$STATIC_URL}/scripts/mootools-debug.js"></script>
                        <script type="text/javascript" src="{$STATIC_URL}/scripts/mootools-more-debug.js"></script>
                    </xsl:when>
                    <xsl:otherwise>
                        <script type="text/javascript" src="{$STATIC_URL}/scripts/mootools.js"></script>
                        <script type="text/javascript" src="{$STATIC_URL}/scripts/mootools-more.js"></script>
                    </xsl:otherwise>
                </xsl:choose>
        		<script type="text/javascript" src="{$STATIC_URL}/scripts/Energine.js"></script>
                <xsl:if test="not($DOC_PROPS[@name='single'])"  >
                    <xsl:call-template name="interface_js"/>
                </xsl:if>

                <script type="text/javascript">
                    var componentToolbars = [];
                    <xsl:if test="count($COMPONENTS[recordset]/javascript/object[@name!='PageEditor']) &gt; 0">
                        var <xsl:for-each select="$COMPONENTS[recordset]/javascript[object[@name!='PageEditor']]"><xsl:value-of select="generate-id(../recordset)"/><xsl:if test="position() != last()">,</xsl:if></xsl:for-each>;
                    </xsl:if>
                    window.addEvent('domready', function () {
                   		<xsl:if test="document/@debug=1">
							Energine.debug = true;
		        		</xsl:if>
		        		Energine.base = '<xsl:value-of select="$BASE"/>';
                        Energine.static = '<xsl:value-of select="$STATIC_URL"/>';
                        try {
                        ScriptLoader.load(<xsl:for-each select="set:distinct($COMPONENTS/javascript/object[@name!='PageEditor']/@name)">'<xsl:value-of select="../@path" /><xsl:value-of select="." />'<xsl:if test="position() != last()">,</xsl:if></xsl:for-each>);
        				<xsl:if test="$COMPONENTS[@componentAction='showPageToolbar']">
                            <xsl:variable name="PAGE_TOOLBAR" select="$COMPONENTS[@componentAction='showPageToolbar']"></xsl:variable>
                            var pageToolbar = new <xsl:value-of select="$PAGE_TOOLBAR/javascript/object/@name" />('<xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="$PAGE_TOOLBAR/@single_template" />', <xsl:value-of select="$ID" />, '<xsl:value-of select="$PAGE_TOOLBAR/toolbar/@name"/>', [
                            <xsl:for-each select="$PAGE_TOOLBAR/toolbar/control">
                                {
                                <xsl:for-each select="@*[name()!='mode']">
                                    '<xsl:value-of select="name()"/>':'<xsl:value-of select="."/>'
                                    <xsl:if test="position()!=last()">,</xsl:if>
                                </xsl:for-each>
                                }<xsl:if test="position()!=last()">,</xsl:if></xsl:for-each>
                            ]);
                            <xsl:if test="not($COMPONENTS[@class='TextBlock'])">
                                pageToolbar.getControlById('editMode').disable();
                            </xsl:if>
        				</xsl:if>
                        <xsl:for-each select="$COMPONENTS[@componentAction!='showPageToolbar']/javascript/object[@name!='PageEditor']">
                            <xsl:variable name="objectID" select="generate-id(../../recordset[not(@name)])"/>
                            var initComponent = function(){
                                <xsl:value-of select="$objectID"/> = new <xsl:value-of select="@name"/>($('<xsl:value-of select="$objectID"/>'));
                            };
                            if(!$('<xsl:value-of select="$objectID"/>')){
                                initComponent.delay(10);
                            }
                            else{
                                initComponent();
                            }
        				</xsl:for-each>
                        <xsl:if test="$COMPONENTS/javascript/object[@name='PageEditor']">
                            <xsl:if test="position()=1">
                                ScriptLoader.load('PageEditor');
                                <xsl:variable name="objectID" select="generate-id($COMPONENTS[javascript/object[@name='PageEditor']]/recordset)"/>
                                <xsl:value-of select="$objectID"/> = new PageEditor();
                            </xsl:if>
                        </xsl:if>

                        }
                        catch (e) {
                    <xsl:if test="document/@debug=1">
                        console.error(e);
                    </xsl:if>
                    }
                    });
        		</script>
                <xsl:apply-templates select="document/translations"/>
                <xsl:if test="$DOC_PROPS[@name='google_analytics'] and ($DOC_PROPS[@name='google_analytics'] != '')">
                    <xsl:value-of select="$DOC_PROPS[@name='google_analytics']" disable-output-escaping="yes"/>
                </xsl:if>
        	</head>
        	<body>
        		<xsl:apply-templates select="document"/>
        	</body>
        </html>
    </xsl:template>

    <xsl:template match="document">
        <div id="container">
            <div id="header">
                <img id="logo" src="images/{$FOLDER}/energine_logo.png" width="246" height="64" alt="Energine"/>
                <xsl:apply-templates select="$COMPONENTS[@class='LangSwitcher']"/>
            </div>
            <xsl:apply-templates select="$COMPONENTS[@class='BreadCrumbs']"/>
            <div id="sidebar">
                <xsl:apply-templates select="$COMPONENTS[@name='mainMenu']"/>
                <xsl:apply-templates select="$COMPONENTS[@class='LoginForm']"/>
            </div>
            <div id="content">
                <h1><xsl:value-of select="$DOC_PROPS[@name='title']"/></h1>
                <xsl:apply-templates select="content" />
            </div>

            <div id="footer">
                <xsl:apply-templates select="$COMPONENTS[@name='FooterTextBlock']"/>
            </div>
        </div>
    </xsl:template>

    <!-- Single mode document -->
    <xsl:template match="document[properties/property[@name='single']]">
        <xsl:attribute name="class">e-singlemode-layout</xsl:attribute>
        <xsl:apply-templates select="container | component"/>
    </xsl:template>

    <xsl:template match="layout | content | container">
        <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="/document/translations" />

    <xsl:template match="component/javascript" />
    
    <!-- Выводим переводы для WYSIWYG -->
    <xsl:template match="/document/translations[translation[@component=//component[@editable]/@name]]">
            <script type="text/javascript">
                <xsl:for-each select="translation[@component=$COMPONENTS[@editable]/@name]">
                    Energine.translations.set('<xsl:value-of select="@const"/>', '<xsl:value-of select="."/>');
                </xsl:for-each>
            </script>
    </xsl:template>




</xsl:stylesheet>
