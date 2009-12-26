<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="field[@name='restore_password_result']">
	<xsl:value-of select="." disable-output-escaping="yes"/>
</xsl:template>
<xsl:template match="translations[ancestor::component[@class='UserProfile']]">
    <script language="JavaScript">
        <xsl:apply-templates />
    </script>
</xsl:template>

<xsl:template match="field[@name='group_div_rights']">
        <div class="page_rights">
            <table width="100%" border="1">
                <thead>
                    <tr>
                        <td><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
                        <xsl:for-each select="recordset/record[1]/field[@name='RightsId']/options/option">
                            <td style="text-align: center;"><xsl:value-of select="."/></td>
                        </xsl:for-each>
                    </tr>
                </thead>
                <tbody>
                    <xsl:call-template name="BUILD_DIV_TREE">
                        <xsl:with-param name="DATA" select="recordset"/>
                        <xsl:with-param name="LEVEL" select="0"/>
                    </xsl:call-template>
                </tbody>
            </table>
       </div>
</xsl:template>

<xsl:template name="BUILD_DIV_TREE">
    <xsl:param name="DATA"/>
    <xsl:param name="LEVEL"/>
    <xsl:for-each select="$DATA/record">
        <tr>
			<xsl:if test="floor(position() div 2) = position() div 2">
				<xsl:attribute name="class">even</xsl:attribute>
			</xsl:if>
            <td class="group_name" style="padding-left:{$LEVEL*20 + 5}px;"><xsl:value-of select="field[@name='Name']"/></td>
            <xsl:for-each select="field[@name='RightsId']/options/option">
                <td style="text-align:center;"><input type="radio" style="width:auto; border:0;" name="div_right[{../../../field[@name='Id']}]" value="{@id}">
                    <xsl:if test="@selected">
                        <xsl:attribute name="checked">checked</xsl:attribute>    
                    </xsl:if>
                </input></td>
            </xsl:for-each>
        </tr>
        <xsl:if test="recordset">
            <xsl:call-template name="BUILD_DIV_TREE">
                <xsl:with-param name="DATA" select="recordset"/>
                <xsl:with-param name="LEVEL" select="$LEVEL+1"/>
            </xsl:call-template>
        </xsl:if>
    </xsl:for-each>
</xsl:template>
</xsl:stylesheet>