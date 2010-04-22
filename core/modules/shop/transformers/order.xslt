<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml"
    >
<!-- xmlns:dyn="http://exslt.org/dynamic"
    extension-element-prefixes="dyn" -->

    <!-- компонент OrderForm -->
    <xsl:template match="recordset[parent::component[@class='OrderForm']]">
        <div id="{generate-id(.)}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}" template="{$BASE}{$LANG_ABBR}{../@template}">
            <xsl:apply-templates/>
        </div>
        <xsl:if test="$TRANSLATION[@const='TXT_REQUIRED_FIELDS']">
            <div class="note">
                <xsl:value-of select="$TRANSLATION[@const='TXT_REQUIRED_FIELDS']" disable-output-escaping="yes"/>
            </div>
        </xsl:if>
    </xsl:template>
    <!-- /компонент OrderForm -->
    
    <!-- компонент UserOrderHistory -->
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
    <!-- /компонент UserOrderHistory -->    
    
    <xsl:template match="field[@name='order_details'][ancestor::component[@class='OrderHistory']]">
        <xsl:variable name="RECORDS" select="recordset/record"/>
        <div class="page_rights">
            <table width="100%" border="0">
                <thead>
                    <tr>
                        <td><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
                        <xsl:for-each select="$RECORDS[position()=1]/field">
                            <td style="text-align:center;"><xsl:value-of select="@title"/></td>
                        </xsl:for-each>
                    </tr>
                </thead>
                <tbody>
                    <xsl:for-each select="$RECORDS">
                        <tr>
                            <xsl:if test="floor(position() div 2) = position() div 2">
                                <xsl:attribute name="class">even</xsl:attribute>
                            </xsl:if>
                            <td class="group_name"><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
                            <xsl:for-each select="field">
                                <td><xsl:value-of select="."/></td>
                            </xsl:for-each>
                        </tr>
                    </xsl:for-each>
                </tbody>
<!--                <tfoot>
                    <tr>
                        <td colspan="{count($RECORDS[position()=1]/field)}"></td>
                        <td><xsl:value-of select="dyn:min($RECORDS/field[@name='product_summ'], '.')"/></td>
                    </tr>
                </tfoot>
                -->
            </table>
        </div>
    </xsl:template>
    
</xsl:stylesheet>