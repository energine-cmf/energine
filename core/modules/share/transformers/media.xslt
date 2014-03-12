<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    >

    <!-- Video player (JW Player) -->
    <xsl:template name="VIDEO_PLAYER">
        <xsl:param name="PLAYER_WIDTH"/>
        <xsl:param name="PLAYER_HEIGHT"/>
        <xsl:param name="FILE"/>
        <xsl:variable name="PLAYER_ID">player-<xsl:value-of select="generate-id()"/></xsl:variable>
        <script type="text/javascript" src="{$STATIC_URL}scripts/jwplayer/jwplayer.js"></script>
        <div id="{$PLAYER_ID}"/>
        <script type="text/javascript">
            jwplayer('<xsl:value-of select="$PLAYER_ID"/>').setup(
                {
                    file: '<xsl:value-of select="$MEDIA_URL"/><xsl:value-of select="$FILE"/>',
                    image: '<xsl:value-of select="$RESIZER_URL"/>w0-h0/<xsl:value-of select="$FILE"/>',
                    width: '<xsl:value-of select="$PLAYER_WIDTH"/><xsl:if test="number($PLAYER_WIDTH) = $PLAYER_WIDTH">px</xsl:if>',
                    height: '<xsl:value-of select="$PLAYER_HEIGHT"/><xsl:if test="number($PLAYER_HEIGHT) = $PLAYER_HEIGHT">px</xsl:if>'
                }<!-- We can set width and height in % also, so we add 'px' suffix only if width and height are numeric -->
            );
        </script>
    </xsl:template>
    <!-- /Video player (JW Player) -->
    
</xsl:stylesheet>