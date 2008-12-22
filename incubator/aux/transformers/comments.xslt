<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:template match="recordset[parent::component[@class='CommentForm']]">		
        <xsl:variable name="LINKED_COMPONENT" select="../@linked_component"/>
    	<div id="{generate-id(.)}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}" linked="{generate-id($COMPONENTS[@name=$LINKED_COMPONENT]/recordset)}">
    		<xsl:apply-templates />
    	</div>
		<xsl:if test="../translations/translation[@const='TXT_REQUIRED_FIELDS']">
			<div class="note">
				<xsl:value-of select="../translations/translation[@const='TXT_REQUIRED_FIELDS']" disable-output-escaping="yes" />
			</div>
		</xsl:if>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@class='Comments']]">
        <ul id="{generate-id(.)}">
            <xsl:for-each select="record">
                <li>
                    <xsl:for-each select="field[position()&gt;1]">
                        <div><xsl:value-of select="." disable-output-escaping="yes"/></div>    
                    </xsl:for-each>
                </li>
            </xsl:for-each>
        </ul>
    </xsl:template>

</xsl:stylesheet>