<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  
    version="1.0">

    <xsl:template match="component[(@class='Wishlist') and (@componentAction='main')]">
        <div id="{generate-id(recordset)}" data-url="{$BASE}{$LANG_ABBR}{@single_template}{@action}">
            <strong><xsl:value-of select="@title"/>:<a href="{$BASE}{$LANG_ABBR}{$TEMPLATE}"><xsl:value-of select="@count"/></a></strong>
        </div>
    </xsl:template>

    <xsl:template match="component[@class='Wishlist' and @componentAction='show']">
        <form id="{generate-id(recordset)}" method="post" action="">
            <xsl:apply-templates/>
        </form>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@class='Wishlist' and @componentAction='show']]">
        <div class="goods_list clearfix"> <!-- клас .wide_list для списка -->
            <xsl:for-each select="record">
                <xsl:variable name="URL">
                    <xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of
                        select="field[@name='smap_id']"/>view/<xsl:value-of
                        select="field[@name='goods_segment']"/>/</xsl:variable>
                <div class="goods_block">
                    <div class="goods_block_inner clearfix">
                        <div class="goods_image">
                            <a href="{$URL}">
                                <img src="{$RESIZER_URL}w200-h150/{field[@name='attachments']/recordset/record[1]/field[@name='file']}"
                                     alt="{field[@name='attachments']/recordset/record[1]/field[@name='title']}"/>
                            </a>
                        </div>
                        <div class="goods_info">
                            <div class="goods_name">
                                <input type="checkbox" name="products[]" value="{field[@name='goods_id']}"/>
                                <a href="{$URL}">
                                    <xsl:value-of select="field[@name='goods_name']"/>
                                </a>
                            </div>
                            <div class="goods_producer">
                                <xsl:value-of select="field[@name='producer_id']/value"/>
                            </div>
                            <div class="goods_status available">
                                <xsl:value-of select="field[@name='sell_status_id']/value"/>
                            </div>
                            <div class="goods_price">
                                <xsl:value-of select="field[@name='goods_price']"/>
                            </div>
                        </div>

                    </div>
                </div>
            </xsl:for-each>
        </div>
    </xsl:template>

</xsl:stylesheet>
