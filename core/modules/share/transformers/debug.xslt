<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template name="DEBUG_INFO">
	<div style="background-color:white;border:1px solid green;position:absolute;left:0px; top:0px;z-index:10000;text-align:left;overflow:auto;width:400px; height:500px;white-space:pre;">
    
    <xsl:for-each select="/document/debug_info/var">
    	<p style="padding:5px;">
            <xsl:value-of select="."/>
        </p>
    <xsl:if test="position()!=last()">
    	<hr/>
    </xsl:if>
    </xsl:for-each>

    </div>
</xsl:template>

</xsl:stylesheet>