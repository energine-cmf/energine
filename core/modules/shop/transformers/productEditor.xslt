<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <!-- компонент ProductEditor -->
    <!-- этот филд скорее всего будет удален - пока остается как есть -->
    <xsl:template match="field[ancestor::component[@class='ProductEditor'][@type='form'][@exttype='grid']][@name='smap_id']">
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
                    	<xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
                    </xsl:if>
                    <xsl:if test="@message">
            	        <xsl:attribute name="nrgn:message" xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
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
    
    <!-- поле ввода сегмента товара -->
    <xsl:template match="field[@name='product_segment'][ancestor::component[@class='ProductEditor']]">
    	<div class="field">
            <xsl:if test="not(@nullable)">
                <xsl:attribute name="class">field required</xsl:attribute>
            </xsl:if>
            <xsl:if test="@title">
                <div class="name">
                    <label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes"/></label>
                </div>
            </xsl:if>
    		<div class="control">
                <div class="smap_segment">
                    <span><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/></span><span id="smap_pid_segment"><xsl:value-of select="../field[@name='smap_id']/@segment"/></span>
                    <xsl:choose>
                        <xsl:when test="@mode='2'">
                            <input style="width: 150px;">
                                <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
                            </input>
                        </xsl:when>
                        <xsl:otherwise>
                            <span class="read current_segment"><xsl:value-of select="." disable-output-escaping="yes"/></span>
                            <input type="hidden" value="{.}">
                                <xsl:attribute name="name"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:attribute>
                            </input>
                        </xsl:otherwise>
                    </xsl:choose>/
                </div>
            </div>
    	</div>
    </xsl:template>
     
    <xsl:template match="field[@name='curr_id'][ancestor::component[@class='ProductEditor'][@type='form']]"/>    
    <!-- /компонент ProductEditor -->

</xsl:stylesheet>
