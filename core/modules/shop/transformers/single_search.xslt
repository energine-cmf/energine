<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        version="1.0">
    <xsl:include href="../../share/transformers/single.xslt"/>

    <xsl:template match="component[@class='SearchResults']">
        <xsl:choose>
        <xsl:when test="not(recordset/@empty)">
            <ul class="autocomplete_list">
                <xsl:for-each select="recordset/record">
                    <li class="item">
                        <a href="{$BASE}{$LANG_ABBR}{field[@name='smap_id']}view/{field[@name='goods_segment']}" class="goods_block clearfix">
                            <div class="goods_img">
                                <img src="{$RESIZER_URL}w69-h70/{field[@name='attachments']/recordset/record[1]/field[@name='file']}" alt=""/>
                            </div>
                            <div class="goods_name">
                                <xsl:value-of select="position()"/>. <xsl:value-of select="field[@name='goods_name']"/>
                            </div>
                            <div class="goods_price">
                                <xsl:value-of select="field[@name='goods_price']/@value" disable-output-escaping="yes"/>
                            </div>
                            <i class="icon icon_chevron_circle_right"></i>
                        </a>
                    </li>
                </xsl:for-each>
            </ul>
            <div class="autocomplete_footer clearfix">
                <div class="footer_text"><xsl:value-of select="$TRANSLATION[@const='TXT_ALL_SEARCH_RESULTS']"/></div>
                <a href="{$BASE}{$LANG_ABBR}search/?{@keyword_name}={@keyword}" class="view_link"><xsl:value-of select="$TRANSLATION[@const='BTN_VIEW']"/></a>
            </div>
        </xsl:when>
            <xsl:otherwise>
                <div><xsl:value-of select="recordset/@empty"/></div>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>