<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <xsl:template match="component[@exttype='calendar']">
        <xsl:if test="not(recordset/@empty)">
            <div id="{generate-id(recordset)}" single_template="{$BASE}{$LANG_ABBR}{@single_template}">
            <button id="previous" date="{toolbar/control[@id='previous']/@month}-{toolbar/control[@id='previous']/@year}">&lt;</button>
            <strong><xsl:value-of select="toolbar/control[@id='current']/@monthName"/>, <xsl:value-of select="toolbar/control[@id='current']/@year"/></strong>
            <button id="next" date="{toolbar/control[@id='next']/@month}-{toolbar/control[@id='next']/@year}">&gt;</button>
            <table>
                <thead>
                    <tr>
                        <xsl:for-each select="recordset/record[1]/field">
                            <th><xsl:value-of select="@title"/></th>
                        </xsl:for-each>
                    </tr>
                </thead>
                <tbody>
                    <xsl:for-each select="recordset/record">
                        <tr>
                            <xsl:for-each select="field">
                                <td><xsl:value-of select="."/></td>
                            </xsl:for-each>
                        </tr>
                    </xsl:for-each>
                </tbody>
                
            </table>
            </div>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>
