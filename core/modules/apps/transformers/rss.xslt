<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml"
            version="1.0"
            encoding="utf-8"
            omit-xml-declaration="no"
            indent="no" />

    <xsl:variable name="DOC_PROPS" select="/document/properties/property" />

    <xsl:variable name="ID" select="$DOC_PROPS[@name='ID']" />
    <xsl:variable name="BASE" select="$DOC_PROPS[@name='base']" />
    <xsl:variable name="LANG_ABBR" select="$DOC_PROPS[@name='lang']/@real_abbr" />
    <xsl:variable name="COMPONENTS" select="//component[@name]"/>
    <xsl:variable name="TRANSLATION" select="/document/translations/translation"/>


    <xsl:template match="/">
        <rss version="2.0">
            <xsl:apply-templates select="//component[@class='NewsFeed'][@componentAction='rss']"></xsl:apply-templates>
        </rss>
    </xsl:template>

    <xsl:template match="component[@class='NewsFeed'][@componentAction='rss']">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@class='NewsFeed'][@componentAction='rss']]">
        <xsl:if test="not(@empty)">
            <channel>
            <title><xsl:value-of select="$TRANSLATION[@const='TXT_RSS_NEWS_TITLE']"/></title>
            <link><xsl:value-of select="$BASE"/></link>
            <language><xsl:value-of select="$LANG_ABBR"/></language>
            <xsl:apply-templates/>
            </channel>
        </xsl:if>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='NewsFeed'][@componentAction='rss']]">
        <item>
            <guid><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/>/<xsl:value-of select="../../@template"/><xsl:value-of select="field[@name='news_segment']"/></guid>
            <title>
                <xsl:value-of select="field[@name='news_title']"/>
            </title>
            <description><xsl:value-of select="field[@name='news_announce_rtf']"/></description>
            <pubDate><xsl:value-of select="field[@name='news_date']"/></pubDate>
            <link><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/>/<xsl:value-of select="../../@template"/><xsl:value-of select="field[@name='news_segment']"/>/</link>
        </item>
    </xsl:template>

    <xsl:template match="toolbar[parent::component[@class='NewsFeed']]"/>

</xsl:stylesheet>