<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="text" encoding="utf-8" indent="no" />

    <xsl:template match="/">
        <xsl:apply-templates select="//component[@name='robots']"/>
    </xsl:template>

    <xsl:template match="component[@name='robots']">
        <xsl:apply-templates select="recordset"/>
    </xsl:template>

    <xsl:template match="component[@name='robots']/recordset">
        <xsl:apply-templates select="record"/>
    </xsl:template>

    <xsl:template match="component[@name='robots']/recordset/record">
        <xsl:value-of select="field[@name='entry']"/>
        <xsl:call-template name="nl"/>
        <xsl:call-template name="nl"/>
    </xsl:template>

    <xsl:template name="nl">
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

</xsl:stylesheet>
