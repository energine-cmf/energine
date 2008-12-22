<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="component[@class='Register']">
    <xsl:apply-templates />
</xsl:template>

<xsl:template match="recordset[parent::component[@class='Register']]">
        <form method="post" action="{../@action}"  id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}" class="base_form registration_form">
    		<xsl:apply-templates />
			<xsl:if test="../translations/translation[@const='TXT_REQUIRED_FIELDS']">
				<div class="note">
					<xsl:value-of select="../translations/translation[@const='TXT_REQUIRED_FIELDS']" disable-output-escaping="yes" />
				</div>
			</xsl:if>
		</form>    
</xsl:template>

<xsl:template match="control[ancestor::component[@class='Register']]">
	<div class="buttons">
		<xsl:element name="input">
			<xsl:attribute name="type">button</xsl:attribute>
			<xsl:attribute name="onclick"><xsl:value-of select="generate-id(ancestor::component[@class='Register']/recordset)"/>.<xsl:value-of select="@click"/>();</xsl:attribute>
			<xsl:attribute name="value"><xsl:value-of select="@title"/></xsl:attribute>
		</xsl:element>
	</div>
</xsl:template>

<xsl:template match="component[@class='Register'][descendant::field[@name='sucess_message']]">
    <div>
        <xsl:value-of select="descendant::field[@name='sucess_message']"/>
    </div>
</xsl:template>


</xsl:stylesheet>
