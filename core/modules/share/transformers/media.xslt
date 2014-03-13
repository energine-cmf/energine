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
        <xsl:variable name="PLAYER_ID">player_<xsl:value-of select="generate-id()"/></xsl:variable>
        <script type="text/javascript" src="{$STATIC_URL}scripts/jwplayer/jwplayer.js"></script>
        <script type="text/javascript" src="{$STATIC_URL}scripts/Player.js"></script>
        <div id="{$PLAYER_ID}"/>
        <script type="text/javascript">
            var <xsl:value-of select="$PLAYER_ID"/> = new Player(
                '<xsl:value-of select="$PLAYER_ID"/>',
                '<xsl:value-of select="$RESIZER_URL"/>w<xsl:value-of select="$PLAYER_WIDTH"/>-h<xsl:value-of select="$PLAYER_HEIGHT"/>/<xsl:value-of select="$FILE"/>',
                ['<xsl:value-of select="$MEDIA_URL"/><xsl:value-of select="$FILE"/>'],
                '<xsl:value-of select="$PLAYER_WIDTH"/>',
                '<xsl:value-of select="$PLAYER_HEIGHT"/>',
                false
            );
        </script>
    </xsl:template>
    <!-- /Video player (JW Player) -->
    
</xsl:stylesheet>