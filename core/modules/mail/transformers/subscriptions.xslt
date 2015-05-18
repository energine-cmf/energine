<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		version="1.0">

	<xsl:template match="component[(@class='MailSubscriptionList') and (@componentAction='main')]">
		<div id="{generate-id(recordset)}" data-url="{$BASE}{$LANG_ABBR}{@single_template}toggle/">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="recordset[parent::component[@class='MailSubscriptionList' and @componentAction='main']]">
		<div class="subscriptions_list clearfix">
			<xsl:for-each select="record">
				<div class="subscription_block">
					<div class="subscription_block_inner clearfix">
						<div class="subscription_info">
							<div class="subscription_name">
								<label>
									<input type="checkbox" name="subscriptions[]" value="{field[@name='subscription_id']}">
										<xsl:if test="field[@name='is_subscribed']='1'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</input>
									<xsl:value-of select="field[@name='subscription_name']"/>
								</label>
							</div>
						</div>
						<div class="subscription_description">
							<xsl:value-of select="field[@name='subscription_description']"/>
						</div>
					</div>
				</div>
			</xsl:for-each>
		</div>
	</xsl:template>

</xsl:stylesheet>