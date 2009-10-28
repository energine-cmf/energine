<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

   <xsl:template match="recordset[parent::component[@class='FileLibrary'][@type='list']]">
    <xsl:variable name="FIRST_TAB_LANG" select="../tabs/tab[position()=1]/@id" />
    <div id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}" file_type="{../@allowed_file_type}">
        <ul class="tabs">
            <xsl:for-each select="../tabs/tab">
                <xsl:variable name="TAB_NAME" select="@name" />
                <xsl:variable name="TAB_LANG" select="@id" />
                <li>
                    <a href="#{generate-id(../.)}"><xsl:value-of select="$TAB_NAME" /></a>
                    <xsl:if test="$TAB_LANG">
                        <span class="data">{ lang: <xsl:value-of select="$TAB_LANG" /> }</span>
                    </xsl:if>
                </li>
            </xsl:for-each>
        </ul>
        <div class="paneContainer">
            <div id="{generate-id(../tabs)}">
                <div class="dirArea">
                    <div class="scrollHelper">
                    </div>
                </div>
            </div>
        </div>
    </div>
</xsl:template>

<xsl:template match="field[@name='upl_path']">
<div class="field">
        <xsl:if test="not(@nullable)">
		    <xsl:attribute name="class">field required</xsl:attribute>
		</xsl:if>
    <div class="name">
        <xsl:element name="label">
            <xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
            <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes" />
        </xsl:element>
    </div>
    <div class="control">
        <div class="image">
            <img>
                <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/>_preview</xsl:attribute>
            </img>
        </div>
        <xsl:element name="input">
            <xsl:attribute name="type">file</xsl:attribute>
            <xsl:attribute name="onchange"><xsl:value-of select="generate-id(ancestor::recordset)"/>.upload(this);</xsl:attribute>
            <xsl:attribute name="id"><xsl:value-of select="@name"/></xsl:attribute>
            <xsl:attribute name="name">file</xsl:attribute>
            <xsl:attribute name="link"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
            <xsl:attribute name="preview"><xsl:value-of select="generate-id(.)"/>_preview</xsl:attribute>
    </xsl:element>
        <xsl:element name="input">
            <xsl:attribute name="type">hidden</xsl:attribute>
            <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
            <xsl:attribute name="name"><xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose></xsl:attribute>
            <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
            <xsl:if test="@pattern">
                <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
                </xsl:if>
            <xsl:if test="@message">
                <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
            </xsl:if>
        </xsl:element>
    </div>
</div>
</xsl:template>
</xsl:stylesheet>