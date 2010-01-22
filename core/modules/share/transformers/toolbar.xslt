<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">

    <!-- Шаблоны панели управления -->
    
    <!-- Собственно панель управления -->
    <xsl:template match="toolbar">
        <xsl:apply-templates/>
    </xsl:template>
    
    <!-- Элемент панели управления -->
    <xsl:template match="toolbar/control">
    	<xsl:variable name="CONTROL">
    		<xsl:choose>
    			<xsl:when test="@type = 'button'">button</xsl:when>
    			<xsl:when test="@type = 'submit'">button</xsl:when>
    			<xsl:otherwise>button</xsl:otherwise>
    		</xsl:choose>
    	</xsl:variable>
    	<xsl:variable name="CONTROL_TYPE">
    		<xsl:choose>
    			<xsl:when test="@type = 'button'">button</xsl:when>
    			<xsl:when test="@type = 'submit'">submit</xsl:when>
    			<xsl:otherwise>button</xsl:otherwise>
    		</xsl:choose>
    	</xsl:variable>
    
    	<xsl:element name="{$CONTROL}">
            <xsl:if test="@mode=1">
                <xsl:attribute name="disabled">disabled</xsl:attribute>
            </xsl:if>
    		<xsl:attribute name="name"><xsl:value-of select="@id"/></xsl:attribute>
    		<xsl:attribute name="title"><xsl:value-of select="@tooltip"/></xsl:attribute>
    		<xsl:attribute name="type"><xsl:value-of select="$CONTROL_TYPE"/></xsl:attribute>
            <xsl:if test="@click!=''">
                <xsl:attribute name="onclick"><xsl:value-of select="@click"/></xsl:attribute>
            </xsl:if>
    		<xsl:value-of select="@title"/>
    	</xsl:element>
    </xsl:template>
    
    <xsl:template match="toolbar/control[@type='link']">
        <xsl:if test="@mode != 0">
            <a href="{$BASE}{$LANG_ABBR}{@click}">
                <xsl:value-of select="@title"/>
            </a>
        </xsl:if>
    </xsl:template>
    
    <!-- Панель управления для формы -->
    <xsl:template match="toolbar[parent::component[@exttype='grid'][@type='form'] or parent::component[@exttype='grid'][@type='list']]">
    	<script type="text/javascript">
         var toolbar_<xsl:value-of select="generate-id(../recordset)"/>;
    	    window.addEvent('domready', function(){
                toolbar_<xsl:value-of select="generate-id(../recordset)"/> = new Toolbar('<xsl:value-of select="@name"/>');
    	        <xsl:apply-templates />
                <xsl:value-of select="generate-id(../recordset)"/>.attachToolbar(toolbar_<xsl:value-of select="generate-id(../recordset)"/>);
                toolbar_<xsl:value-of select="generate-id(../recordset)"/>.bindTo(<xsl:value-of select="generate-id(../recordset)"/>);
            });
    	</script>
    </xsl:template>    
    
    <xsl:template match="component[@exttype='grid']/toolbar/control[@type = 'button']">
    	toolbar_<xsl:value-of select="generate-id(../../recordset)"/>.appendControl(
            new Toolbar.Button({
                id: '<xsl:value-of select="@id"/>',
                title: '<xsl:value-of select="@title"/>',
                action: '<xsl:value-of select="@onclick"/>',
                icon: '<xsl:value-of select="@icon"/>'
            })
    	);
    </xsl:template>
    
    <xsl:template match="component[@exttype='grid']/toolbar/control[@type = 'select']">
        toolbar_<xsl:value-of select="generate-id(../../recordset)"/>.appendControl(
            new Toolbar.Select({
                id: '<xsl:value-of select="@id"/>',
                title: '<xsl:value-of select="@title"/>',
                action: '<xsl:value-of select="@action"/>'
            },
            {
                <xsl:if test="options">
                    <xsl:for-each select="options/option">
                        '<xsl:value-of select="@id"/>':'<xsl:value-of select="."/>'<xsl:if test="position()!=last()">,</xsl:if>
                    </xsl:for-each>
                </xsl:if>
            })
        );
    </xsl:template>
    <!--
    <xsl:template match="component[@exttype='grid']/toolbar/control[@type = 'checkbox']">
        toolbar_<xsl:value-of select="generate-id(../../recordset)"/>.appendControl(
            new Toolbar.Checkbox({
                id: '<xsl:value-of select="@id"/>',
                title: '<xsl:value-of select="@title"/>',
                action: '<xsl:value-of select="@onclick"/>'
            })
        );
    </xsl:template>
    -->
    <!--
    <xsl:template match="component[@exttype='grid']/toolbar/control[@type = 'switcher']">
        toolbar_<xsl:value-of select="generate-id(../../recordset)"/>.appendControl(
            new Toolbar.Switcher({
                id: '<xsl:value-of select="@id"/>',
                title: '<xsl:value-of select="@title"/>',
                action: '<xsl:value-of select="@onclick"/>',
                icon: '<xsl:value-of select="@icon"/>'
            })
        );
    </xsl:template>
    -->    
    <xsl:template match="component[@exttype='grid']/toolbar/control[@type = 'separator']">
    	toolbar_<xsl:value-of select="generate-id(../../recordset)"/>.appendControl(
            new Toolbar.Separator({ id: '<xsl:value-of select="@id"/>' })
    	);
    </xsl:template>
    
    <!-- листалка по страницам -->
    <xsl:template match="toolbar[@name='pager']">
        <xsl:if test="count(control)&gt;1">
            <div class="pager">
                <xsl:apply-templates select="properties/property[@name='title']"/>
                <xsl:apply-templates/>    
            </div>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="control[parent::toolbar[@name='pager']]">
        <xsl:if test="@end_break">
            <span class="control break">...</span>
        </xsl:if>
        <span class="control">
            <a>
                <xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../@template"/><xsl:value-of select="../properties/property[@name='additional_url']"/>page-<xsl:value-of select="@action"/>/<xsl:if test="../properties/property[@name='get_string']!=''">?<xsl:value-of select="../properties/property[@name='get_string']"/></xsl:if></xsl:attribute>                            
                <xsl:value-of select="@tooltip"/>
            </a>
        </span>
        <xsl:if test="@start_break">
            <span class="control break">...</span>
        </xsl:if>
    </xsl:template>
    
    <!-- номер текущей страницы выделен -->
    <xsl:template match="control[@disabled][parent::toolbar[@name='pager']]">
        <xsl:if test="preceding-sibling::control">
            <span class="control arrow">
                <a>
                    <xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../@template"/><xsl:value-of select="../properties/property[@name='additional_url']"/>page-<xsl:value-of select="@action - 1"/>/<xsl:if test="../properties/property[@name='get_string']!=''">?<xsl:value-of select="../properties/property[@name='get_string']"/></xsl:if></xsl:attribute>
                    <img src="images/prev_page.gif"/>
                </a>
            </span>
        </xsl:if>
        <xsl:if test="@end_break">
            <span class="control break">...</span>
        </xsl:if>
        <span class="control current">
            <xsl:value-of select="@tooltip"/>
        </span>
        <xsl:if test="@start_break">
            <span class="control break">...</span>
        </xsl:if>
        <xsl:if test="following-sibling::control">
            <span class="control arrow">
                <a>
                    <xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../@template"/><xsl:value-of select="../properties/property[@name='additional_url']"/>page-<xsl:value-of select="@action + 1"/>/<xsl:if test="../properties/property[@name='get_string']!=''">?<xsl:value-of select="../properties/property[@name='get_string']"/></xsl:if></xsl:attribute>
                    <img src="images/next_page.gif"/>
                </a>
            </span>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="properties[parent::toolbar[@name='pager']]"/>
    
    <xsl:template match="property[@name='title'][ancestor::toolbar[@name='pager']]">
        <span class="title"><xsl:value-of select="."/>:</span>
    </xsl:template>
    <!-- /листалка по страницам -->     
    
    <!-- Панель управления страницей обрабатывается в document.xslt  -->
    <xsl:template match="toolbar[parent::component[@class='PageToolBar']]"/>

<!-- Те действия на которые нет прав  - прячем -->
<xsl:template match="control[@mode=0]"></xsl:template>
</xsl:stylesheet>