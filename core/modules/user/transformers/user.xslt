<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

<xsl:template match="field[@name='restore_password_result']">
	<xsl:value-of select="." disable-output-escaping="yes"/>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='UserProfile']][@name='u_password'] | field[ancestor::component[@class='UserProfile']][@name='u_password2']">
	<div class="field">
		    <xsl:attribute name="class">field required</xsl:attribute>
		    <div class="name">
    			<label for="{@name}">
    				<xsl:value-of select="@title" disable-output-escaping="yes" />
    			</label>
				<span class="mark">*</span>
			</div>
		<div class="control">
			<xsl:element name="input">
                <xsl:attribute name="type">text</xsl:attribute>
                <xsl:attribute name="name"><xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                        </xsl:choose></xsl:attribute>
                <xsl:if test="@length">
                    <xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
                </xsl:if>
                <xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
                <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
                <xsl:attribute name="message2"><xsl:value-of select="@message2"/></xsl:attribute>
            </xsl:element>
        </div>
	</div>
</xsl:template>

<xsl:template match="control[ancestor::component[@class='UserProfile']]">
    <xsl:element name="button">
        <xsl:attribute name="onclick"><xsl:value-of select="generate-id(ancestor::component[@class='UserProfile']/recordset)"/>.<xsl:value-of select="@click"/>(); return false;</xsl:attribute>
		<xsl:value-of select="@title"/>       
    </xsl:element>
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