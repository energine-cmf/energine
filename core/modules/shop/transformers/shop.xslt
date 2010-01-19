<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <!-- компонент CurrencySwitcher -->
    <xsl:template match="component[@class='CurrencySwitcher']">
        <div id="currencySwitcher">
            <form action="" method="POST">
                <xsl:apply-templates/>
            </form>
        </div>        
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@class='CurrencySwitcher']]">
        <div>
            <strong><xsl:value-of select="$TRANSLATION[@const='MSG_SWITCHER_TIP']" />:</strong>
            <select name="current_currency" onchange="this.form.submit();">
                <xsl:apply-templates/>
            </select>
        </div>
        <div style="padding-top: 5px;">
            <strong><xsl:value-of select="$TRANSLATION[@const='TXT_CURRENCY_RATE']" />:<xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></strong>
            <xsl:for-each select="record[field[@name='curr_id']!=1]">
                <xsl:value-of select="field[@name='curr_name']"/><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                <xsl:value-of select="field[@name='curr_rate']"/>
                <xsl:if test="position()!=last()">, </xsl:if>
            </xsl:for-each>
        </div>
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@class='CurrencySwitcher']]">
        <option>
            <xsl:attribute name="value"><xsl:value-of select="field[@name='curr_id']"/></xsl:attribute>
            <xsl:if test="field[@name='is_current']=1">
                <xsl:attribute name="selected">selected</xsl:attribute>
            </xsl:if>
            <xsl:value-of select="field[@name='curr_name']"/> 
        </option>
    </xsl:template>
    <!-- /компонент CurrencySwitcher -->
    
    <!-- компонент PriceLoader -->    
    <xsl:template match="field[ancestor::component[@class='PriceLoader']][@type='file']">
    	<div class="field">
    		<xsl:if test="@title">
    		    <div class="name">
        			<label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes" />:</label>
        			<xsl:if test="not(@nullable) and @type != 'boolean'">
    				    <span style="color: red;"> *</span>
    				</xsl:if>
        			<xsl:text> </xsl:text>
    			</div>
    		</xsl:if>
    		<div class="control">
                <input type="file" id="{@name}" name="{@name}">                    
                    <xsl:if test="@pattern">
                        <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
                    </xsl:if>
                    <xsl:if test="@message">
                        <xsl:attribute name="nrgn:message" xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
                    </xsl:if>
                </input>
            </div>
    	</div>
    </xsl:template>

    <xsl:template match="field[@name='price_loader_result'][ancestor::component[@class='PriceLoader']]">
    	<span style="color: red;"><xsl:value-of select="." disable-output-escaping="yes"/></span>
    </xsl:template>
    <!-- /компонент PriceLoader -->

</xsl:stylesheet>