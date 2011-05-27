<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="field[@type='boolean' and ancestor::component[@class='Form']]">
        <div class="field">
    	    <xsl:if test="not(@nullable)">
    		    <xsl:attribute name="class">field required</xsl:attribute>
    		</xsl:if>
    		<xsl:if test="@title ">
    		    <div class="name">
        			<label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes" /></label>
    				<xsl:if test="not(@nullable)"><span class="mark">*</span></xsl:if>
    			</div>
    		</xsl:if>
    		<div class="control" id="control_{@language}_{@name}">
                <select id="{@name}">
                    <xsl:attribute name="name">
                        <xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                    <xsl:if test="@nullable='1'">
                        <option></option>
                    </xsl:if>
                    <option value="1"><xsl:value-of select="@yes"/></option>
                    <option value="0"><xsl:value-of select="@no"/></option>
                </select>
            </div>
    	</div>
    </xsl:template>

</xsl:stylesheet>