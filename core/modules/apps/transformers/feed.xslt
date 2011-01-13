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

            <ul id="{generate-id(.)}" class="feed">
    			<xsl:choose>
    				<xsl:when test="parent::component[@class='NewsFeed']"><xsl:attribute name="class">feed news</xsl:attribute></xsl:when>    				
    			</xsl:choose>
                <xsl:apply-templates/>
            </ul>        
        
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@exttype='feed'][@type='list']]">
        <li>
            <xsl:if test="$COMPONENTS[@editable]">
                <xsl:attribute name="record"><xsl:value-of select="field[@index='PRI']"/></xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </li>
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@class='NewsFeed'][@type='list']]">
        <li>
            <xsl:if test="$COMPONENTS[@editable]">
                <xsl:attribute name="record"><xsl:value-of select="field[@index='PRI']"/></xsl:attribute>
            </xsl:if>
    		<div class="date"><strong><xsl:value-of select="field[@name='news_date']"/></strong></div>
    		<h4 class="title">
                <a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}{field[@name='news_id']}--{field[@name='news_segment']}/"><xsl:value-of select="field[@name='news_title']"/></a>
    		</h4>
    		<div class="anounce"><xsl:value-of select="field[@name='news_announce_rtf']" disable-output-escaping="yes"/></div>
        </li>
    </xsl:template>
    
    <xsl:template match="toolbar[ancestor::component[@exttype='feed'][@type='list']][@name!='pager']"/>    
    
    <!-- компонент feed в режиме просмотра -->
    <xsl:template match="recordset[parent::component[@exttype='feed'][@type='form']]">
        <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@exttype='feed'][@type='form']]">
        <div class="feed" id="{generate-id(../.)}">
            <xsl:if test="$COMPONENTS[@editable]">
                <xsl:attribute name="current"><xsl:value-of select="field[@index='PRI']"/></xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </div>
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@class='NewsFeed'][@type='form']]">
        <div class="feed news_view" id="{generate-id(../.)}">
            <xsl:if test="$COMPONENTS[@editable]">
                <xsl:attribute name="current"><xsl:value-of select="field[@index='PRI']"/></xsl:attribute>
            </xsl:if>
            <div class="date"><strong><xsl:value-of select="field[@name='news_date']"/></strong></div>
    		<h4 class="title"><xsl:value-of select="field[@name='news_title']"/></h4>
    		<xsl:apply-templates select="field[(@name!='news_date') or (@name!='news_title')]" />
        </div>
    </xsl:template>
    
    <xsl:template match="field[ancestor::component[@exttype='feed'][@type='form']][@type='htmlblock'] | field[ancestor::component[@exttype='feed'][@type='form']][@type='htmlblock']">
        <div><xsl:value-of select="." disable-output-escaping="yes"/></div>
    </xsl:template>
    
    <xsl:template match="component[@exttype='feededitor'][@type='list']">
        <xsl:if test="recordset">
            <xsl:variable name="LINK"><xsl:value-of select="@linkedComponent"/></xsl:variable>
            <ul id="{generate-id(recordset)}" style="display:none;" single_template="{$BASE}{$LANG_ABBR}{@single_template}" linkedTo="{generate-id($COMPONENTS[@name=$LINK]/recordset)}">
                <xsl:for-each select="toolbar/control">
                    <li id="{@id}" title="{@title}" type="{@type}" action="{@onclick}"></li>
                </xsl:for-each>
            </ul>        
        </xsl:if>
    </xsl:template>

    <xsl:template match="field[@type='htmlblock' and ancestor::component[@exttype='feed' and @type='form']]">
           <div class="feed_text">
               <xsl:if test="ancestor::component/@editable">
                   <xsl:attribute name="class">nrgnEditor feed_text</xsl:attribute>
                   <xsl:attribute name="num"><xsl:value-of select="@name"/></xsl:attribute>
                   <xsl:attribute name="single_template"><xsl:value-of select="$BASE"/><xsl:value-of
                               select="$LANG_ABBR"/><xsl:value-of
                               select="ancestor::component/@single_template"/></xsl:attribute>
                       <xsl:attribute name="eID">
                           <xsl:value-of select="../field[@index='PRI']"/>
                       </xsl:attribute>
               </xsl:if>
               <xsl:value-of select="." disable-output-escaping="yes"/>
           </div>
       </xsl:template>
    

</xsl:stylesheet>