<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

    <xsl:template match="component[@class='BasketForm']">
		<xsl:choose>
			<xsl:when test="@summ">
				<div id="{generate-id(recordset)}">
					<xsl:value-of select="@title" />
					<form method="POST" action="">
					<xsl:if test="not(recordset/@empty)">                
							<xsl:apply-templates />                
					</xsl:if>
					</form>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<xsl:if test="recordset/@empty">
					<p><xsl:value-of select="translations/translation[@const='TXT_BASKET_EMPTY']" /></p>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@class='BasketForm'][@type='list']]">
		<table width="100%" border="1" cellpadding="2" cellspacing="0" class="basket">
			<thead>
				<tr>
					<xsl:for-each select="record[1]/field[not(@name='product_id') and not(@name='product_segment') and not(@name='product_thumb_img')]">
						<th>
						<xsl:choose>
							<xsl:when test="@name='basket_id'">
								...
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="@title"/>
							</xsl:otherwise>
						</xsl:choose>
						</th>
					</xsl:for-each>
				</tr>
			</thead>
			<tbody>
				<xsl:apply-templates />
			</tbody>
			<tfoot>
			<tr>
				<td style="text-align: right;" colspan="4"><strong><xsl:value-of select="../translations/translation[@const='TXT_BASKET_SUMM']"/></strong>: </td><td style="text-align: right;"><xsl:value-of select="../@summ"/></td>
			</tr>
			<xsl:if test="../@discount > 0">
				<tr><td style="text-align: right;" colspan="4"><strong><xsl:value-of select="../translations/translation[@const='TXT_BASKET_SUMM_WITH_DISCOUNT']"/>&#160;<xsl:value-of select="format-number(../@discount, '#')"/>%</strong>: </td><td style="text-align: right;"><xsl:value-of select="../@summ_with_discount"/></td></tr>
			</xsl:if>
			</tfoot>
		</table>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='BasketForm'][@type='list']]">		
        <tr>
			<xsl:if test="(position() div 2) != ceiling(position() div 2)">
				<xsl:attribute name="class">odd</xsl:attribute>
			</xsl:if>
            <xsl:apply-templates />
        </tr>
    </xsl:template>

<xsl:template match="field[ancestor::component[@class='BasketForm'][@type='list']]">
	<td><xsl:value-of select="."/></td>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='BasketForm'][@type='list']][@name='product_name']">
    <td width="50%">
		<xsl:if test="../field[@name='product_thumb_img']">
			<a href="{$BASE}{$LANG_ABBR}shop/{../field[@name='product_segment']}/"><img src="{$BASE}{../field[@name='product_thumb_img']}" border="0" style="float: left; margin: 0 5px 0 0; border: 1px solid #eee;" /></a>
		</xsl:if>		
		<a href="{$BASE}{$LANG_ABBR}shop/{../field[@name='product_segment']}/"><xsl:value-of select="."/></a>	
	</td>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='BasketForm'][@type='list']][@name='product_id']"></xsl:template>
<xsl:template match="field[ancestor::component[@class='BasketForm'][@type='list']][@name='product_thumb_img']"></xsl:template>
<xsl:template match="field[ancestor::component[@class='BasketForm'][@type='list']][@name='product_segment']"></xsl:template>
<xsl:template match="field[ancestor::component[@class='BasketForm'][@type='list']][@name='product_price']">
	<td style="text-align: right;"><xsl:value-of select="."/></td>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='BasketForm'][@type='list']][@name='product_summ']">
	<td style="text-align: right;"><xsl:value-of select="."/></td>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='BasketForm'][@type='list']][@name='basket_count']">
	<td style="text-align: center;">
    <xsl:choose>
    	<xsl:when test="@mode>'1'">
            <xsl:element name="input">
                <xsl:attribute name="type">text</xsl:attribute>
                <xsl:attribute name="name">recount[<xsl:value-of select="../field[@name='product_id']"/>]</xsl:attribute>
                <xsl:attribute name="maxlength">3</xsl:attribute>
                <xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                <xsl:attribute name="style">width:30px;</xsl:attribute>
                <xsl:if test="@pattern">
                    <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
                </xsl:if>
                <xsl:if test="@message">
                    <xsl:attribute name="nrgn:message" xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
                </xsl:if>
            </xsl:element>
    	</xsl:when>
        <xsl:otherwise>
        	<span><xsl:value-of select="."/></span>
        </xsl:otherwise>
    </xsl:choose>


    </td>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='BasketForm'][@type='list']][@name='basket_id']">
    <td align="center" width="1%">
        <xsl:choose>
            <xsl:when test="@mode>1">
                <xsl:element name="input">
                    <xsl:attribute name="type">checkbox</xsl:attribute>
                    <xsl:attribute name="name">selectedID[]</xsl:attribute>
                     <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </td>
