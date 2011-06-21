<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <!--
        Все поля для анкет отмечаются по очереди css-классами first_field и second_field.
        Это сделано для того, чтобы разместить поля в две колонки.
        Hidden-поля могут быть только первыми или последними, иначе собьется последовательность.
    -->
    <xsl:template match="field[(@type != 'hidden' and @type != 'captcha') and ancestor::component[@class='Form']]">
    	<div class="field">
            <xsl:attribute name="class">field<xsl:if test="not(@nullable) and @type != 'boolean'"> required</xsl:if><xsl:choose>
                <xsl:when test="position() div 2 = floor(position() div 2)"> first_field</xsl:when>
                <xsl:otherwise> second_field</xsl:otherwise>
            </xsl:choose></xsl:attribute>
    		<xsl:if test="@title and @type != 'boolean'">
    		    <div class="name">
        			<label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes" /></label>
    				<xsl:if test="not(@nullable) and not(ancestor::component/@exttype = 'grid') and not(ancestor::component[@class='TextBlockSource'])"><span class="mark">*</span></xsl:if>
    			</div>
    		</xsl:if>
    		<div class="control" id="control_{@language}_{@name}">                
        		<xsl:apply-imports/>
            </div>
    	</div>
    </xsl:template>

    <xsl:template match="field[@type='boolean' and ancestor::component[@class='Form']]">
        <div class="field">
    	    <xsl:attribute name="class">field<xsl:if test="not(@nullable)"> required</xsl:if><xsl:choose>
                <xsl:when test="position() div 2 = floor(position() div 2)"> first_field</xsl:when>
                <xsl:otherwise> second_field</xsl:otherwise>
            </xsl:choose></xsl:attribute>
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
    
    <xsl:template match="field[@type='file' and ancestor::component[@class='Form']]">
        <div class="field">
    	    <xsl:attribute name="class">field<xsl:if test="not(@nullable)"> required</xsl:if><xsl:choose>
                <xsl:when test="position() div 2 = floor(position() div 2)"> first_field</xsl:when>
                <xsl:otherwise> second_field</xsl:otherwise>
            </xsl:choose></xsl:attribute>
    		<xsl:if test="@title ">
    		    <div class="name">
        			<label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes" /></label>
    				<xsl:if test="not(@nullable)"><span class="mark">*</span></xsl:if>
    			</div>
    		</xsl:if>
    		<div class="control" id="control_{@language}_{@name}">
                <input type="file" >
                    <xsl:attribute name="name">
                        <xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
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

</xsl:stylesheet>