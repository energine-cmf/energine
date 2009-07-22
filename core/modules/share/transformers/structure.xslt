<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="component[@class='MainMenu'] | component[@class='ChildDivisions'] | component[@class='BrotherDivisions'] ">
    	<xsl:apply-templates select="recordset" />
    </xsl:template>

    <xsl:template match="component[@class='ChildDivisions']/recordset | component[@class='BrotherDivisions']/recordset">
        <xsl:if test="not(@empty)">
            <ul class="menu clearfix">				
                <xsl:apply-templates />
            </ul>
        </xsl:if>
    </xsl:template>

    <xsl:template match="component[@class='MainMenu']/recordset">
        <xsl:if test="not(@empty)">
            <ul class="menu">
                <xsl:if test="$DOC_PROPS[@name='default'] !=1">
                    <li>
                        <a href="{$LANG_ABBR}">
                            <xsl:value-of select="../translations/translation[@const='TXT_HOME']" disable-output-escaping="yes" />
                        </a>
                    </li>                    
                </xsl:if>
                <xsl:apply-templates />
            </ul>
        </xsl:if>        
    </xsl:template>

    <xsl:template match="component[@class='MainMenu']/recordset/record">
        <li>
        	<a href="{$LANG_ABBR}{field[@name='Segment']}">
        		<xsl:value-of select="field[@name='Name']" disable-output-escaping="yes" />
        	</a>
        </li>
    </xsl:template>

    <xsl:template match="component[@class='ChildDivisions']/recordset/record | component[@class='BrotherDivisions']/recordset/record">
        <li style="clear: both;">			
        	<a>
                <xsl:if test="$DOC_PROPS[@name='ID']!=field[@name='Id']">
                    <xsl:attribute name="href"><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="field[@name='Segment']"/></xsl:attribute>
                </xsl:if>
        		<xsl:value-of select="field[@name='Name']" disable-output-escaping="yes" />
        	</a>
            <xsl:if test="field[@name='DescriptionRtf'] != ''">
                <p><xsl:value-of select="field[@name='DescriptionRtf']" disable-output-escaping="yes" /></p>
            </xsl:if>   
        </li>
    </xsl:template>

    <xsl:template match="component[@class='BreadCrumbs']">
        <xsl:if test="count(recordset/record) &gt; 1">
            <div id="breadcrumbs">
                <xsl:for-each select="recordset/record">
                    <xsl:if test="field[@name='Id'] != ''">
                        <xsl:choose>
                            <xsl:when test="position() != last()">								
										<a href="{$BASE}{$LANG_ABBR}{field[@name='Segment']}"><xsl:value-of select="field[@name='Name']" disable-output-escaping="yes" /></a>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="field[@name='Name']" disable-output-escaping="yes" />
                            </xsl:otherwise>
                        </xsl:choose>
                        <xsl:if test="position() != last()"> / </xsl:if>
                    </xsl:if>                    
                </xsl:for-each>
            </div>
        </xsl:if>
    </xsl:template>


    <xsl:template match="component[@class='SitemapTree']">
        <xsl:apply-templates />
    </xsl:template>

    <xsl:template match="recordset[ancestor::component[@class='SitemapTree']]">
        <ul>
            <xsl:apply-templates />
        </ul>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='SitemapTree']]">
        <li>
            <a href="{$BASE}{$LANG_ABBR}{field[@name='Segment']}">
                <xsl:value-of select="field[@name='Name']"/>
            </a>
            <xsl:apply-templates />
        </li>
    </xsl:template>

    <xsl:template match="field[ancestor::component[@class='SitemapTree']]" />

</xsl:stylesheet>