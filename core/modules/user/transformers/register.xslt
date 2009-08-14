<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="component[@class='Register']">
    <form method="post" action="{../@action}"  class="base_form registration_form">
        <xsl:apply-templates />
    </form>    
</xsl:template>

<xsl:template match="recordset[parent::component[@class='Register']]">
        <div id="{generate-id(.)}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
    		<xsl:apply-templates />
			<xsl:if test="../translations/translation[@const='TXT_REQUIRED_FIELDS']">
				<div class="note">
					<xsl:value-of select="../translations/translation[@const='TXT_REQUIRED_FIELDS']" disable-output-escaping="yes" />
				</div>
			</xsl:if>
        </div>
</xsl:template>


<xsl:template match="component[@class='Register'][descendant::field[@name='sucess_message']]">
    <div>
        <xsl:value-of select="descendant::field[@name='sucess_message']"/>
    </div>
</xsl:template>


</xsl:stylesheet>
