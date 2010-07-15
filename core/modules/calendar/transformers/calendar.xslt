<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <xsl:template match="component[@exttype='calendar']">
        <xsl:variable name="CONTROLS" select="toolbar/control"></xsl:variable>
        <xsl:if test="not(recordset/@empty)">
            <div id="{generate-id(recordset)}" single_template="{$BASE}{$LANG_ABBR}{@single_template}">
            <a href="{$BASE}{$LANG_ABBR}{@template}{$CONTROLS[@id='previous']/@year}/{$CONTROLS[@id='previous']/@month}/"><xsl:text disable-output-escaping="yes">&lt;</xsl:text></a>
            <a href="{$BASE}{$LANG_ABBR}{@template}{$CONTROLS[@id='current']/@year}/{$CONTROLS[@id='current']/@month}/"><xsl:value-of select="$CONTROLS[@id='current']/@monthName"/>, <xsl:value-of select="$CONTROLS[@id='current']/@year"/></a>
            <a href="{$BASE}{$LANG_ABBR}{@template}{$CONTROLS[@id='next']/@year}/{$CONTROLS[@id='next']/@month}/"><xsl:text disable-output-escaping="yes">&gt;</xsl:text></a>
            <table width="100%">
                <thead>
                    <tr>
                        <xsl:for-each select="recordset/record[1]/field">
                            <th><xsl:value-of select="@title"/></th>
                        </xsl:for-each>
                    </tr>
                </thead>
                <tbody>
                    <xsl:for-each select="recordset/record">
                        <tr align="center">
                            <xsl:for-each select="field">
                                <td>
                                    <xsl:if test="@today">
                                        <xsl:attribute name="style">background-color:#F47820;</xsl:attribute>
                                    </xsl:if>
                                    <xsl:if test="not(@current)">
                                        <xsl:attribute name="style">background-color:silver;</xsl:attribute>
                                    </xsl:if>
                                    <xsl:if test="@selected">
                                        <xsl:attribute name="style">background-color:orange;</xsl:attribute>
                                    </xsl:if>
                                    <xsl:choose>
                                        <xsl:when test="@selected"><a href="{$BASE}{$LANG_ABBR}{../../../@template}{@year}/{@month}/{@day}/"><xsl:value-of select="."/></a></xsl:when>
                                        <xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
                                    </xsl:choose>
                                    </td>
                            </xsl:for-each>
                        </tr>
                    </xsl:for-each>
                </tbody>
                
            </table>
            </div>
        </xsl:if>
    </xsl:template>
    
</xsl:stylesheet>
