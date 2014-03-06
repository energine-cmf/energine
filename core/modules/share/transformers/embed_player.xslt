<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:str="http://exslt.org/strings">
    <xsl:include href="single.xslt"/>

    <xsl:template match="component[@componentAction='embedPlayer']">
        <xsl:variable name="DATA" select="recordset/record[1]/field"/>
        <html>
            <head>
                <xsl:choose>
                    <xsl:when test="document/@debug=1">
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools-debug.js"></script>
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools-more-debug.js"></script>
                    </xsl:when>
                    <xsl:otherwise>
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools.js"></script>
                        <script type="text/javascript" src="{$STATIC_URL}scripts/mootools-more.js"></script>
                    </xsl:otherwise>
                </xsl:choose>
                <meta property="og:video" content="{$MEDIA_URL}{$DATA[@name='file']}"/>
                <meta property="og:video:type" content="application/x-shockwave-flash"/>
                <meta property="og:image" content="{$RESIZER_URL}w0-h0/{$DATA[@name='file']}"/>
                <script type="text/javascript" src="{$STATIC_URL}scripts/jwplayer/jwplayer.js"></script>
                <script type="text/javascript">jwplayer.key="5aUJ4XX+yoQjzSKgeqoNFXzfKqvkZ5XsNEZRqNu1zDU=";</script>
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
                <div id="embed_player" style="height: 100%; width: 100%;">
                </div>
                <script type="text/javascript">
                    jwplayer('embed_player').setup(
                        {
                            file: '<xsl:value-of select="$MEDIA_URL"/><xsl:value-of select="$DATA[@name='file']"/>',
                            image: '<xsl:value-of select="$RESIZER_URL"/>w0-h0/<xsl:value-of select="$DATA[@name='file']"/>',
                            width: '100%',
                            height: '100%'
                        }
                    );
                </script>
            </body>
        </html>
    </xsl:template>

</xsl:stylesheet>