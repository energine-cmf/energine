<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="component[@class='ProductList']">
		<xsl:apply-templates />
</xsl:template>

<xsl:template match="recordset[parent::component[@class='ProductList']]">
    <xsl:if test="record/field[@key='1']!=''">
        <form method="POST" action="" id="{generate-id(.)}" >
            <table class="product" width="100%" border="0">
                <xsl:apply-templates />
            </table>
        </form>
    </xsl:if>
</xsl:template>

<xsl:template match="record[ancestor::component[@class='ProductList'][@type='list']]">
	<tr>
        <xsl:apply-templates select="field[@name='product_name']"/>
    </tr>
    <tr>
        <td style="vertical-align: top;"><xsl:apply-templates select="field[@name='product_thumb_img']"/></td>
        <td style="text-align: justify; vertical-align: top;">
            <xsl:apply-templates select="field[@name='product_short_description_rtf']"/>
            <a href="{$BASE}{$LANG_ABBR}{field[@name='smap_id']/@smap_segment}{field[@name='product_segment']}/"><xsl:value-of select="../../toolbar/control[@id='go']/@title"/></a>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="right"><a href="#" onclick="{generate-id(../../recordset)}.{../../toolbar/control[@id='basket']/@click}({field[@name='product_id']}); return false;"><img src="images/cart_put.gif" width="16" height="16" align="baseline" alt="" border="0"/>&#160;<xsl:value-of select="../../toolbar/control[@id='basket']/@title"/></a></td>
    </tr>
    <tr>
        <td colspan="2">
            <xsl:if test="../../@discount > 0">
                <xsl:apply-templates select="field[@name='product_price_with_discount']"/>
            </xsl:if>
            <xsl:apply-templates select="field[@name='product_price']"/>
            <xsl:apply-templates select="field[@name='ps_id']"/>
        </td>
    </tr>
    <tr>
        <td style="font-size: 0.9em;" colspan="2"><xsl:apply-templates select="field[@name='producer_id']"/></td>
    </tr>
    <tr>
        <td style="font-size: 0.9em;" colspan="2"><xsl:apply-templates select="field[@name='smap_id']"/></td>
    </tr>
    <tr>
        <td style="border-top: 1px solid #000;" colspan="2"><br /></td>
    </tr>
</xsl:template>


<xsl:template match="record[ancestor::component[@class='ProductList'][@type='form']]">    
    <tr>
        <td style="vertical-align: top;" rowspan="3"><xsl:apply-templates select="field[@name='product_thumb_img']"/></td>
        <td style="text-align: justify;"><xsl:value-of select="field[@name='product_description_rtf']" disable-output-escaping="yes"/></td>
    </tr>
    <xsl:if test="count(field[@param])&gt;0">
        <tr>
            <td>
            <table style="width: 100%;" class="bordered">
                <tr>
                    <th colspan="2"><xsl:value-of select="field[@param]/@param"/></th>
                </tr>
                <xsl:apply-templates select="field[@param]"/>
            </table>
            </td>
        </tr>
    </xsl:if>
	<tr>
        <td colspan="2" align="right"><a href="#" onclick="{generate-id(../../recordset)}.{../../toolbar/control[@id='basket']/@click}({field[@name='product_id']}); return false;"><img src="images/cart_put.gif" width="16" height="16" align="baseline" alt="" border="0"/>&#160;<xsl:value-of select="../../toolbar/control[@id='basket']/@title"/></a></td>
    </tr>
    <tr>
        <td colspan="4">
            <xsl:apply-templates select="field[@name='product_price']"/>
            <xsl:apply-templates select="field[@name='ps_id']"/>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="right"><br /><xsl:apply-templates select="field[@name='producer_id']"/></td>
    </tr>
    <tr>
        <td colspan="2" align="right"><xsl:apply-templates select="field[@name='smap_id']"/></td>
    </tr>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductList']][@param]">
	<tr>
        <td style="width: 25%;"><strong><xsl:value-of select="@title"/></strong></td><td style="width: 75%;"><xsl:value-of select="."/></td>
    </tr>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductList'][@type='list']][@name='product_name']">
	<td colspan="2">
        <h3>
            <a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}{../field[@name='product_segment']}/"><xsl:value-of select="."/></a>
        </h3>
    </td>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductList'][@type='list']][@type='htmlblock']">
    <xsl:if test=".!=''">
        <div>
            <xsl:value-of select="." disable-output-escaping="yes" />
        </div>
    </xsl:if>
