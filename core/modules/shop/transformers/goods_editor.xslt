<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:set="http://exslt.org/sets"
        extension-element-prefixes="set"
		version="1.0">

    <xsl:template match="field[@name='smap_id' and ancestor::component[@sample='GoodsEditor']]" mode="field_input">
        <select id="{@name}">
            <xsl:attribute name="name"><xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName"/><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name"/>]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
            </xsl:choose></xsl:attribute>
            <xsl:call-template name="PRODUCTS_SMAP_SELECTOR">
                <xsl:with-param name="RECORDSET" select="recordset"/>
            </xsl:call-template>
        </select>
    </xsl:template>



    <xsl:template match="field[(@name='smap_features_multi') and (@type='multi') and (ancestor::component[@sample='DivisionEditor'])]" mode="field_input">
        <xsl:variable name="DATA" select="options/option"/>
        <xsl:variable name="NAME"><xsl:choose>
            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name"/>]</xsl:when>
            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
        </xsl:choose>[]</xsl:variable>
                <div class="checkbox_set">
                    <xsl:for-each select="set:distinct($DATA/@group_id)">
                        <xsl:variable name="GROUP_ID" select="."/>
                        <section>
                            <h4><xsl:value-of select="$DATA[@group_id =$GROUP_ID][1]/@group_name"/></h4>
                            <xsl:for-each select="$DATA[@group_id =$GROUP_ID]">
                                <div>
                                    <input type="checkbox" id="{generate-id(.)}" name="{$NAME}" value="{@id}" class="checkbox">
                                        <xsl:if test="@selected">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </input>
                                    <label for="{generate-id(.)}"><xsl:value-of select="."/></label>
                                </div>
                            </xsl:for-each>
                        </section>
                    </xsl:for-each>
                </div>
    </xsl:template>

    <xsl:template name="PRODUCTS_SMAP_SELECTOR">
        <xsl:param name="RECORDSET"/>
        <xsl:param name="LEVEL" select="0"/>
        <xsl:for-each select="$RECORDSET/record">
            <xsl:choose>
                <xsl:when test="field[@name='isLabel']=1">
                    <optgroup label="{field[@name='name']}"></optgroup>
                </xsl:when>
                <xsl:otherwise>
                    <option value="{field[@name='id']}">
                        <xsl:if test="field[@name='selected']=1">
                            <xsl:attribute name="selected">selected</xsl:attribute>
                        </xsl:if>
                        <xsl:call-template name="REPEATABLE">
                            <xsl:with-param name="STR"><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></xsl:with-param>
                            <xsl:with-param name="COUNT" select="$LEVEL"/>
                        </xsl:call-template>
                        <xsl:value-of select="field[@name='name']"/>
                        </option>

                </xsl:otherwise>
            </xsl:choose>
            <xsl:call-template name="PRODUCTS_SMAP_SELECTOR">
                <xsl:with-param name="RECORDSET" select="recordset"/>
                <xsl:with-param name="LEVEL"><xsl:value-of select="$LEVEL+2"/></xsl:with-param>
            </xsl:call-template>
        </xsl:for-each>
    </xsl:template>

    <xsl:template name="REPEATABLE">
        <xsl:param name="STR"/>
        <xsl:param name="COUNT"/>
        <xsl:param name="CURRENT" select="0"/>
        <xsl:if test="$CURRENT&lt;$COUNT">
            <xsl:value-of select="$STR" disable-output-escaping="yes"/>
            <xsl:call-template name="REPEATABLE">
                <xsl:with-param name="COUNT" select="$COUNT"/>
                <xsl:with-param name="STR" select="$STR"/>
                <xsl:with-param name="CURRENT"><xsl:value-of select="$CURRENT+1"/></xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>

    <xsl:template match="component[@class='SiteList' and (following::component[@class='CategoryEditor'] or preceding::component[@class='CategoryEditor'])]"  mode="insideEditor">
            <select onchange="document.location = '{$BASE}{$LANG_ABBR}{$TEMPLATE}show-shop/' + this.options[this.selectedIndex].value + '/';" id="site_selector">
                <xsl:for-each select="recordset/record">
                    <option value="{field[@name='site_id']}">
                        <xsl:if test="field[@name='site_id'] = $COMPONENTS[@sample='DivisionEditor']/@site">
                            <xsl:attribute name="selected">selected</xsl:attribute>
                        </xsl:if>
                        <xsl:value-of select="field[@name='site_name']"/></option>
                </xsl:for-each>
            </select>
        </xsl:template>

    <xsl:template match="field[@name='currency_id' and ancestor::component[@sample='GoodsEditor']]" />

    <xsl:template match="field[@name='goods_price' and ancestor::component[@sample='GoodsEditor']]" mode="field_content">
        <div class="control" id="control_{@language}_{@name}">
            <xsl:if test="../field[@name='currency_id']/options/option[@selected]/@currency_shortname_order ='before'">
                <span class="currency_before"><xsl:value-of select="../field[@name='currency_id']/options/option[@selected]/@currency_shortname" /></span>
            </xsl:if>
            <xsl:apply-templates select="." mode="field_input"/>
            <xsl:if test="../field[@name='currency_id']/options/option[@selected]/@currency_shortname_order ='after'">
                <span class="currency_after"><xsl:value-of select="../field[@name='currency_id']/options/option[@selected]/@currency_shortname" /></span>
            </xsl:if>
        </div>
    </xsl:template>

    <xsl:template match="field[@name='feature_smap_multi' and ancestor::component[@sample='FeatureEditor']]" mode="field_content">
        <div class="control smap_features" id="control_{@language}_{@name}">
            <xsl:variable name="OPTIONS" select="options/option"/>
            <xsl:for-each select="$OPTIONS[@root]">
                <xsl:sort select="@site_id"/>
                <xsl:sort select="@smap_order_num" order="descending"/>
                <xsl:if test="preceding::option/@root != @root">
                    <h4><xsl:value-of select="@root"/></h4>
                </xsl:if>
                <h5><xsl:value-of select="."/></h5>
                <xsl:call-template name="SMAP_FEATURE_TREE">
                    <xsl:with-param name="NODES" select="$OPTIONS"/>
                    <xsl:with-param name="CURRENT" select="."/>
                </xsl:call-template>
            </xsl:for-each>
        </div>
    </xsl:template>

    <xsl:template name="SMAP_FEATURE_TREE">
        <xsl:param name="NODES"/>
        <xsl:param name="CURRENT"/>
        
        <xsl:if test="count($NODES[@smap_pid = $CURRENT/@id]) &gt; 0">
        <ul>
        <xsl:for-each select="$NODES[@smap_pid = $CURRENT/@id]">
            <li>
                <input id="{generate-id(.)}" type="checkbox" name="shop_features[feature_smap_multi][]" value="{@id}" class="checkbox">
                    <xsl:if test="@selected"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
                </input>
                <label for="{generate-id(.)}"><xsl:value-of select="."/></label>
                <xsl:call-template name="SMAP_FEATURE_TREE">
                    <xsl:with-param name="NODES" select="$NODES"/>
                    <xsl:with-param name="CURRENT" select="."/>
                </xsl:call-template>
            </li>
        </xsl:for-each>
        </ul>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>
