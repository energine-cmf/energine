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
    <xsl:variable name="RESIZER_URL"><xsl:value-of select="$BASE/@resizer"/></xsl:variable>
    <xsl:variable name="MAIN_SITE"><xsl:value-of select="$DOC_PROPS[@name='base']/@default"/><xsl:value-of select="$LANG_ABBR"/></xsl:variable>

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
                        <link rel="stylesheet" type="text/css" href="{$STATIC_URL}stylesheets/singlemode.css"/>
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

                <script type="text/javascript">
                    $extend(Energine, {
                    <xsl:if test="document/@debug=1">
                        debug :true,
                    </xsl:if>
                    'base' : '<xsl:value-of select="$BASE"/>',
                    'static' : '<xsl:value-of select="$STATIC_URL"/>',
                    'resizer' : '<xsl:value-of select="$RESIZER_URL"/>',
                    'media' : '<xsl:value-of select="$MEDIA_URL"/>',
                    'root' : '<xsl:value-of select="$MAIN_SITE"/>',
                    'lang' : '<xsl:value-of select="$DOC_PROPS[@name='lang']/@real_abbr"/>'
                    });
                </script>

                <xsl:apply-templates select="/document/javascript/library" mode="head"/>

                <xsl:call-template name="scripts"/>

                <script type="text/javascript">
                    var componentToolbars = [];
                    <xsl:if test="count($COMPONENTS[recordset]/javascript/behavior[@name!='PageEditor']) &gt; 0">
                        var <xsl:for-each select="$COMPONENTS[recordset]/javascript[behavior[@name!='PageEditor']]"><xsl:value-of select="generate-id(../recordset)"/><xsl:if test="position() != last()">,</xsl:if></xsl:for-each>;
                    </xsl:if>
                    window.addEvent('domready', function () {
                        try {
        				<xsl:if test="$COMPONENTS[@componentAction='showPageToolbar']">
                            <xsl:variable name="PAGE_TOOLBAR" select="$COMPONENTS[@componentAction='showPageToolbar']"></xsl:variable>
                            var pageToolbar = new <xsl:value-of select="$PAGE_TOOLBAR/javascript/behavior/@name" />('<xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="$PAGE_TOOLBAR/@single_template" />', <xsl:value-of select="$ID" />, '<xsl:value-of select="$PAGE_TOOLBAR/toolbar/@name"/>', [
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
                        <xsl:for-each select="$COMPONENTS[@componentAction!='showPageToolbar']/javascript/behavior[@name!='PageEditor']">
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
                        <xsl:if test="$COMPONENTS/javascript/behavior[@name='PageEditor']">
                            <xsl:if test="position()=1">
                                <xsl:variable name="objectID" select="generate-id($COMPONENTS[javascript/behavior[@name='PageEditor']]/recordset)"/>
                                <xsl:value-of select="$objectID"/> = new PageEditor();
                            </xsl:if>
                        </xsl:if>

                        }
                        catch (e) {
                    <xsl:if test="document/@debug=1">
                        if (window['console']) {
                            if (console['error']) {
                                console.error(e);
                            }
                        }
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
        <xsl:if test="$COMPONENTS[@class='Ads']/recordset/record/field[@name='ad_top_728_90']">
            <div class="top_adblock">
                <xsl:value-of select="$COMPONENTS[@class='Ads']/recordset/record/field[@name='ad_top_728_90']" disable-output-escaping="yes"/>
            </div>
        </xsl:if>
        <xsl:if test="$COMPONENTS[@class='CrossDomainAuth']">
            <img src="{$COMPONENTS[@class='CrossDomainAuth']/@authURL}?return={$COMPONENTS[@class='CrossDomainAuth']/@returnURL}" width="1" height="1" style="display:none;" alt="" onload="document.location = document.location.href;"/>
        </xsl:if>
        <div class="base">
            <div class="header">
                <h1 class="logo">
                    <a>
                        <xsl:if test="$DOC_PROPS[@name='default']!=1">
                            <xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/></xsl:attribute>
                        </xsl:if>
                        <img src="images/{$FOLDER}/energine_logo.png" width="246" height="64" alt="Energine"/>
                    </a>
                </h1>
                <xsl:apply-templates select="$COMPONENTS[@class='LangSwitcher']"/>
            </div>
            <div class="main">
                <xsl:apply-templates select="$COMPONENTS[@class='BreadCrumbs']"/>
                <xsl:apply-templates select="content"/>
            </div>
            <div class="footer">
                <xsl:apply-templates select="$COMPONENTS[@name='footerTextBlock']"/>
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
    
    <xsl:template match="/document/translations"/>

    <xsl:template match="component/javascript"/>
    
    <!-- Выводим переводы для WYSIWYG -->
    <xsl:template match="/document/translations[translation[@component=//component[@editable]/@name]]">
            <script type="text/javascript">
                <xsl:for-each select="translation[@component=$COMPONENTS[@editable]/@name]">
                    Energine.translations.set('<xsl:value-of select="@const"/>', '<xsl:value-of select="."/>');
                </xsl:for-each>
            </script>
    </xsl:template>

    <xsl:template match="/document/javascript"/>

    <xsl:template match="/document/javascript/library"/>

    <xsl:template match="/document/javascript/library" mode="head">
        <xsl:variable name="anticache">
            <xsl:if test="/document/@debug=1">
                <xsl:text>?</xsl:text>
                <xsl:value-of select="generate-id()"/>
            </xsl:if>
        </xsl:variable>
        <script type="text/javascript" src="{$STATIC_URL}scripts/{@path}.js{$anticache}"/>
    </xsl:template>

</xsl:stylesheet>
