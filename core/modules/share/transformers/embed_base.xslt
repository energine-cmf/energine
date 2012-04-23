<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:nrgn="http://energine.org"
        xmlns="http://www.w3.org/1999/xhtml">







    <!-- именованный шаблон с дефолтным набором атрибутов для элемента формы - НЕ ПЕРЕПИСЫВАТЬ В ДРУГОМ МЕСТЕ! -->
    <xsl:template name="FORM_ELEMENT_ATTRIBUTES">

    </xsl:template>
    <!-- /default form elements -->







    <!-- именованный шаблон для построения заголовка окна -->
    <xsl:template name="build_title">
    </xsl:template>

    <!-- именованный шаблон для подключения значка сайта -->
    <xsl:template name="favicon">
        <link rel="shortcut icon" href="{$STATIC_URL}images/energine.ico" type="image/x-icon"/>
    </xsl:template>

    <!-- именованный шаблон для подключения интерфейсных скриптов  -->
    <xsl:template name="scripts">
        <xsl:if test="not($DOC_PROPS[@name='single'])"><!-- User JS is here--></xsl:if>
    </xsl:template>

    <!-- именованный шаблон для подключения файлов стилей -->
    <xsl:template name="stylesheets">
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

    <!-- URL ресайзера изображений -->
    <xsl:variable name="IMAGE_RESIZER_URL"><xsl:value-of select="$STATIC_URL"/>resizer/</xsl:variable>

    <!-- URL ресайзера видео -->
    <xsl:variable name="VIDEO_RESIZER_URL"><xsl:value-of select="$STATIC_URL"/>resizer/</xsl:variable>

</xsl:stylesheet>