</xsl:template>
<xsl:template match="field[ancestor::component[@class='ProductList'][@type='list']][@name='product_segment']"/>

<xsl:template match="field[ancestor::component[@class='ProductList'][@type='list']][@key='1']" />

<xsl:template match="field[ancestor::component[@class='ProductList'][@type='form']][@name='product_name']">
	<!-- <td colspan="2"><h3><xsl:value-of select="."/></h3></td> -->
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductList']][@name='product_thumb_img']">
    <xsl:if test=".!=''">
       	<a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}{../field[@name='product_segment']}/" ><img src="{$BASE}{.}" alt="" border="0"/></a><br/>
        <a href="{$BASE}{../field[@name='product_photo_img']}" target="_blank"><img src="{$BASE}images/magnifier.gif" width="16" height="16" border="0" alt=""/></a>
    </xsl:if>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductList'][@type='form']][@name='product_thumb_img']">
    <xsl:if test=".!=''">
       	<a href="{$BASE}{../field[@name='product_photo_img']}" target="_blank"><img src="{$BASE}{.}" alt="" border="0"/></a><br/>
        <a href="{$BASE}{../field[@name='product_photo_img']}" target="_blank"><img src="{$BASE}images/magnifier.gif" width="16" height="16" border="0" alt=""/></a>
    </xsl:if>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductList']][@name='producer_id']">
        <span><xsl:value-of select="@title"/>: </span><a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}manufacturer-{@producer_segment}/"><xsl:value-of select="."/></a>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductList']][@name='smap_id']">
        <span><xsl:value-of select="@title"/>: </span><a href="{$BASE}{$LANG_ABBR}{@smap_segment}"><xsl:value-of select="."/></a>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductList']][@type='float']">
	<div style="margin: 2px 0px; text-align: right;">
        <strong><xsl:value-of select="@title"/>: <span style="padding: 0px 0.5em; background: #070; color: #FFF; font: bold 14px tahoma, sans-serif;"><xsl:value-of select="."/></span></strong>
    </div>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductList']][@name='product_price_with_discount']">
	<div style="margin: 2px 0px; text-align: right;">
        <strong><xsl:value-of select="@title"/>&#160;<xsl:value-of select="format-number(../../../@discount, '#')"/>%: <span style="padding: 0px 0.5em; background: #070; color: #FFF; font: bold 14px tahoma, sans-serif;"><xsl:value-of select="."/></span></strong>
    </div>
</xsl:template>


<xsl:template match="field[ancestor::component[@class='ProductList']][@name='ps_id']">
    <div style="text-align: right; font-weight: bold; color: white;"><span style="background-color: red; margin: 2px 0px; padding: 0 0.5em;"><xsl:value-of select="options/option[@selected]"/></span></div>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductList']][@name='product_photo_img']"/>
<xsl:template match="toolbar[ancestor::component[@class='ProductList']][@name!='pager']" />


