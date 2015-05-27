<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		xmlns:set="http://exslt.org/sets"
		extension-element-prefixes="set"
		version="1.0">

	<xsl:template match="field[@name='ads_item_smap_multi' and ancestor::component[@class='AdsItemEditor']]" mode="field_content">
		<div class="control smap_features" id="control_{@language}_{@name}">
			<xsl:variable name="OPTIONS" select="options/option"/>
			<xsl:for-each select="$OPTIONS[@root]">
				<xsl:sort select="@site_id"/>
				<xsl:sort select="@smap_order_num" order="descending"/>
				<xsl:if test="preceding::option/@root != @root">
					<h4><xsl:value-of select="@root"/></h4>
				</xsl:if>

				<input id="{generate-id(.)}" type="checkbox" name="ads_items[ads_item_smap_multi][]" value="{@id}" class="checkbox">
					<xsl:if test="@selected"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
				</input>
				<label for="{generate-id(.)}"><xsl:value-of select="."/></label>

				<xsl:call-template name="SMAP_ADS_ITEM_TREE">
					<xsl:with-param name="NODES" select="$OPTIONS"/>
					<xsl:with-param name="CURRENT" select="."/>
				</xsl:call-template>
			</xsl:for-each>
		</div>
	</xsl:template>

	<xsl:template name="SMAP_ADS_ITEM_TREE">
		<xsl:param name="NODES"/>
		<xsl:param name="CURRENT"/>

		<xsl:if test="count($NODES[@smap_pid = $CURRENT/@id]) &gt; 0">
			<ul>
				<xsl:for-each select="$NODES[@smap_pid = $CURRENT/@id]">
					<li>
						<input id="{generate-id(.)}" type="checkbox" name="ads_items[ads_item_smap_multi][]" value="{@id}" class="checkbox">
							<xsl:if test="@selected"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
						</input>
						<label for="{generate-id(.)}"><xsl:value-of select="."/></label>
						<xsl:call-template name="SMAP_ADS_ITEM_TREE">
							<xsl:with-param name="NODES" select="$NODES"/>
							<xsl:with-param name="CURRENT" select="."/>
						</xsl:call-template>
					</li>
				</xsl:for-each>
			</ul>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
