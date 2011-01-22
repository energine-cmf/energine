<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">

    <xsl:template match="component[@class='PageList' or @class='NavigationMenu']">
        <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="recordset[ancestor::component[@class='PageList'] or ancestor::component[@class='NavigationMenu']]">
        <xsl:if test="not(@empty)">
            <ul class="menu clearfix">
                <xsl:apply-templates/>
            </ul>
        </xsl:if>
    </xsl:template>    

    <xsl:template match="record[ancestor::component[@class='PageList']]">
        <li style="clear: both;">
            <xsl:apply-templates select="field[@name='attachments']"/>
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
            <xsl:if test="recordset">
                <xsl:apply-templates />
            </xsl:if>
        </li>
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@class='NavigationMenu']]">
        <li style="clear: both;">
            <a>
                <xsl:if test="$DOC_PROPS[@name='ID']!=field[@name='Id']">
                    <xsl:attribute name="href">
                        <xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="field[@name='Segment']"/>
                    </xsl:attribute>
                </xsl:if>
                <xsl:value-of select="field[@name='Name']"/>
            </a>
            <xsl:apply-templates select="recordset"></xsl:apply-templates>
        </li>
    </xsl:template>
    
    <!-- компонент MainMenu -->
    <xsl:template match="component[@name='mainMenu']">
    	<xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="recordset[ancestor::component[@name='mainMenu']]">
        <xsl:if test="not(@empty)">
            <ul class="main_menu">
                <xsl:apply-templates/>
            </ul>
        </xsl:if>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@name='mainMenu']]">
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

    <xsl:template match="record[ancestor::component[@name='mainMenu']]">
        <li>
            <a href="{$LANG_ABBR}{field[@name='Segment']}"><xsl:value-of select="field[@name='Name']"/></a>
            <xsl:if test="recordset">
                <xsl:apply-templates select="recordset"/>
            </xsl:if>
        </li>
    </xsl:template>
    <!-- /компонент MainMenu -->
    


    
    <!-- компонент LangSwitcher -->
    <xsl:template match="component[@class='LangSwitcher']">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@class='LangSwitcher']]">
        <xsl:if test="count(record)&gt;1">
            <ul class="lang_switcher">
                <xsl:apply-templates/>
            </ul>
        </xsl:if>
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
    
</xsl:stylesheet>