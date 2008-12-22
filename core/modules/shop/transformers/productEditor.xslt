<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="field[ancestor::component[@class='ProductEditor'][@type='form']][@name='smap_id']">
	<div class="field">
	    <xsl:if test="not(@nullable)">
		    <xsl:attribute name="class">field required</xsl:attribute>
		</xsl:if>
		<xsl:if test="@title">
		    <div class="name">
    			<xsl:element name="label">
    				<xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
    				<xsl:value-of select="@title" disable-output-escaping="yes" />
    			</xsl:element>
    			<xsl:text> </xsl:text>
			</div>
		</xsl:if>
		<div class="control">
            <xsl:variable name="FIELD_ID"><xsl:value-of select="generate-id()"/></xsl:variable>
            <span class="read" id="s_{$FIELD_ID}" style="margin-right:5px;"><xsl:value-of select="@data_name" disable-output-escaping="yes" /></span>
            <xsl:element name="input">
                <xsl:attribute name="type">hidden</xsl:attribute>
                <xsl:attribute name="id">h_<xsl:value-of select="$FIELD_ID"/></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                <xsl:if test="@pattern">
                	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
                </xsl:if>
                <xsl:if test="@message">
        	        <xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
                </xsl:if>
        		<xsl:attribute name="name"><xsl:choose>
					<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
					<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
				</xsl:choose></xsl:attribute>            	
            </xsl:element>
    		<button type="button" id="{generate-id(.)}" hidden_field="h_{$FIELD_ID}" span_field="s_{$FIELD_ID}">...</button>
            <script type="text/javascript">
            $('<xsl:value-of select="generate-id(.)"/>').onclick = function(event){
                var event = new Event(event);
                <xsl:value-of select="generate-id(../..)"/>.showTree(event.target);
            }
            </script>
        </div>
	</div>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductEditor'][@type='form']][@name='product_price']">
<div class="field">
	    <xsl:if test="not(@nullable)">
		    <xsl:attribute name="class">field required</xsl:attribute>
		</xsl:if>
		<xsl:if test="@title">
		    <div class="name">
    			<label for="{@name}">
    				<xsl:value-of select="@title" disable-output-escaping="yes" />
    			</label>
			</div>
		</xsl:if>
		<div class="control">
    		<xsl:element name="input">
            <xsl:attribute name="type">text</xsl:attribute>
            <xsl:attribute name="name"><xsl:choose>
                        <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                        <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                    </xsl:choose></xsl:attribute>
            <xsl:if test="@length">
                <xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
            </xsl:if>
            <xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
            <xsl:attribute name="style">width:50px;</xsl:attribute>
            <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
            <xsl:if test="@pattern">
                <xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="@message">
                <xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
            </xsl:if>
        </xsl:element>
        <xsl:variable name="CURRENCY_SELECTOR" select="../field[@name='curr_id']"></xsl:variable> 
        <xsl:element name="select">
        <xsl:attribute name="style">width:200px;</xsl:attribute>
		<xsl:attribute name="name"><xsl:choose>
					<xsl:when test="$CURRENCY_SELECTOR/@tableName"><xsl:value-of select="$CURRENCY_SELECTOR/@tableName" />[<xsl:value-of select="$CURRENCY_SELECTOR/@name" />]</xsl:when>
					<xsl:otherwise><xsl:value-of select="$CURRENCY_SELECTOR/@name" /></xsl:otherwise>
				</xsl:choose></xsl:attribute>
        <xsl:attribute name="id"><xsl:value-of select="$CURRENCY_SELECTOR/@name"/></xsl:attribute>
		<xsl:if test="$CURRENCY_SELECTOR/@nullable='1'">
			<xsl:element name="option"></xsl:element>
		</xsl:if>
		<xsl:for-each select="$CURRENCY_SELECTOR/options/option">
			<xsl:element name="option">
				<xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
				<xsl:if test="@selected">
					<xsl:attribute name="selected">selected</xsl:attribute>
				</xsl:if>
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:for-each>
	</xsl:element>
        </div>
	</div>    
</xsl:template>

<xsl:template match="field[ancestor::component[@class='ProductEditor'][@type='form']][@name='curr_id']" />


<xsl:template match="field[ancestor::component[@class='ProductEditor']][@name='product_segment']">
	<div class="field">
		    <xsl:attribute name="class">field required</xsl:attribute>
		    <div class="name">
    			<label for="{@name}">
    				<xsl:value-of select="@title" disable-output-escaping="yes" />
    			</label>
			</div>
		<div class="control" style="font-size: 10px; color: gray;">
            <span><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/></span><span id="smap_pid_segment"><xsl:value-of select="../field[@name='smap_id']/@segment"/></span>
            <xsl:choose>
                <xsl:when test="@mode='2'">
                    <xsl:element name="input">
                        <xsl:attribute name="type">text</xsl:attribute>
                        <xsl:attribute name="style">width:150px;</xsl:attribute>
                        <xsl:attribute name="name"><xsl:choose>
                                    <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                                    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                                </xsl:choose></xsl:attribute>
                        <xsl:if test="@length">
                            <xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
                        </xsl:if>
                        <xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                        <xsl:if test="@pattern">
                            <xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
                        </xsl:if>
                        <xsl:if test="@message">
                            <xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
                        </xsl:if>
                    </xsl:element>        
                </xsl:when>
                <xsl:otherwise>
                    <span class="read" style="color: #000; font-size: 11px;"><xsl:value-of select="." disable-output-escaping="yes" /></span>
                    <xsl:element name="input">
                        <xsl:attribute name="type">hidden</xsl:attribute>
                        <xsl:attribute name="name"><xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                        </xsl:choose></xsl:attribute>
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    </xsl:element>
                </xsl:otherwise>
            </xsl:choose>/    
        </div>
	</div>
</xsl:template>

</xsl:stylesheet>