</xsl:template>

<xsl:template match="toolbar[parent::component[@name='basket']] | toolbar[parent::component[@class='BasketForm']]">
	<div style="text-align: right;">

		<xsl:if test="control[@id='delete']/@mode != 0">
			<input type="{control[@id='delete']/@type}" name="{control[@id='delete']/@id}" value="{control[@id='delete']/@title}" style="float: left; margin-right:5px;" />		
		</xsl:if>	

		<xsl:if test="control[@id='update']/@mode != 0">
			<input type="{control[@id='update']/@type}" name="{control[@id='update']/@id}" value="{control[@id='update']/@title}" style="float: left;" />		
		</xsl:if>

		<xsl:if test="control[@id='order']/@mode != 0">
			<input type="{control[@id='order']/@type}" name="{control[@id='order']/@id}" value="{control[@id='order']/@title}" onclick="document.location = '{$BASE}{$LANG_ABBR}shop/order/'" />		
		</xsl:if>
		
	</div>	
</xsl:template>

<!-- вывод кнопок под корзиной, не используется -->
<xsl:template match="control[ancestor::component[@name='basket'] | ancestor::component[@class='BasketForm']]">
    <xsl:if test="@mode!=0">
        <xsl:element name="input">
            <xsl:attribute name="type"><xsl:value-of select="@type"/></xsl:attribute>
            <xsl:attribute name="name"><xsl:value-of select="@id"/></xsl:attribute>
            <xsl:attribute name="value"><xsl:value-of select="@title"/></xsl:attribute>
            <xsl:if test="position()!=last()">
                <xsl:attribute name="style">margin-right:5px;</xsl:attribute>
            </xsl:if>
            <xsl:if test="@click">
                <xsl:attribute name="onclick"><xsl:value-of select="@click"/></xsl:attribute>
            </xsl:if>
        </xsl:element>
    </xsl:if>
</xsl:template>
<!-- /вывод кнопок под корзиной, не используется -->

<xsl:template match="component[@class='BasketList']">
    <div class="basket_list" id="{generate-id(recordset)}">
     <xsl:value-of select="@title" />:
     <xsl:choose>
        <xsl:when test="recordset/@empty">
            <xsl:value-of select="translations/translation[@const='TXT_BASKET_EMPTY']"/>
        </xsl:when>
        <xsl:otherwise>
            <xsl:apply-templates />
        </xsl:otherwise>
     </xsl:choose>
    </div>
</xsl:template>

<xsl:template match="recordset[parent::component[@class='BasketList']]">
<div>
    <strong><xsl:value-of select="record/field[@name='basket_count']/@title"/></strong>
	<xsl:text disable-output-escaping="yes"> </xsl:text>
	<xsl:value-of select="sum(record/field[@name='basket_count'])"/><br/>
    <strong><xsl:value-of select="../translations/translation[@const='TXT_BASKET_SUMM2']"/></strong>
	<xsl:text disable-output-escaping="yes"> </xsl:text>
	<xsl:value-of select="../@summ"/>
</div>
</xsl:template>


<xsl:template match="toolbar[parent::component[@class='BasketList']]">
    <div>		
        <xsl:apply-templates />
    </div>
</xsl:template>

<xsl:template match="control[ancestor::component[@class='BasketList']]">
	<a href="{$BASE}{$LANG_ABBR}{@click}"><xsl:value-of select="@title" /></a>
	<xsl:text disable-output-escaping="yes"> </xsl:text>
</xsl:template>

</xsl:stylesheet>
