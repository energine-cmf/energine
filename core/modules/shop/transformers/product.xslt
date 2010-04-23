<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="component[@class='ProductList']">
		<xsl:apply-templates/>
</xsl:template>

<xsl:template match="recordset[parent::component[@class='ProductList']]">
    <xsl:choose>
        <xsl:when test="record/field[@key='1'] = ''">
            <xsl:choose>
                <xsl:when test="../@componentAction='search'">
                    <p><xsl:value-of select="$TRANSLATION[@const='TXT_NO_PRODUCTS_FOUND']"/></p>
                </xsl:when>
                <xsl:otherwise>
                    <p><xsl:value-of select="$TRANSLATION[@const='TXT_NO_PRODUCTS']"/></p>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:when>
        <xsl:otherwise>
            <script>
                stopEvent = function(event) {
                    // stop event
                    event = event || new Event(window.event);
                    if (event.stopPropagation) event.stopPropagation();
                    else event.cancelBubble = true;
                    if (event.preventDefault) event.preventDefault();
                    else event.returnValue = false;
                }
            </script>
            <form method="POST" action="" id="{generate-id(.)}">
                <table class="product" width="100%" border="0">
                    <xsl:apply-templates/>
                </table>
            </form>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template match="record[ancestor::component[@class='ProductList'][@type='list']]">
	<tr>
        <xsl:apply-templates select="field[@name='product_name']"/>
    </tr>
    <tr>
        <td style="vertical-align: top; padding-right: 10px;">
            <xsl:if test="field[@name='product_images']/recordset">
                <xsl:apply-templates select="field[@name='product_images']/recordset/record[1]/field[@name='upl_path']"/>
            </xsl:if>
        </td>
        <td style="width: 100%; text-align: justify; vertical-align: top;">
            <xsl:apply-templates select="field[@name='product_short_description_rtf']"/>
            <a href="{$BASE}{$LANG_ABBR}{field[@name='smap_id']/@smap_segment}{field[@name='product_segment']}/"><xsl:value-of select="../../toolbar/control[@id='go']/@title"/></a>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="right"><a href="#" onclick="stopEvent(event); {generate-id(../../recordset)}.{../../toolbar/control[@id='basket']/@click}({field[@name='product_id']});"><img src="images/cart_put.gif" width="16" height="16" align="baseline" alt="" border="0"/>&#160;<xsl:value-of select="../../toolbar/control[@id='basket']/@title"/></a></td>
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
        <td style="border-top: 1px solid #000;" colspan="2"><br/></td>
    </tr>
</xsl:template>


<xsl:template match="record[ancestor::component[@class='ProductList'][@type='form']]">    
    <tr>
        <td style="vertical-align: top; padding-right: 10px;" rowspan="3" class="product_image">
            <xsl:if test="field[@name='product_images']/recordset">
                <ul>
                    <xsl:for-each select="field[@name='product_images']/recordset/record">
                        <li><xsl:apply-templates select="field[@name='upl_path']"/></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>
        </td>
        <td style="text-align: justify; vertical-align: top; width: 100%;"><xsl:value-of select="field[@name='product_description_rtf']" disable-output-escaping="yes"/></td>
    </tr>
    <xsl:if test="count(field[@param])&gt;0">
        <tr>
            <td style="padding-top: 1em;">
                <table width="100%" cellspacing="0" class="lined_table">
                    <thead>
                        <tr>
                            <th colspan="2"><xsl:value-of select="field[@param]/@param"/></th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:apply-templates select="field[@param]"/>
                    </tbody>
                </table>
            </td>
        </tr>
    </xsl:if>
	<tr>
        <td colspan="2" align="right"><a href="#" onclick="stopEvent(event); {generate-id(../../recordset)}.{../../toolbar/control[@id='basket']/@click}({field[@name='product_id']});"><img src="images/cart_put.gif" width="16" height="16" align="baseline" alt="" border="0"/>&#160;<xsl:value-of select="../../toolbar/control[@id='basket']/@title"/></a></td>
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
        <xsl:if test="position() div 2 = floor(position() div 2)">
            <xsl:attribute name="class">odd</xsl:attribute>
        </xsl:if>
        <td style="width: 25%;"><strong><xsl:value-of select="@title"/></strong></td><td style="width: 75%;"><xsl:value-of select="."/></td>
    </tr>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductList'][@type='list']][@name='product_name']">
	<td colspan="2">
        <h3>
            <a href="{$BASE}{$LANG_ABBR}{../field[@name='smap_id']/@smap_segment}{../field[@name='product_segment']}/"><xsl:value-of select="."/></a>
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


