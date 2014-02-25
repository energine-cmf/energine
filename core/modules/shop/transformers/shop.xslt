<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="field[@type='tab'][ancestor::component[@type='form']]" mode="field_name">
            <li data-src="{ancestor::component/@single_template}{.}" data-segment="{@segment}" data-url="{ancestor::component/@single_template}">
                <a href="#{generate-id(.)}"><xsl:value-of select="@title" /></a>
            </li>
        </xsl:template>

    <!-- Корзина -->
    <xsl:template match="component[@class='Basket'][@componentAction='init']">
        <div id="{generate-id(recordset)}" data-url="{$BASE}{$LANG_ABBR}{@single_template}">
        </div>
    </xsl:template>

    <xsl:template match="component[@class='Basket'][@componentAction='main']">
        <ul>
            <xsl:for-each select="recordset/record">
                <li>
                    <xsl:value-of select="field[@name='sb_quantity']"/>
                    <i style="margin-left: 5px;"><xsl:value-of select="field[@name='product_title']"/></i>
                </li>
            </xsl:for-each>
        </ul>
        <div><b>Total:</b> <xsl:value-of select="@basketTotal"/></div>
    </xsl:template>
    <!-- /Корзина -->

    <!-- Товары -->
    <xsl:template match="component[@class='ProductFeed' and @componentAction='main']">
        <div id="{generate-id(recordset)}">
            <ul>
                <xsl:for-each select="recordset/record">
                    <li data-product-id="{field[@name='product_id']}">
                        <xsl:value-of select="field[@name='product_title']"/>
                        <xsl:value-of select="$NBSP" disable-output-escaping="yes"/>
                        <b><xsl:value-of select="field[@name='product_price']"/></b>
                        <xsl:value-of select="$NBSP" disable-output-escaping="yes"/>
                        <a href="#" class="basket_put">+</a>
                    </li>
                </xsl:for-each>
            </ul>
        </div>
    </xsl:template>
    <!-- /Товары -->

</xsl:stylesheet>
