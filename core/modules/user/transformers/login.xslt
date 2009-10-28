<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
    
    <xsl:template match="component[@class='LoginForm']">
        <div id="userForm">
            <form method="post" action="{@action}">
                <input type="hidden" name="componentAction" value="{@componentAction}" />
        		<xsl:apply-templates />
            </form>
        </div>
    </xsl:template>

    <xsl:template match="field[@name='message'][ancestor::component[@class='LoginForm']]">
        <div style="color:red; font-weight:bold;border:1px solid red; padding:5px;margin:2px;">
            <xsl:value-of select="."/>
        </div>
    </xsl:template>

    <xsl:template match="component[@class='LoginForm' and @componentAction='showLogoutForm']/recordset">
    	<div>
    	   <xsl:apply-templates />
        </div>
    </xsl:template>

    <xsl:template match="component[@class='LoginForm' and @componentAction='showLogoutForm']/recordset/record">
        <span><xsl:value-of select="../../translations/translation[@const='TXT_USER_GREETING']"/></span><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
        <span><xsl:value-of select="../../translations/translation[@const='TXT_USER_NAME']"/>:<xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text><strong><xsl:value-of select="field[@name='u_name']"/></strong></span><br />
        <span><xsl:value-of select="../../translations/translation[@const='TXT_ROLE_TEXT']"/>:<xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text><strong><xsl:value-of select="field[@name='role_name']"/></strong></span>
    </xsl:template>

    <xsl:template match="control[@id='restore']">
        <xsl:if test="@mode != 0">
        <br/>
            <a href="{$BASE}{$LANG_ABBR}{@click}">
                <xsl:value-of select="@title" />
            </a>
        </xsl:if>
    </xsl:template>

    <xsl:template match="component[@class='LoginForm']/translations" />

</xsl:stylesheet>