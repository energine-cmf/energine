<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <!-- обработка компонента типа feed -->
    <xsl:template match="component[@exttype='feed']">
        <xsl:apply-templates/>
    </xsl:template>
    
    <!-- компонент feed в режиме списка -->
    <xsl:template match="recordset[parent::component[@exttype='feed'][@type='list']]">
        <xsl:if test="not(@empty)">
            <ul id="{generate-id(.)}" class="feed">
                <xsl:apply-templates/>
            </ul>        
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@exttype='feed'][@type='list']]">
        <li>
            <xsl:if test="$COMPONENTS[@editable]">
                <xsl:attribute name="record"><xsl:value-of select="field[@index='PRI']"/></xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </li>
    </xsl:template>
    
    <xsl:template match="toolbar[ancestor::component[@exttype='feed'][@type='list']][@name!='pager']"/>    
    
    <!-- компонент feed в режиме просмотра -->
    <xsl:template match="recordset[parent::component[@exttype='feed'][@type='form']]">
        <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@exttype='feed'][@type='form']]">
        <div class="feed">
            <xsl:apply-templates/>
        </div>
    </xsl:template>
    
    <xsl:template match="field[ancestor::component[@exttype='feed'][@type='form']][@type='htmlblock'] | field[ancestor::component[@exttype='feed'][@type='form']][@type='htmlblock']">
        <div><xsl:value-of select="." disable-output-escaping="yes"/></div>
    </xsl:template>
    
    <xsl:template match="component[@exttype='feededitor'][@type='list']">
        <xsl:if test="recordset">
            <script type="text/javascript">
                var <xsl:value-of select="generate-id(recordset)"/>;
            </script>
            <xsl:variable name="LINK"><xsl:value-of select="@linkedComponent"/></xsl:variable>
            <ul id="{generate-id(recordset)}" style="display:none;" single_template="{$BASE}{$LANG_ABBR}{@single_template}" linkedTo="{generate-id($COMPONENTS[@name=$LINK]/recordset)}">
                <xsl:for-each select="toolbar/control">
                    <li id="{@id}" title="{@title}" type="{@type}" action="{@onclick}"></li>
                </xsl:for-each>
            </ul>        
        </xsl:if>
    </xsl:template>
    
    <!-- компонент NewsFeed -->
    <xsl:template match="component[@class='NewsFeed']">
        <xsl:if test="not(recordset[@empty])">
            <div class="news">
                <xsl:apply-templates/>
            </div>
        </xsl:if>        
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@class='NewsFeed'][@type='list']]">
        <ul id="{generate-id(.)}" class="news_list">
            <xsl:apply-templates/>
        </ul>        
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@class='NewsFeed'][@type='list']]">
        <li>
            <xsl:if test="$COMPONENTS[@editable]">
                <xsl:attribute name="record"><xsl:value-of select="field[@index='PRI']"/></xsl:attribute>
            </xsl:if>
            <div class="date"><strong><xsl:value-of select="field[@name='news_date']"/></strong></div>            
            <h4 class="name">
                <xsl:choose>
                    <xsl:when test="field[@name='news_text_rtf'] = 1">
                        <a href="{$BASE}{$LANG_ABBR}{../../@template}{translate(field[@name='news_date'], '/', '-')}/"><xsl:value-of select="field[@name='news_title']"/></a>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="field[@name='news_title']"/>
                    </xsl:otherwise>
                </xsl:choose>                
            </h4>
            <div class="anounce"><xsl:value-of select="field[@name='news_announce_rtf']" disable-output-escaping="yes"/></div>
        </li>
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@class='NewsFeed'][@type='form']]">
        <div class="news_view">
            <xsl:apply-templates/>
            <div class="go_back"><a href="{$BASE}{$LANG_ABBR}{../@template}"><xsl:value-of select="$TRANSLATION[@const='TXT_BACK_TO_LIST']"/></a></div>
        </div>        
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@class='NewsFeed'][@type='form']]">
        <div class="date"><strong><xsl:value-of select="field[@name='news_date']"/></strong></div>
        <h3 class="name"><xsl:value-of select="field[@name='news_title']"/></h3>
        <div class="text"><xsl:value-of select="field[@name='news_text_rtf']" disable-output-escaping="yes"/></div>        
    </xsl:template>
    <!-- /компонент NewsFeed -->    

</xsl:stylesheet>
