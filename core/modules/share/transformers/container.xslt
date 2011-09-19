<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns="http://www.w3.org/1999/xhtml"
        version="1.0">

    <xsl:template match="container[(@column or @widget) and //component[@name='adminPanel']]">
        <div>
            <xsl:choose>
                <xsl:when test="@column">
                    <xsl:attribute name="column"><xsl:value-of select="@name"/></xsl:attribute>
                </xsl:when>
                <xsl:when test="@widget">
                    <xsl:attribute name="class">e-widget</xsl:attribute>
                    <xsl:attribute name="widget"><xsl:value-of select="@name"/></xsl:attribute>
                    <xsl:if test="@widget='static'">
                        <xsl:attribute name="static">static</xsl:attribute>
                    </xsl:if>
                </xsl:when>
            </xsl:choose>
            <xsl:apply-templates/>
        </div>
    </xsl:template>

    <!-- Контейнеры с атрибутом html_class выводятся в виде div с соответствующим классом -->
    <xsl:template match="content[@html_class] | container[@html_class]">
        <div class="{@html_class}">
            <xsl:choose>
                <xsl:when test="@column and $COMPONENTS[@name='adminPanel']">
                    <xsl:attribute name="column"><xsl:value-of select="@name"/></xsl:attribute>
                </xsl:when>
                <xsl:when test="@widget and $COMPONENTS[@name='adminPanel']">
                    <xsl:attribute name="class">e-widget <xsl:value-of select="@html_class"/></xsl:attribute>
                    <xsl:attribute name="widget"><xsl:value-of select="@name"/></xsl:attribute>
                    <xsl:if test="@widget='static'">
                        <xsl:attribute name="static">static</xsl:attribute>
                    </xsl:if>
                </xsl:when>
            </xsl:choose>
            <xsl:apply-templates/>
        </div>
    </xsl:template>

    <!-- Блок - контейнер для визуального отделения одного или группы компонентов -->
    <xsl:template match="container[@block]">
        <xsl:param name="HOLDER_NAME"/>
        <xsl:if test="($COMPONENTS[@name='adminPanel']) or (component[not(@class='TextBlock') and not(recordset[@empty])]) or (component[@class='TextBlock' and (@editable or recordset/record/field != '')])">
            <div>
                <xsl:attribute name="class">block<xsl:if test="@html_class"><xsl:text disable-output-escaping="yes"> </xsl:text><xsl:value-of select="@html_class"/></xsl:if><xsl:if test="@widget and $COMPONENTS[@name='adminPanel']"> e-widget</xsl:if></xsl:attribute>
                <xsl:if test="@widget and $COMPONENTS[@name='adminPanel']">
                    <xsl:attribute name="widget">
                        <xsl:choose>
                            <xsl:when test="$HOLDER_NAME"><xsl:value-of select="$HOLDER_NAME"/></xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                    <xsl:if test="@widget='static'">
                        <xsl:attribute name="static">static</xsl:attribute>
                    </xsl:if>
                </xsl:if>
                <xsl:apply-templates select="component[1]" mode="block_header"/>
                <div class="block_content clearfix">
                    <xsl:apply-templates mode="block_content"/>
                </div>
            </div>
        </xsl:if>
    </xsl:template>

    <!-- Блок alfa - главный на странице, в него выводится заголовок страницы -->
    <xsl:template match="container[@block='alfa']">
        <xsl:param name="HOLDER_NAME"/>
        <div>
            <xsl:attribute name="class">block alfa_block<xsl:if test="@html_class"><xsl:text disable-output-escaping="yes"> </xsl:text><xsl:value-of select="@html_class"/></xsl:if><xsl:if test="@widget and $COMPONENTS[@name='adminPanel']"> e-widget</xsl:if></xsl:attribute>
            <xsl:if test="@widget and $COMPONENTS[@name='adminPanel']">
                <xsl:attribute name="widget">
                    <xsl:choose>
                        <xsl:when test="$HOLDER_NAME"><xsl:value-of select="$HOLDER_NAME"/></xsl:when>
                        <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
                <xsl:if test="@widget='static'">
                    <xsl:attribute name="static">static</xsl:attribute>
                </xsl:if>
            </xsl:if>
            <xsl:if test="$DOC_PROPS[@name='default'] != '1'">
                <div class="block_header clearfix">
                    <h1 class="block_title"><xsl:value-of select="$DOC_PROPS[@name='title']"/></h1>
                </div>
            </xsl:if>
            <div class="block_content clearfix">
                <xsl:apply-templates mode="block_content"/>
            </div>
        </div>
    </xsl:template>

    <!--
        Контейнер с атрибутом contains - это холдер, куда вставляется другой контейнер или компонент. 
        Например, можно в любое место в контентном файле вызвать нужный компонент/контейнер из лейаута.
    -->
    <xsl:template match="container[@contains]">
        <xsl:variable name="CONTAINS" select="@contains"/>
        <xsl:apply-templates select="//container[@name=$CONTAINS] | $COMPONENTS[@name=$CONTAINS]">
            <xsl:with-param name="HOLDER_NAME"><xsl:if test="@widget='widget'"><xsl:value-of select="@name"/></xsl:if></xsl:with-param>
        </xsl:apply-templates>
    </xsl:template>

    <!-- Заголовок блока по-умолчанию -->
    <xsl:template match="container | component" mode="block_header">
        <xsl:if test="@title">
            <div class="block_header clearfix">                
                <h2 class="block_title"><xsl:value-of select="@title"/></h2>
            </div>
        </xsl:if>
    </xsl:template>

    <!-- Контент блока по-умолчанию -->
    <xsl:template match="container | component" mode="block_content">
        <xsl:apply-templates select="."/>
    </xsl:template>

    <!-- Рекламный блок в левой колонке -->
    <xsl:template match="container[@name='leftAdBlock']">
        <xsl:if test="$COMPONENTS[@class='Ads']/recordset/record/field[@name='ad_left_250_250'] != ''">
            <div class="left_adblock">
                <xsl:value-of select="$COMPONENTS[@class='Ads']/recordset/record/field[@name='ad_left_250_250']" disable-output-escaping="yes"/>
            </div>
        </xsl:if>
    </xsl:template>
    
</xsl:stylesheet>
