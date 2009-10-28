<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

    <xsl:template match="component[@class='LangSwitcher']">
    	<ul id="langswitcher">
    		<xsl:apply-templates />
    	</ul>
    </xsl:template>

    <xsl:template match="component[@class='LangSwitcher']/recordset">
        <xsl:apply-templates />
    </xsl:template>

    <xsl:template match="component[@class='LangSwitcher']/recordset/record">
        <li>
            <xsl:choose>
                <xsl:when test="$LANG_ID != field[@name='lang_id']">
                    <a href="{field[@name='lang_url']}"><span><xsl:value-of select="field[@name='lang_name']"/></span></a>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:attribute name="class">current</xsl:attribute>
                    <a><span><xsl:value-of select="field[@name='lang_name']"/></span></a>
                </xsl:otherwise>
            </xsl:choose>
        </li>
    </xsl:template>

</xsl:stylesheet>