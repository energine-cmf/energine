<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns="http://www.w3.org/1999/xhtml">

    <xsl:template match="component[@exttype='calendar']">
        <xsl:if test="not(recordset/@empty)">
            <div id="{generate-id(recordset)}">
                <!-- single_template="{$BASE}{$LANG_ABBR}{@single_template}"-->
                <xsl:apply-templates select="toolbar"/>
                <xsl:apply-templates select="recordset"/>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@exttype='calendar']]">
        <table width="100%">
            <thead>
                <tr>
                    <xsl:for-each select="recordset/record[1]/field">
                        <th>
                            <xsl:value-of select="@title"/>
                        </th>
                    </xsl:for-each>
                </tr>
            </thead>
            <tbody>
                <xsl:apply-templates/>
            </tbody>

        </table>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@exttype='calendar']]">
        <tr align="center">
            <xsl:apply-templates/>
        </tr>
    </xsl:template>
    
    <xsl:template match="field[ancestor::component[@exttype='calendar']]">
        <td>
            <xsl:if test="@today">
                <xsl:attribute name="style">background-color:#F47820;
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="not(@current)">
                <xsl:attribute name="style">background-color:silver;
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="@selected">
                <xsl:attribute name="style">background-color:orange;
                </xsl:attribute>
            </xsl:if>
            <xsl:choose>
                <xsl:when test="@selected">
                    <a href="{$BASE}{$LANG_ABBR}{../../../@template}{@year}/{@month}/{@day}/">
                        <xsl:value-of select="."/>
                    </a>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="."/>
                </xsl:otherwise>
            </xsl:choose>
        </td>
    </xsl:template>

    <xsl:template match="toolbar[parent::component[@exttype='calendar']]">
        <xsl:apply-templates select="control[@id='previous']"/>
        <xsl:apply-templates select="control[@id='current']"/>
        <xsl:apply-templates select="control[@id='next']"/>
    </xsl:template>

    <xsl:template
            match="control[parent::toolbar[parent::component[@exttype='calendar']]]">
        <a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}{@year}/{@month}/">
            <xsl:choose>
                <xsl:when test="@id='current'">
                    <xsl:value-of select="@monthName"/>,
                    <xsl:value-of select="@year"/>
                </xsl:when>
                <xsl:when test="@id='next'">
                    <xsl:text disable-output-escaping="yes">&gt;</xsl:text>
                </xsl:when>
                <xsl:when test="@id='previous'">
                    <xsl:text disable-output-escaping="yes">&lt;</xsl:text>
                </xsl:when>
            </xsl:choose>
        </a>
    </xsl:template>

</xsl:stylesheet>
