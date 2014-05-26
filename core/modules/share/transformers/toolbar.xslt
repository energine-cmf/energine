<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    >

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
    <xsl:template match="toolbar/control[(@type='link') and (@mode != 0) and not(@disabled)]">
        <a href="{$BASE}{$LANG_ABBR}{@click}" id="{@id}">
            <xsl:value-of select="@title"/>
        </a>
    </xsl:template>

    <xsl:template match="toolbar/control[@type='separator']">
        <br/>
    </xsl:template>
    <!-- Панель управления для формы -->
    <xsl:template match="toolbar[parent::component[@exttype='grid']]">
        <script type="text/javascript">
            window.addEvent('domready', function(){
                    componentToolbars['<xsl:value-of select="generate-id(../recordset)"/>'] = new Toolbar('<xsl:value-of select="@name"/>');
                <xsl:apply-templates />
                if(<xsl:value-of select="generate-id(../recordset)"/>)<xsl:value-of select="generate-id(../recordset)"/>.attachToolbar(componentToolbars['<xsl:value-of select="generate-id(../recordset)"/>']);
                var holder = document.id('<xsl:value-of select="generate-id(../recordset)"/>'),
                    content = holder.getElement('.e-pane-content');
                if (content <xsl:text disable-output-escaping="yes">&amp;&amp;</xsl:text> $(document.body).clientWidth.toInt() <xsl:text disable-output-escaping="yes">&lt;</xsl:text>= 680) {
                    var tToolbar = holder.getElement('.e-pane-t-toolbar'),
                        bToolbar = holder.getElement('.e-pane-b-toolbar'),
                        contentHeight = $(document.body).getSize().y;
                    if (tToolbar) contentHeight -= tToolbar.getComputedSize().totalHeight;
                    if (bToolbar) contentHeight -= bToolbar.getComputedSize().totalHeight;
                    content.setStyles({
                        height: contentHeight,
                        position: 'static'
                    });
                }
            });
        </script>
    </xsl:template>    
    
    <xsl:template match="component[@exttype='grid']/toolbar/control[@type = 'button']">
    	componentToolbars['<xsl:value-of select="generate-id(../../recordset)"/>'].appendControl(
            new Toolbar.Button({
                id: '<xsl:value-of select="@id"/>',
                title: '<xsl:value-of select="@title"/>',
                action: '<xsl:value-of select="@onclick"/>',
                icon: '<xsl:value-of select="@icon"/>'
            })
    	);
    </xsl:template>


    <xsl:template match="component[@exttype='grid']/toolbar/control[@type = 'switcher']">
        componentToolbars['<xsl:value-of select="generate-id(../../recordset)"/>'].appendControl(
        new Toolbar.Switcher({
        id: '<xsl:value-of select="@id"/>',
        title: '<xsl:value-of select="@title"/>',
        action: '<xsl:value-of select="@onclick"/>',
        icon: '<xsl:value-of select="@icon"/>'
        })
        );
    </xsl:template>

    <xsl:template match="component[@exttype='grid']/toolbar/control[@type='file']">
    	componentToolbars['<xsl:value-of select="generate-id(../../recordset)"/>'].appendControl(
            new Toolbar.File({
                id: '<xsl:value-of select="@id"/>',
                title: '<xsl:value-of select="@title"/>',
                action: '<xsl:value-of select="@onclick"/>',
                icon: '<xsl:value-of select="@icon"/>'
            })
    	);
    </xsl:template>

    <xsl:template match="component[@exttype='grid']/toolbar/control[@type = 'select']">
        componentToolbars['<xsl:value-of select="generate-id(../../recordset)"/>'].appendControl(
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
  
    <xsl:template match="component[@exttype='grid']/toolbar/control[@type = 'separator']">
        componentToolbars['<xsl:value-of select="generate-id(../../recordset)"/>'].appendControl(
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
        <span class="control">
            <a>
                <xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../@template"/><xsl:value-of select="../properties/property[@name='additional_url']"/>page-<xsl:value-of select="@action"/>/<xsl:if test="../properties/property[@name='get_string']!=''">?<xsl:value-of select="../properties/property[@name='get_string']"/></xsl:if></xsl:attribute>                            
                <xsl:value-of select="@title"/>
            </a>
        </span>
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
        <span class="control current"><xsl:value-of select="@title"/></span>
        <xsl:if test="following-sibling::control">
            <span class="control arrow">
                <a>
                    <xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../@template"/><xsl:value-of select="../properties/property[@name='additional_url']"/>page-<xsl:value-of select="@action + 1"/>/<xsl:if test="../properties/property[@name='get_string']!=''">?<xsl:value-of select="../properties/property[@name='get_string']"/></xsl:if></xsl:attribute>
                    <img src="images/next_page.gif"/>
                </a>
            </span>
        </xsl:if>
    </xsl:template>
    
    <!-- разделитель между группами цифр -->
    <xsl:template match="control[@type='separator'][parent::toolbar[@name='pager']]">
        <span class="control break">...</span>
    </xsl:template>
    
    <xsl:template match="properties[parent::toolbar[@name='pager']]"/>
    
    <xsl:template match="property[@name='title'][ancestor::toolbar[@name='pager']]">
        <span class="title"><xsl:value-of select="."/>:</span>
    </xsl:template>
    <!-- /листалка по страницам -->     
    
    <!-- Панель управления страницей обрабатывается в document.xslt  -->
    <xsl:template match="toolbar[parent::component[@class='PageToolBar']]"/>
    <!-- Те действия на которые нет прав  - прячем -->
    <xsl:template match="toolbar/control[@mode=0]"></xsl:template>
    <!--Равно как и те действия права на которые есть, но по каким то причинам их делать нельзя-->
    <xsl:template match="toolbar/control[@disabled]"></xsl:template>
</xsl:stylesheet>