<xsl:template match="toolbar[@name='pager'][ancestor::component[@class='ProductList'][@componentAction='showManufacturerProducts']]">
    <xsl:if test="count(control)&gt;1">
        <div class="pager">
        <xsl:if test="properties/property[@name='title']">
            <span><xsl:value-of select="properties/property[@name='title']"/>:</span>
        </xsl:if>
        <xsl:for-each select="control">
            <xsl:if test="@disabled">
                <xsl:if test="position() != 1">
                <span style="margin:0px 5px 0px 5px;">        
                    <a><xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../@template"/>manufacturer-<xsl:value-of select="../../recordset/record/field[@name='producer_id']/@producer_segment"/>/page-<xsl:value-of select="@action"/>/</xsl:attribute><img src="images/prev_page.gif" align="absmiddle" border="0"/></a>
                </span>
                </xsl:if>
            </xsl:if>

            <span style="margin:0px 5px 0px 5px;">
            <xsl:if test="@end_break">... </xsl:if>
            
            <xsl:element name="a">
                <xsl:choose>
                    <xsl:when test="not(@disabled)">
                        <xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../@template"/>manufacturer-<xsl:value-of select="../../recordset/record/field[@name='producer_id']/@producer_segment"/>/page-<xsl:value-of select="@action"/>/</xsl:attribute>                
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:attribute name="style">background-color:#0080FF;color:white; padding:0 3px; padding-bottom:1px;</xsl:attribute>                
                    </xsl:otherwise>
                </xsl:choose>
                <xsl:value-of select="@title"/>
            </xsl:element>
            <xsl:if test="@start_break"> ...</xsl:if>
            </span>
            <xsl:if test="@disabled">
                <xsl:if test="position() != last()">
                    <span style="margin:0px 5px 0px 5px;">        
                        <a><xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../@template"/>manufacturer-<xsl:value-of select="../../recordset/record/field[@name='producer_id']/@producer_segment"/>/page-<xsl:value-of select="@action"/>/</xsl:attribute><img src="images/next_page.gif" align="absmiddle" border="0"/></a>
                    </span>
                </xsl:if>        
            </xsl:if>            
        </xsl:for-each>

        </div>
    </xsl:if>
</xsl:template>

<xsl:template match="recordset[parent::component[@class='ProductDivisions']]">
<xsl:if test="record[1]/field[@name='Id']!=''">
    <ul class="clearfix">
        <xsl:apply-templates/>
    </ul>
</xsl:if>
</xsl:template>

<xsl:template match="record[parent::recordset[parent::component[@class='ProductDivisions']]]">
<xsl:if test="field[@name='Segment']!='/shop/order/'">
    <li style="clear: both;">
        <xsl:element name="a">
            <xsl:if test="field[@name='product_count']&gt;0">
                <xsl:attribute name="href"><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="field[@name='Segment']"/></xsl:attribute>
            </xsl:if>
            <xsl:value-of select="field[@name='Name']"/>
        </xsl:element>
        (<xsl:value-of select="field[@name='product_count']"/>)
        <xsl:if test="field[@name='DescriptionRtf']!=''">
            <div style="padding:2px 0px 2px 5px;">
                <xsl:value-of select="field[@name='DescriptionRtf']" disable-output-escaping="yes"/>
            </div>
        </xsl:if>       
    </li>
</xsl:if>
</xsl:template>

<xsl:template match="searchform">
    <form method="GET" action="{$BASE}{$LANG_ABBR}{../@template}{@action}/">
        <fieldset><legend><xsl:value-of select="@title"/></legend>
            <label for="search_field"><input type="string" name='{search_field/@name}' id="search_field" style="width:300px;" value="{search_field}"/></label><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
            <xsl:element name="input">
            	<xsl:attribute name="type">submit</xsl:attribute>
                <xsl:attribute name="onclick"><![CDATA[if(document.getElementById('search_field').value.length<4)return false;]]></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="search_field/@action_title"/></xsl:attribute>
            </xsl:element>
        </fieldset>
    </form>
</xsl:template>

<xsl:template match="component[@class='ManufacturerList']">
        <xsl:value-of select="$TRANSLATION[@const='FIELD_PRODUCER']"/>:
        <xsl:apply-templates />
</xsl:template>

<xsl:template match="recordset[parent::component[@class='ManufacturerList']]">
    <ul>
        <xsl:apply-templates />
    </ul>
</xsl:template>

<xsl:template match="record[ancestor::component[@class='ManufacturerList']]">
    <li><a href="{$BASE}{$LANG_ABBR}{field[@name='producer_segment']}"><xsl:value-of select="field[@name='producer_name']"/></a></li>
</xsl:template>

</xsl:stylesheet>
