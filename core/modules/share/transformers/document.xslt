<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:og="http://ogp.me/ns#"
    xmlns:video="http://ogp.me/ns/video#"
    xmlns:nrgn="http://energine.net#"
    exclude-result-prefixes="og video nrgn"
    >
    <xsl:variable name="DOC_PROPS" select="/document/properties/property"/>
    <xsl:variable name="VARS" select="/document/variables/var"/>
    <xsl:variable name="COMPONENTS" select="//component[@name]"/>
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
    <xsl:variable name="TEMPLATE"><xsl:value-of select="$DOC_PROPS[@name='template']"/></xsl:variable>

    <!--@deprecated-->
    <!--Оставлено для обратной совместимости, сейчас рекомендуется определять обработчик рута в модуле сайта и взывать рутовый шаблон в режиме head-->
    <xsl:template match="/" mode="title">
        <title><xsl:choose>
            <xsl:when test="$DOC_PROPS[@name='title']/@alt = ''">
                <xsl:for-each select="$COMPONENTS[@name='breadCrumbs']/recordset/record">
                    <xsl:sort data-type="text" order="descending" select="position()"/>
                    <xsl:choose>
                        <xsl:when test="position() = last()">
                            <xsl:if test="$ID = field[@name='Id'] and (field[@name='Name'] != '' or field[@name='Title'] != '')">
                                <xsl:choose>
                                    <xsl:when test="field[@name='Title'] != ''">
                                        <xsl:value-of select="field[@name='Title']"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="field[@name='Name']"/>
                                    </xsl:otherwise>
                                </xsl:choose>
                                <xsl:text> / </xsl:text>
                            </xsl:if>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:if test="field[@name='Name'] != '' or field[@name='Title'] != ''">
                                <xsl:choose>
                                    <xsl:when test="field[@name='Title'] != ''">
                                        <xsl:value-of select="field[@name='Title']"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="field[@name='Name']"/>
                                    </xsl:otherwise>
                                </xsl:choose>
                                <xsl:text> / </xsl:text>
                            </xsl:if>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:for-each>
                <xsl:value-of select="$COMPONENTS[@name='breadCrumbs']/@site"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$DOC_PROPS[@name='title']/@alt"/>
            </xsl:otherwise>
        </xsl:choose></title>
    </xsl:template>

    <xsl:template match="/" mode="favicon">
        <link rel="shortcut icon" type="image/x-icon">
            <xsl:attribute name="href">
                <xsl:choose>
                    <xsl:when test="$DOC_PROPS[@name='base']/@favicon!=''"><xsl:value-of select="$DOC_PROPS[@name='base']/@favicon"/></xsl:when>
                    <xsl:otherwise><xsl:value-of select="$STATIC_URL"/>images/energine.ico</xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
        </link>
    </xsl:template>

    <xsl:template match="/" mode="stylesheets">
        <!-- файлы стилей для текущего варианта дизайна -->
        <link href="{$STATIC_URL}stylesheets/{$FOLDER}/main.css" rel="stylesheet" type="text/css"
              media="Screen, projection"/>
        <!-- отдельный файл стилей для IE подключается через условные комментарии -->
        <xsl:text disable-output-escaping="yes">&lt;!--[if IE]&gt;</xsl:text>
        <link href="{$STATIC_URL}stylesheets/{$FOLDER}/ie.css" rel="stylesheet" type="text/css"
              media="Screen, projection"/>
        <xsl:text disable-output-escaping="yes">&lt;![endif]--&gt;</xsl:text>
        <link href="{$STATIC_URL}stylesheets/{$FOLDER}/print.css" rel="stylesheet" type="text/css" media="print"/>
        <link href="{$STATIC_URL}stylesheets/{$FOLDER}/handheld.css" rel="stylesheet" type="text/css" media="handheld"/>
    </xsl:template>

    <xsl:template match="/" mode="scripts">
        <xsl:if test="//field[@type='captcha' and ancestor::component[@type='form']]">
            <script src="https://www.google.com/recaptcha/api.js" async="async" defer="defer"></script>
        </xsl:if>
        <xsl:if test="not($DOC_PROPS[@name='single'])"><!-- User JS is here--></xsl:if>
    </xsl:template>

    <xsl:template match="/" mode="jquery_scripts">
        <xsl:if test="not($DOC_PROPS[@name='single'])"><!-- User JS is here--></xsl:if>
    </xsl:template>

    <xsl:template match="/" mode="og">
        <xsl:for-each select="document/og/property[@name!='duration']">
            <meta property="og:{@name}" content="{.}" />
        </xsl:for-each>
        <xsl:if test="document/og/property[@name='duration']">
            <meta property="video:duration" content="{document/og/property[@name='duration']}" />
        </xsl:if>
        <xsl:if test="document/og/property[@name='image']">
            <link rel="image_src" href="{document/og/property[@name='image']}" />
        </xsl:if>
        <meta property="og:url" content="{$DOC_PROPS[@name='url']}" />
    </xsl:template>

    <xsl:template match="/" mode="head">
        <xsl:apply-templates select="." mode="title"/>
        <base href="{$BASE}"/>
        <xsl:apply-templates select="." mode="favicon"/>

        <link rel="stylesheet" type="text/css" href="{$STATIC_URL}stylesheets/energine.css"/>
        <xsl:choose>
            <xsl:when test="not($DOC_PROPS[@name='single'])">
                <xsl:apply-templates select="." mode="stylesheets"/>
            </xsl:when>
            <xsl:otherwise>
                <link rel="stylesheet" type="text/css" href="{$STATIC_URL}stylesheets/singlemode.css"/>
                <!--<script type="text/javascript">window.singleMode = true;</script>-->
            </xsl:otherwise>
        </xsl:choose>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <xsl:if test="$DOC_PROPS[@name='keywords']">
            <meta name="keywords" content="{$DOC_PROPS[@name='keywords']}"/>
        </xsl:if>
        <xsl:if test="$DOC_PROPS[@name='description']">
            <meta name="description" content="{$DOC_PROPS[@name='description']}"/>
        </xsl:if>
        <xsl:if test="$DOC_PROPS[@name='robots']">
            <meta name="robots" content="{$DOC_PROPS[@name='robots']}"/>
        </xsl:if>
        <xsl:apply-templates select="." mode="og"/>
        <script type="text/javascript" src="{/document/javascript/@mootools}"></script>
        <script type="text/javascript" src="{$STATIC_URL}scripts/Energine.js"></script>
        <script type="text/javascript">
            Object.append(Energine, {
            <xsl:if test="document/@debug=1">'debug' :true,</xsl:if>
            'base' : '<xsl:value-of select="$BASE"/>',
            'static' : '<xsl:value-of select="$STATIC_URL"/>',
            'resizer' : '<xsl:value-of select="$RESIZER_URL"/>',
            'media' : '<xsl:value-of select="$MEDIA_URL"/>',
            'root' : '<xsl:value-of select="$MAIN_SITE"/>',
            'lang' : '<xsl:value-of select="$DOC_PROPS[@name='lang']/@real_abbr"/>',
            'singleMode':<xsl:value-of select="boolean($DOC_PROPS[@name='single'])"/>
            });
        </script>
        <xsl:apply-templates select="/document//javascript/variable" mode="head"/>
        <xsl:apply-templates select="/document/javascript/library" mode="head"/>
        <xsl:apply-templates select="." mode="scripts"/>
        <script type="text/javascript">
            var componentToolbars = [];
            <xsl:if test="count($COMPONENTS[recordset]/javascript/behavior[(@name!='PageEditor')]) &gt; 0">
                var <xsl:for-each select="$COMPONENTS[recordset]/javascript[behavior[(@name!='PageEditor')]]"><xsl:for-each select="behavior"><xsl:if
                    test="@use='jquery'">jquery_</xsl:if><xsl:value-of select="generate-id(../../recordset)"/><xsl:if test="position() != last()">,</xsl:if></xsl:for-each><xsl:if test="position() != last()">,</xsl:if></xsl:for-each>;
            </xsl:if>
            window.addEvent('domready', function () {
                <xsl:if test="$COMPONENTS[@componentAction='showPageToolbar']">
                    try {
                    <xsl:variable name="PAGE_TOOLBAR" select="$COMPONENTS[@componentAction='showPageToolbar']"></xsl:variable>
                    var pageToolbar = new <xsl:value-of select="$PAGE_TOOLBAR/javascript/behavior/@name" />('<xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="$PAGE_TOOLBAR/@single_template" />', <xsl:value-of select="$ID" />, '<xsl:value-of select="$PAGE_TOOLBAR/toolbar/@name"/>', [
                    <xsl:for-each select="$PAGE_TOOLBAR/toolbar/control">
                        {
                        <xsl:for-each select="@*[name()!='mode']">
                            '<xsl:value-of select="name()"/>':'<xsl:value-of select="."/>'<xsl:if test="position()!=last()">,</xsl:if>
                        </xsl:for-each><xsl:if test="options">, 'options':{
                            <xsl:for-each select="options/option">'<xsl:value-of select="@id"/>':'<xsl:value-of select="."/>'<xsl:if test="position()!=last()">,</xsl:if>
                            </xsl:for-each>},
                        'initialValue':'<xsl:value-of select="options/option[@selected]/@id"/>'
                        </xsl:if>
                        }<xsl:if test="position()!=last()">,</xsl:if></xsl:for-each>
                    ]<xsl:if test="$PAGE_TOOLBAR/toolbar/properties/property">, <xsl:for-each select="$PAGE_TOOLBAR/toolbar/properties/property">{'<xsl:value-of select="@name"/>':'<xsl:value-of select="."/>'<xsl:if test="position()!=last()">,</xsl:if>}</xsl:for-each></xsl:if>);
                    }
                    catch (e) {
                        console.error(e);
                    }
                </xsl:if>
                <xsl:for-each select="$COMPONENTS[@componentAction!='showPageToolbar']/javascript/behavior[(@name!='PageEditor') and not(@use='jquery')]">
                    <xsl:call-template name="INIT_JS" />
                </xsl:for-each>
                <xsl:if test="$COMPONENTS/javascript/behavior[@name='PageEditor']">
                    <xsl:if test="position()=1">
                        <xsl:variable name="objectID" select="generate-id($COMPONENTS[javascript/behavior[@name='PageEditor']]/recordset)"/>
                        try {
                            <xsl:value-of select="$objectID"/> = new PageEditor();
                        }
                        catch (e) {
                            console.error(e);
                        }
                    </xsl:if>
                </xsl:if>
            });
        </script>
        <xsl:apply-templates select="document/translations"/>
        <xsl:variable name="USE_JQUERY" select="(count(/document/javascript/library[contains(@name, 'jquery')]) &gt;0) or (count(//javascript/behavior[@use='jquery']) &gt; 0)"/>
        <xsl:if test="$USE_JQUERY">
            <script src="{/document/javascript/@jquery}"></script>
            <script type="text/javascript">
                jQuery.noConflict();
                jQuery.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
                  options['headers'] = {'X-Request': options.dataType};
                });
            </script>

            <xsl:apply-templates select="/" mode="jquery_scripts"/>
            <xsl:apply-templates select="/document/javascript/library" mode="jquery"/>
            <xsl:if test="count(//javascript/behavior[@use='jquery']) &gt; 0">
                <script type="text/javascript">
                    (function($, window, document) {
                    // Listen for the jQuery ready event on the document
                    $(function() {
                <xsl:for-each select="//javascript/behavior[@use='jquery']">
                    <xsl:call-template name="INIT_JS">
                        <xsl:with-param name="PREFIX">jquery_</xsl:with-param>
                    </xsl:call-template>
                </xsl:for-each>
                    });
                    }(window.jQuery, window, document));
                </script>
            </xsl:if>
            <xsl:apply-templates select="/document/javascript/library"/>
        </xsl:if>
    </xsl:template>

    <xsl:template name="INIT_JS">
        <xsl:param name="PREFIX"></xsl:param>
        <xsl:variable name="objectID" select="generate-id(../../recordset[not(@name)])"/>
        <xsl:choose>
            <xsl:when test="$objectID!=''">
                if(document.getElementById('<xsl:value-of select="$objectID"/>')){
                    <xsl:value-of select="$PREFIX"/><xsl:value-of select="$objectID"/> = new <xsl:value-of select="@name"/>('<xsl:value-of select="$objectID"/>');
                }
            </xsl:when>
            <xsl:otherwise>
                new <xsl:value-of select="@name"/>();
            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>

    <xsl:template match="document">
        <xsl:apply-templates select="layout"/>
    </xsl:template>

    <!-- Single mode document -->
    <xsl:template match="document[properties/property[@name='single']]">
        <xsl:attribute name="class">e-singlemode-layout</xsl:attribute>
        <xsl:apply-templates select="container | component"/>
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

    <xsl:template match="/document//javascript/variable"/>

    <xsl:template match="/document/javascript/library" mode="head">
        <xsl:variable name="PATH" select="@name"/>
        <xsl:if test="not(contains($PATH, 'jquery')) and not(//behavior[(@use='jquery') and (@name=$PATH)]) and(not(contains(@path, 'jquery')))">
            <script type="text/javascript" src="{$STATIC_URL}scripts/{@path}.js"/>
        </xsl:if>
    </xsl:template>

    <xsl:template match="/document/javascript/library" mode="jquery">
        <xsl:variable name="PATH" select="@path"/>
        <xsl:variable name="NAME" select="@name"/>
        <xsl:if test="contains($PATH,'jquery')">
            <script type="text/javascript" src="{@path}"/>
        </xsl:if>
        <xsl:if test="//behavior[@use='jquery']/@name=$NAME">
            <script type="text/javascript" src="{$STATIC_URL}scripts/{@path}.js"/>
        </xsl:if>
    </xsl:template>

    <xsl:template match="/document//javascript/variable" mode="head">
        <script type="text/javascript">
            <xsl:text>window["</xsl:text>
            <xsl:value-of select="@name"/>
            <xsl:text>"] = </xsl:text>
            <xsl:choose>
                <xsl:when test="@type='json'">
                    <xsl:value-of select="."/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>"</xsl:text>
                    <xsl:value-of select="."/>
                    <xsl:text>"</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:text>;</xsl:text>
        </script>
    </xsl:template>

    <xsl:template match="component[@class='SiteProperties']"/>

</xsl:stylesheet>
