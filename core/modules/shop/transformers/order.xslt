<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml"
    >
<!-- xmlns:dyn="http://exslt.org/dynamic"
    extension-element-prefixes="dyn" -->

    <!-- компонент OrderForm -->
    <xsl:template match="component[@class='OrderForm']">
    	<xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@class='OrderForm']]">
        <form action="{../@action}" method="POST" id="{generate-id(.)}" class="base_form order_form">
        	<xsl:apply-templates/>
            <xsl:call-template name="captcha"/>
        	<xsl:if test="$TRANSLATION[@const='TXT_REQUIRED_FIELDS']">
        		<div class="note">
        			<xsl:value-of select="$TRANSLATION[@const='TXT_REQUIRED_FIELDS']" disable-output-escaping="yes"/>
        		</div>
        	</xsl:if>
        </form>
    </xsl:template>
    
    <xsl:template match="control[ancestor::component[@class='OrderForm']]">
        <xsl:variable name="JS_OBJECT" select="generate-id(ancestor::component[@class='OrderForm']/recordset)"/>
        <button>
            <xsl:if test="@click">
            	<xsl:attribute name="onclick"><xsl:value-of select="$JS_OBJECT"/>.<xsl:value-of select="@click"/>();</xsl:attribute>
            </xsl:if>
            <xsl:value-of select="@title"/>
        </button>        
    </xsl:template>
    
    <xsl:template match="component[@class='OrderForm'][@componentAction='success'] | component[@class='OrderForm'][@componentAction='save']">
        <div class="result_message">
            <xsl:value-of select="recordset/record/field" disable-output-escaping="yes"/>
        </div>
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