<xsl:template match="field[ancestor::component[@class='ProductList']][@name='producer_id']">
        <xsl:if test=".!=''">
            <span><xsl:value-of select="@title"/>: </span><a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}manufacturer-{@producer_segment}/"><xsl:value-of select="."/></a>
        </xsl:if>
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

<xsl:template match="toolbar[ancestor::component[@class='ProductList']][@name!='pager']"/>

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
        <a>
            <xsl:if test="field[@name='product_count']&gt;0">
                <xsl:attribute name="href"><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="field[@name='Segment']"/></xsl:attribute>
            </xsl:if>
            <xsl:value-of select="field[@name='Name']"/>
        </a>
        (<xsl:value-of select="field[@name='product_count']"/>)
        <xsl:if test="field[@name='DescriptionRtf']!=''">
            <div style="padding: 2px 0px 2px 5px;">
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
            <input type="submit" value="{search_field/@action_title}">
                <xsl:attribute name="onclick"><![CDATA[if(document.getElementById('search_field').value.length<4)stopEvent(event);]]></xsl:attribute>                
            </input>
        </fieldset>
    </form>
</xsl:template>

<xsl:template match="component[@class='ManufacturerList']">
        <xsl:value-of select="$TRANSLATION[@const='FIELD_PRODUCER']"/>:
        <xsl:apply-templates/>
</xsl:template>

<xsl:template match="recordset[parent::component[@class='ManufacturerList']]">
    <ul>
        <xsl:apply-templates/>
    </ul>
</xsl:template>

<xsl:template match="record[ancestor::component[@class='ManufacturerList']]">
    <li><a href="{$BASE}{$LANG_ABBR}{field[@name='producer_segment']}"><xsl:value-of select="field[@name='producer_name']"/></a></li>
</xsl:template>

    <!-- pager для списка продуктов, который отфильтрован по производителю -->
    <xsl:template match="control[parent::toolbar[@name='pager'][parent::component[@class='ProductList'][@componentAction='showManufacturerProducts']]]">
        <span class="control">
            <a>
                <xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../@template"/>manufacturer-<xsl:value-of select="../../recordset/record/field[@name='producer_id']/@producer_segment"/>/<xsl:value-of select="../properties/property[@name='additional_url']"/>page-<xsl:value-of select="@action"/>/<xsl:if test="../properties/property[@name='get_string']!=''">?<xsl:value-of select="../properties/property[@name='get_string']"/></xsl:if></xsl:attribute>                            
                <xsl:value-of select="@title"/>
            </a>
        </span>
    </xsl:template>
    
    <xsl:template match="control[@disabled][parent::toolbar[@name='pager'][parent::component[@class='ProductList'][@componentAction='showManufacturerProducts']]]">
        <xsl:if test="preceding-sibling::control">
            <span class="control arrow">
                <a>
                    <xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../@template"/>manufacturer-<xsl:value-of select="../../recordset/record/field[@name='producer_id']/@producer_segment"/>/<xsl:value-of select="../properties/property[@name='additional_url']"/>page-<xsl:value-of select="@action - 1"/>/<xsl:if test="../properties/property[@name='get_string']!=''">?<xsl:value-of select="../properties/property[@name='get_string']"/></xsl:if></xsl:attribute>
                    <img src="images/prev_page.gif"/>
                </a>
            </span>
        </xsl:if>
        <span class="control current"><xsl:value-of select="@title"/></span>
        <xsl:if test="following-sibling::control">
            <span class="control arrow">
                <a>
                    <xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../@template"/>manufacturer-<xsl:value-of select="../../recordset/record/field[@name='producer_id']/@producer_segment"/>/<xsl:value-of select="../properties/property[@name='additional_url']"/>page-<xsl:value-of select="@action + 1"/>/<xsl:if test="../properties/property[@name='get_string']!=''">?<xsl:value-of select="../properties/property[@name='get_string']"/></xsl:if></xsl:attribute>
                    <img src="images/next_page.gif"/>
                </a>
            </span>
        </xsl:if>
    </xsl:template>
     

</xsl:stylesheet>
