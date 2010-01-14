<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="component[@class='OrderForm']">
	<xsl:apply-templates />
</xsl:template>

<xsl:template match="translations[ancestor::component[@class='UserOrderHistory']]"></xsl:template>

<xsl:template match="recordset[parent::component[@class='OrderForm']]">
<form action="{../@action}" method="POST" id="{generate-id(.)}" class="base_form order_form">
	<xsl:if test="../@componentAction = 'main'"><xsl:value-of select="../@title"/></xsl:if>
	<xsl:apply-templates />
	<xsl:if test="$TRANSLATION[@const='TXT_REQUIRED_FIELDS']">
		<div class="note">
			<xsl:value-of select="$TRANSLATION[@const='TXT_REQUIRED_FIELDS']" disable-output-escaping="yes" />
		</div>
	</xsl:if>
</form>
</xsl:template>

<xsl:template match="control[ancestor::component[@class='OrderForm']]">
<div class="buttons">
	<xsl:variable name="JS_OBJECT" select="generate-id(ancestor::component[@class='OrderForm']/recordset)"/>
    <xsl:element name="button">
        <xsl:if test="@click">
        	<xsl:attribute name="onclick"><xsl:value-of select="$JS_OBJECT"/>.<xsl:value-of select="@click"/>();</xsl:attribute>
        </xsl:if>
        <xsl:value-of select="@title"/>
    </xsl:element>
</div>
</xsl:template>

<xsl:template match="field[ancestor::component[@type='form'][@class='OrderForm']][@name='message']">
	<div class="field textbox">        
		<xsl:value-of select="." disable-output-escaping="yes" />
    </div>
</xsl:template>

<xsl:template match="field[ancestor::component[@type='form'][@class='OrderForm']][@type='string'] | field[ancestor::component[@type='form'][@class='OrderForm']][@type='email']| field[ancestor::component[@type='form'][@class='OrderForm']][@type='phone']">
	<div class="field">
        <xsl:if test="@title">
            <xsl:if test="not(@nullable) and @type != 'boolean'">
                <xsl:attribute name="class">field required</xsl:attribute>
            </xsl:if>
		    <div class="name">
    			<xsl:element name="label">
    				<xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
    				<xsl:value-of select="@title" disable-output-escaping="yes" />                    
    			</xsl:element>
				<xsl:if test="not(@nullable)">
					<span class="mark"> *</span>
                </xsl:if>    			
			</div>
		</xsl:if>
		<div class="control">
        <xsl:element name="input">
                <xsl:attribute name="type">text</xsl:attribute>
                <!-- <xsl:attribute name="style">width:300px;</xsl:attribute>
                <xsl:attribute name="class">shop-form-textfield</xsl:attribute> -->
                <xsl:attribute name="name"><xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                        </xsl:choose></xsl:attribute>
                <xsl:if test="@length">
                    <xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
                </xsl:if>
                <xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                <xsl:if test="@pattern">
                    <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
                </xsl:if>
                <xsl:if test="@message">
                    <xsl:attribute name="nrgn:message" xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
                </xsl:if>
            </xsl:element>
        </div>
    </div>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='UserOrderHistory'][@type='list']][@name='order_created']">
	<a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}{../field[@name='order_id']}/"><xsl:value-of select="."/></a>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='UserOrderHistory'][@type='list']][@name='os_id']">
     - <xsl:value-of select="options/option[@selected]"/>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='UserOrderHistory'][@type='list']][@name='order_id']" />

<xsl:template match="field[ancestor::component[@class='UserOrderHistory'][@type='form']][@name='order_detail']">
	<div class="field">
        <div class="name">
           <label> <xsl:value-of select="@title"/>:</label>
        </div>
        <div class="control">
            <xsl:apply-templates />
        </div>
    </div>
</xsl:template>

<xsl:template match="recordset[parent::field[@name='order_detail']][ancestor::component[@class='UserOrderHistory'][@type='form']]">
	<table border="1" cellspacing="0" cellpadding="2" class="basket">
        <thead>
            <tr>
                <xsl:for-each select="record[1]/field[@name!='product_id']">
                	<th><xsl:value-of select="@title"/></th>
                </xsl:for-each>
            </tr>
        </thead>
        <tbody>
            <xsl:for-each select="record">
            	<tr>
                    <xsl:for-each select="field[@name!='product_id']">
                        <td><xsl:value-of select="."/></td>
                    </xsl:for-each>
                </tr>
            </xsl:for-each>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="{count(record[1]/field[@name!='product_summ'][@name!='product_id'])}" align="right"><xsl:value-of select="@title"/>:</td><td><strong><xsl:value-of select="@summ"/></strong></td>
            </tr>
            <xsl:if test="@discount > 0">
                <tr><td style="text-align: right;" colspan="3"><xsl:value-of select="$TRANSLATION[@const='TXT_BASKET_SUMM_WITH_DISCOUNT']"/>&#160;<xsl:value-of select="format-number(@discount, '#')"/>%: </td><td style="text-align: right;"><strong><xsl:value-of select="format-number(@summ_with_discount, '#.00')"/></strong></td></tr>
            </xsl:if>
        </tfoot>
    </table>
</xsl:template>

</xsl:stylesheet>
