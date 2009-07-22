<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="xml"
        version="1.0"
        encoding="utf-8"
        omit-xml-declaration="no"
        media-type="application/rss+xml"
        indent="no" />

    <xsl:template match="/">
        <rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
            <xsl:apply-templates />
        </rss>
    </xsl:template>

    <xsl:template match="document">
        <channel>
            <title><xsl:value-of select="properties/property[@name='title']"/></title>
            <link><xsl:value-of select="properties/property[@name='base']"/></link>
            <description></description>
            <pubDate>Thu, 18 Jun 2009 18:56:22 +0300</pubDate>
            <language><xsl:value-of select="properties/property[@name='lang']/@real_abbr"/></language>
            <xsl:apply-templates/>
          </channel>
    </xsl:template>

    <xsl:template match="component | layout | properties" />

    <xsl:template match="component[@class='RSSChannel']">
        <item>
              <title><![CDATA[Трали вали]]></title>
              <link>http://maanimo.com/nes/events/6-russkie-kollektory-zaymutsya-ukrainskimi-doljnikami</link>
              <description><![CDATA[аинский рынок коллекторских услуг. Об этом сообщает УНИАН со ссылкой на председателя совета директоров Центра ЮСБ Александра Федорова]]></description>
              <guid isPermaLink="false">http://maanimo.com/цarticles/49262</guid>
              <pubDate>Thu, 19 Jun 2009 15:56:12 +0300</pubDate>
              <comments>http://maanimo.com/newsыevents/4926-russkie-kollektory-zaymutsya-ukrainskimi-doljnikami#comments</comments>
            </item>
            <item>
              <title><![CDATA[Оппа]]></title>
              <link>http://maanimo.com/news/events/6-russkie-kollektory-zaymutsya-ukrainskimi-doljnikami</link>
              <description><![CDATA[Российское агентство Центр ЮСБ планирует в ближайшее время выйти на украинский рынок коллекторских услуг. Об этом сообщает УНИАН со ссылкой на председателя совета директоров Центра ЮСБ Александра Федорова]]></description>
              <guid isPermaLink="false">http://maanimo.com/articles/49262</guid>
              <pubDate>Thu, 19 Jun 2009 15:56:22 +0300</pubDate>
              <comments>http://maanimo.com/news/events/4926-russkie-kollektory-zaymutsya-ukrainskimi-doljnikami#comments</comments>
            </item>
            <item>
              <title><![CDATA[Русские коллекторы займутся украинскими должниками]]></title>
              <link>http://maanimo.com/news/events/4926-russkie-kollektory-zaymutsya-ukrainskimi-doljnikami</link>
              <description><![CDATA[Российское коллекторское агентство Центр ЮСБ планирует в ближайшее время выйти на украинский рынок коллекторских услуг. Об этом сообщает УНИАН со ссылкой на председателя совета директоров Центра ЮСБ Александра Федорова]]></description>
              <guid isPermaLink="false">http://maanimo.com/articles/4926</guid>
              <pubDate>Thu, 19 Jun 2009 15:56:22 +0300</pubDate>
              <comments>http://maanimo.com/news/events/4926-russkie-kollektory-zaymutsya-ukrainskimi-doljnikami#comments</comments>
            </item>
            <item>
              <title><![CDATA[МВФ ухудшил прогноз по ВВП для Украины]]></title>
              <link>http://maanimo.com/news/events/4925-mvf-uhudshil-prognoz-po-vvp-dlya-ukrainy</link>
              <description><![CDATA[Международный валютный фонд ухудшил прогноз падения ВВП Украины по итогам 2009 г. с 8%, как было объявлено в начале июня, до 12%]]></description>
              <guid isPermaLink="false">http://maanimo.com/articles/4925</guid>
              <pubDate>Thu, 28 Jun 2009 15:38:11 +0300</pubDate>
              <comments>http://maanimo.com/news/events/4925-mvf-uhudshil-prognoz-po-vvp-dlya-ukrainy#comments</comments>
            </item>
    </xsl:template>
</xsl:stylesheet>