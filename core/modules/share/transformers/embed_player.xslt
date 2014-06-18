<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:str="http://exslt.org/strings">
    <xsl:include href="single.xslt"/>
    <xsl:include href="media.xslt"/>

    <xsl:template match="component[@componentAction='embedPlayer']">
        <xsl:variable name="DATA" select="recordset/record[1]/field"/>
        <html>
            <head>
                <xsl:choose>
                    <xsl:when test="document/@debug=1">
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools-debug.js"></script>
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools-more-debug.js"></script>
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools-ext-debug.js"></script>
                    </xsl:when>
                    <xsl:otherwise>
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools.js"></script>
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools-more.js"></script>
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools-ext.js"></script>
                    </xsl:otherwise>
                </xsl:choose>
                <meta property="og:video" content="{$MEDIA_URL}{$DATA[@name='file']}"/>
                <meta property="og:video:type" content="application/x-shockwave-flash"/>
                <meta property="og:image" content="{$RESIZER_URL}w0-h0/{$DATA[@name='file']}"/>
                <style type="text/css">
                    html, body {
                        height: 100%;
                        padding: 0;
                        margin: 0;
                        border: 0;
                        overflow: hidden;
                        background-color: #000000;
                    }
                </style>
            </head>
            <body>
                <xsl:call-template name="VIDEO_PLAYER">
                    <xsl:with-param name="PLAYER_WIDTH">100%</xsl:with-param>
                    <xsl:with-param name="PLAYER_HEIGHT">100%</xsl:with-param>
                    <xsl:with-param name="FILE"><xsl:value-of select="$DATA[@name='file']"/></xsl:with-param>
                </xsl:call-template>
            </body>
        </html>
    </xsl:template>

</xsl:stylesheet>