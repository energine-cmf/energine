<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="component[@exttype='feed']">
    <xsl:apply-templates />
</xsl:template>

<xsl:template match="recordset[parent::component[@exttype='feed'][@type='list']]">
    <xsl:if test="not(@empty)">
        <ul id="{generate-id(.)}">
			<xsl:choose>
				<xsl:when test="parent::component[@class='NewsFeed']">	<xsl:attribute name="class">news</xsl:attribute></xsl:when>
				<xsl:otherwise></xsl:otherwise>
			</xsl:choose>
            <xsl:apply-templates />
        </ul>        
    </xsl:if>
</xsl:template>

<xsl:template match="record[ancestor::component[@exttype='feed'][@type='list']]">
    <li>
        <xsl:if test="$COMPONENTS[@editable]">
            <xsl:attribute name="record"><xsl:value-of select="field[@index='PRI']"/></xsl:attribute>
        </xsl:if>
        <xsl:apply-templates />        
    </li>
</xsl:template>
<xsl:template match="record[ancestor::component[@class='NewsFeed'][@type='list']]">
    <li>
        <xsl:if test="$COMPONENTS[@editable]">
            <xsl:attribute name="record"><xsl:value-of select="field[@index='PRI']"/></xsl:attribute>
        </xsl:if>
		<div class="date"><strong><xsl:value-of select="field[@name='news_date']" /></strong></div>
		<h4>
            <a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}{translate(field[@name='news_date'], '/', '-')}/"><xsl:value-of select="field[@name='news_title']" /></a>
		</h4>
		<div class="anounce"><xsl:value-of select="field[@name='news_announce_rtf']" disable-output-escaping="yes" /></div>
    </li>
</xsl:template>


<xsl:template match="toolbar[ancestor::component[@exttype='feed'][@type='list']][@name!='pager']" />


<xsl:template match="recordset[parent::component[@exttype='feed'][@type='form']]">
    <xsl:apply-templates />
</xsl:template>

<xsl:template match="record[ancestor::component[@exttype='feed'][@type='form']]">
    <div class="textbox">
        <xsl:apply-templates />
    </div>
</xsl:template>

<xsl:template match="record[ancestor::component[@class='NewsFeed'][@type='form']]">
    <div class="news_view">
        <div class="date"><strong><xsl:value-of select="field[@name='news_date']" /></strong></div>
		<h4><xsl:value-of select="field[@name='news_title']" /></h4>
		<div class="text"><xsl:value-of select="field[@name='news_text_rtf']" disable-output-escaping="yes" /></div>
    </div>
</xsl:template>

<xsl:template match="field[ancestor::component[@exttype='feed'][@type='form']][@type='htmlblock'] | field[ancestor::component[@exttype='feed'][@type='form']][@type='htmlblock']">
    <div><xsl:value-of select="."  disable-output-escaping="yes"/></div>
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


</xsl:stylesheet>