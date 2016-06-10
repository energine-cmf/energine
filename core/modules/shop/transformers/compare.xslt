<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        version="1.0">

	<!-- todo: css-классы -->
	<!-- информер сравнения в шапке -->
	<xsl:template match="component[@class='GoodsCompare' and @componentAction='main']">
		<div id="{generate-id(recordset)}"
			 class="header_compare_block"
			 data-compare-url="{$BASE}{$LANG_ABBR}{@single_template}compare/"
			 data-informer-url="{$BASE}{$LANG_ABBR}{@single_template}informer/"
			 data-add-url="{$BASE}{$LANG_ABBR}{@single_template}add/"
			 data-remove-url="{$BASE}{$LANG_ABBR}{@single_template}remove/"
			 data-clear-url="{$BASE}{$LANG_ABBR}{@single_template}clear/"
				>
			<!-- контейнер для содержимого информера -->
		</div>
		<xsl:apply-templates select="javascript"/>
	</xsl:template>

	<!-- информер + попап сравнения в шапке (вызывается через single) -->
	<xsl:template match="component[@class='GoodsCompare' and (@componentAction='informer' or @componentAction='add' or @componentAction='remove' or @componentAction='clear')]">
		<xsl:if test="@goods_count>0">
			<a href="#" class="compare_link">
				<xsl:text>Сравнение (</xsl:text>
				<xsl:value-of select="@goods_count"/>
				<xsl:text>)</xsl:text>
			</a>
			<div class="popup_compare hidden">
				<div class="compare_text">Выбрано продуктов для сравнения:</div>
				<div class="compare_count">5</div>
				<a href="#" class="clear_compare_list">Очистить</a>
				<xsl:for-each select="recordset/record">
					<xsl:value-of select="field[@name='smap_name']"/>
					<xsl:text>: </xsl:text>
					<xsl:value-of select="field[@name='goods_count']"/>
					<xsl:if test="field[@name='goods_count']>1">
						<button type="button" class="compare-toggle" data-goods-ids="{field[@name='goods_ids']}">
							Сравнить
						</button>
					</xsl:if>
				</xsl:for-each>
			</div>
		</xsl:if>
	</xsl:template>

	<!-- todo: сам список сравнения (componentAction='compare') -->

</xsl:stylesheet>
