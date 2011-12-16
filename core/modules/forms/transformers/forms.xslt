<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns="http://www.w3.org/1999/xhtml"
        xmlns:nrgn="http://energine.org">
        
    <xsl:template match="record[ancestor::component[@class='Form']]">
        <xsl:if test="ancestor::component/@componentAction='main' and field[@name='form_description']!=''">
            <div class="textblock"><xsl:value-of select="field[@name='form_description']" disable-output-escaping="yes"/></div>
        </xsl:if>
        <xsl:if test="field[@name='form_error_message']">
            <xsl:apply-templates select="field[@name='form_error_message']" mode="custom"/>
        </xsl:if>
        <xsl:apply-templates/>
        <xsl:if test="ancestor::component/@componentAction='main' and field[@name='form_post_description']!=''">
            <div class="textblock"><xsl:value-of select="field[@name='form_post_description']" disable-output-escaping="yes"/></div>
        </xsl:if>
    </xsl:template>

    <xsl:template match="field[(@name='form_description' or @name='form_post_description') and ancestor::component[@class='Form']]"/>
    
    <xsl:template match="field[@type='boolean' and ancestor::component[@class='Form']]">
        <div class="field">
    	    <xsl:attribute name="class">field<xsl:if test="not(@nullable)"> required</xsl:if></xsl:attribute>
    		<xsl:if test="@title ">
    		    <div class="name">
        			<label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes"/></label>
    				<xsl:if test="not(@nullable)"><span class="mark">*</span></xsl:if>
    			</div>
    		</xsl:if>
    		<div class="control" id="control_{@language}_{@name}">
                <select id="{@name}">
                    <xsl:attribute name="name">
                        <xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                    <xsl:if test="@nullable='1'">
                        <option></option>
                    </xsl:if>
                    <option value="1"><xsl:if test=".=1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if><xsl:value-of select="@yes"/></option>
                    <option value="0"><xsl:if test="not(.)"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if><xsl:value-of select="@no"/></option>
                </select>
            </div>
    	</div>
    </xsl:template>
    
    <xsl:template match="field[@type='file' and ancestor::component[@class='Form']]">
        <div class="field">
    	    <xsl:attribute name="class">field<xsl:if test="not(@nullable)"> required</xsl:if></xsl:attribute>
    		<xsl:if test="@title ">
    		    <div class="name">
        			<label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes"/></label>
    				<xsl:if test="not(@nullable)"><span class="mark">*</span></xsl:if>
    			</div>
    		</xsl:if>
    		<div class="control" id="control_{@language}_{@name}">
                <input type="file">
                    <xsl:attribute name="name">
                        <xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                    <xsl:if test="@pattern">
                        <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
                    </xsl:if>
                    <xsl:if test="@message">
                        <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
                    </xsl:if>
                    <xsl:if test="@message2">
                        <xsl:attribute name="nrgn:message2"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message2"/></xsl:attribute>
                    </xsl:if>
                </input>
            </div>
    	</div>
    </xsl:template>

    <xsl:template match="field[@name='form_error_message' and ancestor::component[@class='Form']]" mode="custom">
        <h2 class="error"><xsl:value-of select="@title"/>: <xsl:value-of select="."/></h2>
        <hr/>
    </xsl:template>

    <xsl:template match="field[@name='form_error_message' and ancestor::component[@class='Form']]"/>

</xsl:stylesheet>