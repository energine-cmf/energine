<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <xsl:template match="component[@exttype='calendar']">
        <xsl:if test="not(recordset/@empty)">
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
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>
