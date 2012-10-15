<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">

    <!-- компоненты PageList и NavigationMenu -->
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
        <li class="menu_item">
            <h4 class="menu_name">
                <a>
                    <xsl:if test="$DOC_PROPS[@name='ID']!=field[@name='Id']">
                        <xsl:attribute name="href">
                            <xsl:choose>
                                <xsl:when test="field[@name='Redirect']=''"><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="field[@name='Segment']"/></xsl:when>
                                <xsl:otherwise><xsl:value-of select="field[@name='Redirect']"/></xsl:otherwise>
                            </xsl:choose>
                        </xsl:attribute>
                    </xsl:if>
                    <xsl:value-of select="field[@name='Name']"/>
                </a>
            </h4>
            <xsl:if test="field[@name='attachments']/recordset">
                <div class="menu_image">
                    <a>
                        <xsl:if test="$DOC_PROPS[@name='ID']!=field[@name='Id']">
                            <xsl:attribute name="href">
                                <xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="field[@name='Segment']"/>
                            </xsl:attribute>
                        </xsl:if>
                        <xsl:apply-templates select="field[@name='attachments']" mode="preview">
                            <xsl:with-param name="PREVIEW_WIDTH">90</xsl:with-param>
                            <xsl:with-param name="PREVIEW_HEIGHT">68</xsl:with-param>
                        </xsl:apply-templates>
                    </a>
                </div>
            </xsl:if>
            <xsl:if test="field[@name='DescriptionRtf'] != ''">
                <div class="menu_announce">
                    <xsl:value-of select="field[@name='DescriptionRtf']" disable-output-escaping="yes"/>
                </div>
            </xsl:if>
            <xsl:if test="recordset">
                <xsl:apply-templates/>
            </xsl:if>
        </li>
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@class='NavigationMenu']]">
        <li class="menu_item">
            <a>
                <xsl:if test="$DOC_PROPS[@name='ID']!=field[@name='Id']">
                    <xsl:attribute name="href">
                                <xsl:choose>
                                    <xsl:when test="field[@name='Redirect']=''"><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="field[@name='Segment']"/></xsl:when>
                                    <xsl:otherwise><xsl:value-of select="field[@name='Redirect']"/></xsl:otherwise>
                                </xsl:choose>
                            </xsl:attribute>
                </xsl:if>
                <xsl:value-of select="field[@name='Name']"/>
            </a>
            <xsl:apply-templates select="recordset"/>
        </li>
    </xsl:template>
    <!-- /компоненты PageList и NavigationMenu -->
    
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
                    <li class="home">
                        <a href="{$BASE}">
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
            <a>
                <xsl:choose>
                    <xsl:when test="field[@name='Id']!=$ID">
                        <xsl:attribute name="href"><xsl:choose>
                                    <xsl:when test="field[@name='Redirect']=''"><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="field[@name='Segment']"/></xsl:when>
                                    <xsl:otherwise><xsl:value-of select="field[@name='Redirect']"/></xsl:otherwise>
                                </xsl:choose></xsl:attribute>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:attribute name="class">active</xsl:attribute>
                    </xsl:otherwise>
                </xsl:choose>
             <xsl:value-of select="field[@name='Name']"/></a>
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
            <a>
                <xsl:if test="$LANG_ID != field[@name='lang_id']">
                    <xsl:attribute name="href"><xsl:value-of select="field[@name='lang_url']"/></xsl:attribute>
                </xsl:if>
                <xsl:value-of select="field[@name='lang_name']"/>
            </a>    
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

    <!-- компонент PageMedia -->
    <xsl:template match="component[@class='PageMedia']">
        <xsl:if test="recordset/record[1]/field[@name='attachments']/recordset">
            <div class="media_box">
                <xsl:apply-templates select="recordset/record[1]/field[@name='attachments']" mode="player">
                    <xsl:with-param name="PLAYER_WIDTH">664</xsl:with-param>
                    <xsl:with-param name="PLAYER_HEIGHT">498</xsl:with-param>
                </xsl:apply-templates>
                <xsl:apply-templates select="recordset/record[1]/field[@name='attachments']" mode="carousel">
                    <xsl:with-param name="PREVIEW_WIDTH">90</xsl:with-param>
                    <xsl:with-param name="PREVIEW_HEIGHT">68</xsl:with-param>
                </xsl:apply-templates>
            </div>
        </xsl:if>
    </xsl:template>
    <!-- /компонент PageMedia -->

</xsl:stylesheet>