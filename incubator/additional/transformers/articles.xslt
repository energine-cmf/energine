<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="record[ancestor::component[@class='ArticlesFeed'][@type='list']]">
    <li>
        <xsl:if test="$COMPONENTS[@editable]">
            <xsl:attribute name="record"><xsl:value-of select="field[@index='PRI']"/></xsl:attribute>
        </xsl:if>
        <h4><a href="{$BASE}{$LANG_ABBR}{../../@template}{field[@name='art_id']}/"><xsl:value-of select="field[@name='art_name']"/></a></h4>
        <div>
            <xsl:value-of select="field[@name='art_announce_rtf']" disable-output-escaping="yes"/>
            <strong><xsl:value-of select="field[@name='art_date']"/></strong>
        </div>
    </li>
</xsl:template>

</xsl:stylesheet>