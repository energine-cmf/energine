<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"

        version="1.0">
    <!--
    Контейнер может иметь следующие атрибуты:
	1) name (значение: *) - уникальное имя контейнера, обязательный атрибут, значение используется для виджетов и колонок в специальных атрибутах, для JavaScript
	2) html_class (значение: *) - контейнер с таким атрибутом будет выведен как div с указанным классом
	3) column (значение: column) - указывает, что контейнер является колонкой, т.е. содержит набор виджетов, контейнер с таким атрибутом будет выведен для администратора как div с атрибутом column="name", обязателен для работы лейаут-менеджера
	4) widget (значение: widget | static) - указывает, что контейнер является виджетом, т.е. целостным блоком, который можно перетащить в другое место на странице, контейнер с таким атрибутом будет выведен для администратора как div с атрибутом widget="name" и классом class="e-widget", обязателен для работы лейаут-менеджера
		widget - виджет, доступный для перемещения и удаления, а также для редактирования параметров
		static - виджет, доступный только для редактирования параметров, применяется для тех виджетов, которые всегда должны быть на одном и том же месте
	5) block (значение: alfa | beta) - указывает, что контейнер является "блоком", т.е. визуально целостным объектом, служит именно для визуального оформления, контейнер с таким атрибутом будет выведен как кусок html-кода, создающий нужное форматирование
		alfa - блок, который является "главным" на странице
		beta - любой другой блок
    -->

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
        <xsl:if test="($COMPONENTS[@name='adminPanel']) or (@block='alfa') or (component[not(@sample='TextBlock') and not(recordset[@empty])]) or (component[@sample='TextBlock' and (@editable or recordset/record/field != '')])">
            <div>
                <xsl:attribute name="class">block<xsl:if test="@block='alfa'"> alfa_block</xsl:if><xsl:if test="@html_class"><xsl:text> </xsl:text><xsl:value-of select="@html_class"/></xsl:if><xsl:if test="@widget and $COMPONENTS[@name='adminPanel']"> e-widget</xsl:if></xsl:attribute>
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
                <xsl:apply-templates select="." mode="block_header"/>
                <xsl:apply-templates select="." mode="block_content"/>
            </div>
        </xsl:if>
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
    <xsl:template match="container[@block]" mode="block_header">
        <xsl:variable name="MAIN_COMPONENT" select="component[1]"/>
        <xsl:if test="$MAIN_COMPONENT/@title">
            <div class="block_header clearfix">                
                <h2 class="block_title"><xsl:value-of select="$MAIN_COMPONENT/@title" disable-output-escaping="yes"/></h2>
            </div>
        </xsl:if>
    </xsl:template>

    <!-- Контент блока по-умолчанию -->
    <xsl:template match="container[@block]" mode="block_content">
        <div class="block_content clearfix">
            <xsl:apply-templates/>
        </div>
    </xsl:template>

    <!-- Заголовок alfa-блока -->
    <xsl:template match="container[@block='alfa']" mode="block_header">
        <xsl:if test="$DOC_PROPS[@name='default'] != 1">
            <div class="block_header clearfix">
                <h1 class="block_title"><xsl:value-of select="$DOC_PROPS[@name='title']" disable-output-escaping="yes"/></h1>
            </div>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>
