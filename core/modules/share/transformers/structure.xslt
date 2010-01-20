<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <!-- компонент MainMenu -->
    <xsl:template match="component[@class='MainMenu']">
    	<xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@class='MainMenu']]">
        <xsl:if test="not(@empty)">
            <ul class="main_menu">
                <xsl:if test="$DOC_PROPS[@name='default'] != 1">
                    <li>
                        <a href="{$LANG_ABBR}">
                            <xsl:value-of select="$TRANSLATION[@const='TXT_HOME']" disable-output-escaping="yes"/>
                        </a>
                    </li>                    
                </xsl:if>
                <xsl:apply-templates/>
            </ul>
        </xsl:if>        
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='MainMenu']]">
        <li>
            <a href="{$LANG_ABBR}{field[@name='Segment']}"><xsl:value-of select="field[@name='Name']"/></a>
        </li>
    </xsl:template>
    <!-- /компонент MainMenu -->
    
    <!-- компоненты ChildDivisions и BrotherDivisions -->
    <xsl:template match="component[@class='ChildDivisions' or @class='BrotherDivisions']">
        <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@class='ChildDivisions' or @class='BrotherDivisions']]">
        <xsl:if test="not(@empty)">
            <ul class="menu clearfix">
                <xsl:apply-templates/>
            </ul>
        </xsl:if>
    </xsl:template>    

    <xsl:template match="record[ancestor::component[@class='ChildDivisions' or @class='BrotherDivisions']]">
        <li style="clear: both;">
        	<a>
                <xsl:if test="$DOC_PROPS[@name='ID']!=field[@name='Id']">
                    <xsl:attribute name="href">
                        <xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="field[@name='Segment']"/>
                    </xsl:attribute>
                </xsl:if>
        		<xsl:value-of select="field[@name='Name']"/>
        	</a>
            <xsl:if test="field[@name='DescriptionRtf'] != ''">
                <p><xsl:value-of select="field[@name='DescriptionRtf']" disable-output-escaping="yes"/></p>
            </xsl:if>
        </li>
    </xsl:template>
    <!-- /компоненты ChildDivisions и BrotherDivisions -->
    
    <!-- компонент LangSwitcher -->
    <xsl:template match="component[@class='LangSwitcher']">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@class='LangSwitcher']]">
        <ul class="lang_switcher">
            <xsl:apply-templates/>
        </ul>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='LangSwitcher']]">
        <li>
            <xsl:choose>
                <xsl:when test="$LANG_ID = field[@name='lang_id']">
                    <xsl:attribute name="class">current</xsl:attribute>
                    <span><xsl:value-of select="field[@name='lang_name']"/></span>                    
                </xsl:when>
                <xsl:otherwise>
                    <a href="{field[@name='lang_url']}"><xsl:value-of select="field[@name='lang_name']"/></a>                    
                </xsl:otherwise>
            </xsl:choose>
        </li>
    </xsl:template>
    <!-- /компонент LangSwitcher -->
    
    <!-- компонент BreadCrumbs -->
    <xsl:template match="component[@class='BreadCrumbs']">
        <xsl:if test="count(recordset/record) &gt; 1">
            <xsl:apply-templates/>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@class='BreadCrumbs']]">
        <div class="breadcrumbs">
            <xsl:apply-templates/>
        </div>
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@class='BreadCrumbs']]">
        <xsl:if test="field[@name='Id'] != ''">
            <a href="{$BASE}{$LANG_ABBR}{field[@name='Segment']}"><xsl:value-of select="field[@name='Name']"/></a> /
        </xsl:if> 
    </xsl:template>
    
    <xsl:template match="record[position() = last()][ancestor::component[@class='BreadCrumbs']]">
        <xsl:if test="field[@name='Id'] != ''">
            <xsl:value-of select="field[@name='Name']"/>
        </xsl:if>
    </xsl:template>
    <!-- /компонент BreadCrumbs -->
    
    <!-- компонент SitemapTree -->
    <xsl:template match="component[@class='SitemapTree']">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="recordset[ancestor::component[@class='SitemapTree']]">
        <ul>
            <xsl:apply-templates/>
        </ul>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='SitemapTree']]">
        <li>
            <a href="{$BASE}{$LANG_ABBR}{field[@name='Segment']}"><xsl:value-of select="field[@name='Name']"/></a>
            <xsl:apply-templates/>
        </li>
    </xsl:template>

    <xsl:template match="field[ancestor::component[@class='SitemapTree']]"/>
    <!-- /компонент SitemapTree -->
    
    <!-- компонент FileLibrary - файловый репозиторий -->
    <xsl:template match="recordset[parent::component[@class='FileLibrary'][@type='list']]">
        <xsl:variable name="TAB_ID" select="generate-id(record[1])"></xsl:variable>
        <div id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}" file_type="{../@allowed_file_type}">
            <ul class="tabs">
                <li>
                    <a href="#{$TAB_ID}"><xsl:value-of select="record[1]/field[1]/@tabName" /></a>
                </li>
            </ul>
            <div class="paneContainer">
                <div id="{$TAB_ID}">
                    <div class="dirArea">
                        <div class="scrollHelper"></div>
                    </div>
                </div>
            </div>
        </div>
    </xsl:template>
    <!-- /компонент FileLibrary -->

</xsl:stylesheet>