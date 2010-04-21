<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <!-- компонент LoginForm  -->
    <!-- режим гостя -->
    <xsl:template match="component[@class='LoginForm']">
        <form method="post" action="{@action}" class="base_form login_form">
            <input type="hidden" name="componentAction" value="{@componentAction}" />
            <xsl:apply-templates/>
        </form>
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@class='LoginForm']]">
        <div id="{generate-id(.)}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}" template="{$BASE}{$LANG_ABBR}{../@template}">
            <xsl:apply-templates/>
        </div>
    </xsl:template>
    
    <xsl:template match="control[@id='restore'][ancestor::component[@class='LoginForm']]">
        <xsl:if test="@mode != 0">
            <div class="restore_link">
                <a href="{$BASE}{$LANG_ABBR}{@click}"><xsl:value-of select="@title" /></a>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template match="field[@name='message'][ancestor::component[@class='LoginForm']]">
        <div class="error_message">
            <xsl:apply-templates/>
        </div>
    </xsl:template>
    
    <!-- режим пользователя за логином -->
    <xsl:template match="recordset[parent::component[@class='LoginForm'][@componentAction='showLogoutForm']]">
        <div>
           <xsl:apply-templates/>
        </div>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='LoginForm'][@componentAction='showLogoutForm']]">
        <span class="user_greeting"><xsl:value-of select="$TRANSLATION[@const='TXT_USER_GREETING']"/></span><xsl:value-of select="$NBSP" disable-output-escaping="yes" />
        <span class="user_name"><xsl:value-of select="$TRANSLATION[@const='TXT_USER_NAME']"/>:<xsl:value-of select="$NBSP" disable-output-escaping="yes" /><strong><xsl:value-of select="field[@name='u_name']"/></strong></span><br/>
        <span class="user_role"><xsl:value-of select="$TRANSLATION[@const='TXT_ROLE_TEXT']"/>:<xsl:value-of select="$NBSP" disable-output-escaping="yes" /><strong><xsl:value-of select="field[@name='role_name']"/></strong></span>
    </xsl:template>    
    <!-- /компонент LoginForm  -->
    
    <!-- компонент Register -->
    <xsl:template match="component[@class='Register'][@componentAction='success']">
        <div class="result_message">
            <xsl:value-of select="recordset/record/field" disable-output-escaping="yes"/>
        </div>
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@class='Register']]">
        <div id="{generate-id(.)}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
            <xsl:apply-templates/>
            <xsl:call-template name="captcha"/>     
        </div>
        <xsl:if test="$TRANSLATION[@const='TXT_REQUIRED_FIELDS']">
            <div class="note">
                <xsl:value-of select="$TRANSLATION[@const='TXT_REQUIRED_FIELDS']" disable-output-escaping="yes"/>
            </div>
        </xsl:if>
    </xsl:template>    
    <!-- /компонент Register -->
    
    <!-- компонент UserProfile -->
    <xsl:template match="component[@class='UserProfile'][@componentAction='success']">
        <div class="result_message">
            <xsl:value-of select="recordset/record/field" disable-output-escaping="yes"/>
        </div>
    </xsl:template>
    <!-- /компонент UserProfile -->
    
    <!-- компонент RoleEditor -->    
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
    <!-- /компонент RoleEditor -->
    
</xsl:stylesheet>