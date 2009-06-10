<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
    Тут собраны правила вывода всех полей
-->
<!--
    Шаблон - контроллер для любого поля из компонента типа форм
    В зависимости от типа поля вызывает соответствующий именованный шаблон
-->
<xsl:template match="field[ancestor::component[@type='form']]">
	<div class="field">
	    <xsl:if test="not(@nullable) and @type != 'boolean'">
		    <xsl:attribute name="class">field required</xsl:attribute>
		</xsl:if>
		<xsl:if test="@title and @type != 'boolean'">
		    <div class="name">
    			<label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes" /></label>
				<xsl:if test="not(@nullable) and not(ancestor::component/@exttype = 'grid')"><span class="mark">*</span></xsl:if>
                <xsl:if test="@nullable and ancestor::component/@exttype = 'grid'">
                    (<a href="#" message1="{../../../translations/translation[@const='TXT_OPEN_FIELD']}" message0="{../../../translations/translation[@const='TXT_CLOSE_FIELD']}">
                    <xsl:attribute name="onclick">return showhideField(this, '<xsl:value-of select="@name"/>'<xsl:if test="@language">, <xsl:value-of select="@language"/>
                    </xsl:if>);</xsl:attribute>
                    <xsl:attribute name="is_hidden">
                        <xsl:choose>
                            <xsl:when test="@type='select'">
                                <xsl:choose>
                                    <xsl:when test="not(options/option/@selected)">1</xsl:when>
                                    <xsl:otherwise>0</xsl:otherwise>
                                </xsl:choose>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:choose>
                                    <xsl:when test=".=''">1</xsl:when>
                                    <xsl:otherwise>0</xsl:otherwise>
                                </xsl:choose>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                    <xsl:choose>
                        <xsl:when test="@type='select'">
                            <xsl:choose>
                                <xsl:when test="not(options/option/@selected)">
                                    <xsl:value-of select="../../../translations/translation[@const='TXT_OPEN_FIELD']"/>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="../../../translations/translation[@const='TXT_CLOSE_FIELD']"/>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:choose>
                                <xsl:when test=".=''">
                                    <xsl:value-of select="../../../translations/translation[@const='TXT_OPEN_FIELD']"/>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="../../../translations/translation[@const='TXT_CLOSE_FIELD']"/>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:otherwise>
                    </xsl:choose>
                    </a>)
                </xsl:if>
                <xsl:if test="@help">
                    <xsl:variable name="HELP_IMG"><xsl:value-of select="generate-id()"/></xsl:variable>
                    <img src="images/help.gif" width="11" height="11" border="0" title="{@help}"/>
                </xsl:if>
			</div>
		</xsl:if>

		<div class="control" id="control_{@language}_{@name}">
            <xsl:if test="@nullable and ancestor::component/@exttype = 'grid'">
                <xsl:choose>
                    <xsl:when test="@type='select'">
                        <xsl:if test="not(options/option/@selected)">
                            <xsl:attribute name="style">display:none;</xsl:attribute>
                        </xsl:if>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:if test=".=''">
                            <xsl:attribute name="style">display:none;</xsl:attribute>
                        </xsl:if>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:if>
    		<xsl:choose>
    			<xsl:when test="@type='string'">
    				<xsl:call-template name="STRING"/>
    			</xsl:when>
    			<xsl:when test="@type='integer'">
    				<xsl:call-template name="INTEGER"/>
    			</xsl:when>
    			<xsl:when test="@type='email'">
    				<xsl:call-template name="EMAIL"/>
    			</xsl:when>
    			<xsl:when test="@type='phone'">
    				<xsl:call-template name="PHONE"/>
    			</xsl:when>
    			<xsl:when test="@type='float'">
    				<xsl:call-template name="FLOAT"/>
    			</xsl:when>
    			<xsl:when test="@type='password'">
    				<xsl:call-template name="PASSWORD"/>
    			</xsl:when>
    			<xsl:when test="@type='boolean'">
    				<xsl:call-template name="BOOLEAN"/>
    			</xsl:when>
    			<xsl:when test="@type='image'">
    				<xsl:call-template name="IMAGE"/>
    			</xsl:when>
    			<xsl:when test="@type='file'">
    				<xsl:call-template name="FILE" />
    			</xsl:when>
    			<xsl:when test="@type='select'">
    				<xsl:call-template name="SELECT"/>
    			</xsl:when>
    			<xsl:when test="@type='multi'">
    				<xsl:call-template name="MULTI"/>
    			</xsl:when>
    			<xsl:when test="@type='text'">
    				<xsl:call-template name="TEXT"/>
    			</xsl:when>
    			<xsl:when test="@type='htmlblock'">
    				<xsl:call-template name="HTML_EDITOR"/>
    			</xsl:when>
                <xsl:when test="@type='htmlblocksource'">
                    <xsl:call-template name="HTML_SOURCE_EDITOR"/>
                </xsl:when>
    			<xsl:when test="@type='datetime'">
    				<xsl:call-template name="DATE_TIME"/>
    			</xsl:when>
    			<xsl:when test="@type='date'">
    				<xsl:call-template name="DATE"/>
    			</xsl:when>
    			<xsl:when test="@type='pfile'">
    				<xsl:call-template name="PFILE"/>
    			</xsl:when>
    			<xsl:when test="@type='prfile'">
    				<xsl:call-template name="PRFILE"/>
    			</xsl:when>
    			<xsl:otherwise>
    				<xsl:call-template name="STRING"/>
    			</xsl:otherwise>
    		</xsl:choose>
        </div>
	</div>
</xsl:template>

<!-- Строковое поле -->
<xsl:template name="STRING">
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
		<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <xsl:if test="@pattern">
        	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
	</xsl:element>
</xsl:template>

<!-- поле для ввода Email -->
<xsl:template name="EMAIL">
	<xsl:element name="input">
		<xsl:attribute name="type">text</xsl:attribute>
		<xsl:attribute name="name"><xsl:choose>
					<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
					<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
				</xsl:choose></xsl:attribute>
		<xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
		<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <xsl:if test="@pattern">
        	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
	</xsl:element>
</xsl:template>

<!-- поле для ввода телефона-->
<xsl:template name="PHONE">
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
		<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <xsl:if test="@pattern">
        	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
	</xsl:element>
</xsl:template>


<!-- Строковое поле -->
<xsl:template name="INTEGER">
	<xsl:element name="input">
		<xsl:attribute name="type">text</xsl:attribute>
		<xsl:attribute name="name"><xsl:choose>
					<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
					<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
				</xsl:choose></xsl:attribute>
		<xsl:if test="@length">
			<xsl:attribute name="maxlength">5</xsl:attribute>
		</xsl:if>
		<xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
		<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <xsl:attribute name="class">int</xsl:attribute>
        <xsl:attribute name="length">5</xsl:attribute>
        <xsl:if test="@pattern">
        	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
	</xsl:element>
</xsl:template>


<!-- Строковое поле -->
<xsl:template name="FLOAT">
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
		<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <xsl:if test="@pattern">
        	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:attribute name="class">float</xsl:attribute>
        <xsl:if test="@message">
        	<xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
	</xsl:element>
</xsl:template>

<!-- Поле пароля -->
<xsl:template name="PASSWORD">
	<xsl:element name="input">
		<xsl:attribute name="type">password</xsl:attribute>
		<xsl:attribute name="name"><xsl:choose>
				<xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
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
</xsl:template>

<!-- Поле логического типа -->
<xsl:template name="BOOLEAN">
	<xsl:variable name="FIELD_NAME">
        <xsl:choose>
            <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
			<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <input type="hidden" name="{$FIELD_NAME}" value="0" />
    <input class="checkbox" type="checkbox" id="{@name}" name="{$FIELD_NAME}" style="width:auto;" value="1">
        <xsl:if test=". = 1">
            <xsl:attribute name="checked">checked</xsl:attribute>
		</xsl:if>
    </input>
	<label for="{@name}"><xsl:value-of select="concat(' ', @title)" disable-output-escaping="yes" /></label>
</xsl:template>

<xsl:template name="IMAGE">
    <div class = "image">
            <xsl:element name="img">
            <xsl:if test=".!=''">
                <xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
            </xsl:if>
            <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/>_preview</xsl:attribute>
        </xsl:element>
    </div>

    <xsl:element name="input">
        <xsl:attribute name="type">text</xsl:attribute>
        <xsl:attribute name="class">file</xsl:attribute>
        <xsl:attribute name="readonly">readonly</xsl:attribute>
        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <xsl:attribute name="name"><xsl:choose>
					<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
					<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
		</xsl:choose></xsl:attribute>
        <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
        <xsl:if test="@pattern">
        	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>

    </xsl:element>
    <button onclick="{generate-id(../..)}.openFileLib(this);" type="button" link="{generate-id(.)}" preview="{generate-id(.)}_preview">...</button>
</xsl:template>

<xsl:template name="FILE">
    <div class="file">
        <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/>_preview</xsl:attribute>
    </div>
    <xsl:if test=".!=''">
        <a href="{.}" target="_blank"><xsl:value-of select="."/></a>
    </xsl:if>
    <div>

    </div>
    <xsl:element name="input">
        <xsl:attribute name="type">text</xsl:attribute>
        <xsl:attribute name="class">file</xsl:attribute>
        <xsl:attribute name="readonly">readonly</xsl:attribute>
        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <xsl:attribute name="name"><xsl:choose>
					<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
					<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
		</xsl:choose></xsl:attribute>
        <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
        <xsl:if test="@pattern">
        	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </xsl:element>
    <button onclick="{generate-id(../..)}.openFileLib(this);" type="button" link="{generate-id(.)}" preview="{generate-id(.)}_preview">...</button>
</xsl:template>

<xsl:template name="PFILE">
    <div class="image">
        <img alt="" border="0" id="{generate-id(.)}_preview">
            <xsl:if test="@is_image">
                <xsl:attribute name="style">display:hidden;</xsl:attribute>
                <xsl:if test=".!=''">
                    <xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
                </xsl:if>
            </xsl:if>
        </img>
    </div>

    <div style="margin-bottom:5px;">
        <a href="{.}" target="_blank" id='{generate-id(.)}_link'><xsl:value-of select="."/></a>
    </div>
    <xsl:variable name="FIELD_ID">tmp_<xsl:value-of select="generate-id()"/></xsl:variable>
    <input type="file">
        <xsl:attribute name="id"><xsl:value-of select="$FIELD_ID"/></xsl:attribute>
        <xsl:attribute name="name">file</xsl:attribute>
        <xsl:attribute name="field"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
        <xsl:attribute name="link"><xsl:value-of select="generate-id(.)"/>_link</xsl:attribute>
        <xsl:attribute name="preview"><xsl:value-of select="generate-id(.)"/>_preview</xsl:attribute>
        <xsl:attribute name="onchange"><xsl:value-of select="generate-id(ancestor::recordset)"/>.upload.bind(<xsl:value-of select="generate-id(ancestor::recordset)"/>)(this);</xsl:attribute>
    </input>
    <input type="hidden">
        <xsl:attribute name="name"><xsl:choose>
            <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
            <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
        </xsl:choose></xsl:attribute>
        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
        <xsl:if test="@pattern">
            <xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
            </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
</xsl:template>

<xsl:template name="PRFILE">
    <div class="image">
        <img alt="" border="0" id="{generate-id(.)}_preview">
            <xsl:if test="@is_image">
                <xsl:attribute name="style">display:hidden;</xsl:attribute>
                <xsl:if test=".!=''">
                    <xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
                </xsl:if>
            </xsl:if>
        </img>
    </div>

    <div style="margin-bottom:5px;">
        <a href="{.}" target="_blank" id='{generate-id(.)}_link'><xsl:value-of select="."/></a>
    </div>
    <xsl:variable name="FIELD_ID">tmp_<xsl:value-of select="generate-id()"/></xsl:variable>
    <input type="file">
        <xsl:attribute name="id"><xsl:value-of select="$FIELD_ID"/></xsl:attribute>
        <xsl:attribute name="name">file</xsl:attribute>
        <xsl:attribute name="field"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
        <xsl:attribute name="link"><xsl:value-of select="generate-id(.)"/>_link</xsl:attribute>
        <xsl:attribute name="preview"><xsl:value-of select="generate-id(.)"/>_preview</xsl:attribute>
        <xsl:attribute name="protected">protected</xsl:attribute>
        <xsl:attribute name="onchange"><xsl:value-of select="generate-id(ancestor::recordset)"/>.upload.bind(<xsl:value-of select="generate-id(ancestor::recordset)"/>)(this);</xsl:attribute>
    </input>
    <input type="hidden">
        <xsl:attribute name="name"><xsl:choose>
            <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
            <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
        </xsl:choose></xsl:attribute>
        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
        <xsl:if test="@pattern">
            <xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
            </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
</xsl:template>

<!-- Поле выбора из списка -->
<xsl:template name="SELECT">
	<xsl:element name="select">
		<xsl:attribute name="name"><xsl:choose>
					<xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
					<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
				</xsl:choose></xsl:attribute>
        <xsl:attribute name="id"><xsl:value-of select="@name"/></xsl:attribute>
		<xsl:if test="@nullable='1'">
			<xsl:element name="option"></xsl:element>
		</xsl:if>
		<xsl:for-each select="options/option">
			<xsl:element name="option">
				<xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
				<xsl:if test="@selected">
					<xsl:attribute name="selected">selected</xsl:attribute>
				</xsl:if>
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:for-each>
	</xsl:element>
</xsl:template>


<!-- Поле множественного выбора -->
<xsl:template name="MULTI">
    <xsl:variable name="NAME"><xsl:choose>
        <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
        <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
        </xsl:choose>[]</xsl:variable>
        <div style="margin-left:20px;padding-top:5px;">
            <xsl:for-each select="options/option">
                <div>
                    <xsl:element name="input">
                        <xsl:attribute name="type">checkbox</xsl:attribute>
                        <xsl:attribute name="style">width:auto;vertical-align:middle;border:none;</xsl:attribute>
                        <xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
                        <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
                        <xsl:attribute name="name"><xsl:value-of select="$NAME"/></xsl:attribute>
                        <xsl:if test="@selected">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:if>
                    </xsl:element>
                    <label for="{generate-id(.)}"><xsl:value-of select="."/></label>
                </div>
            </xsl:for-each>
        </div>
</xsl:template>

<!-- Поле типа текст -->
<xsl:template name="TEXT">
	<xsl:element name="textarea">
        <xsl:attribute name="id"><xsl:value-of select="@name"/></xsl:attribute>
		<xsl:attribute name="name"><xsl:choose>
					<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
					<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
				</xsl:choose></xsl:attribute>
		<xsl:if test="@pattern">
        	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
        <xsl:value-of select="."/>
	</xsl:element>
</xsl:template>

<!-- Поле типа rtf текст -->
<xsl:template name="HTML_EDITOR">
	<xsl:element name="textarea">
        <xsl:attribute name="class">richEditor</xsl:attribute>
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
		<xsl:value-of select="."/>
	</xsl:element>
</xsl:template>

<!-- Для любого поля на которое нет прав на просмотр -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@mode=0]">
</xsl:template>


<!-- Для любого поля на которое права только чтение -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@mode='1']">
    <xsl:if test=".!=''">
        <div class="field">
            <xsl:if test="@title">
                <xsl:element name="label">
                    <xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
                    <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes" />
                </xsl:element><xsl:text> </xsl:text>
            </xsl:if>
            <span class="read"><xsl:value-of select="." disable-output-escaping="yes" /></span>
            <xsl:element name="input">
                <xsl:attribute name="type">hidden</xsl:attribute>
                <xsl:attribute name="name"><xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                </xsl:choose></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
            </xsl:element>
        </div>
    </xsl:if>
</xsl:template>

<!-- Read-only поле логического типа -->
<xsl:template match="field[@mode=1][@type='boolean'][ancestor::component[@exttype='grid'][@type='form']]">
	<div class="field">
		<xsl:if test="@title">
			<xsl:element name="label">
				<xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
				<xsl:value-of select="concat(@title, ':')" />
			</xsl:element><xsl:text> </xsl:text>
		</xsl:if>
	<xsl:variable name="FIELD_NAME"><xsl:choose>
			<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
			<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
	</xsl:choose></xsl:variable>
	<xsl:element name="input">
		<xsl:attribute name="type">hidden</xsl:attribute>
		<xsl:attribute name="name"><xsl:value-of select="$FIELD_NAME"/></xsl:attribute>
		<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
	</xsl:element>
	<xsl:element name="input">
	    <xsl:attribute name="class">checkbox</xsl:attribute>
		<xsl:attribute name="type">checkbox</xsl:attribute>
		<xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
		<xsl:attribute name="name"><xsl:value-of select="$FIELD_NAME"/></xsl:attribute>
		<xsl:attribute name="disabled">disabled</xsl:attribute>
		<xsl:if test=".=1">
			<xsl:attribute name="checked">checked</xsl:attribute>
		</xsl:if>
	</xsl:element>
	</div>
</xsl:template>


<!-- Для поля HTMLBLOCK на которое права только чтение -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@mode='1'][@type='htmlblock']">
    <xsl:if test=".!=''">
        <div class="field">
            <xsl:if test="@title">
                <xsl:element name="label">
                    <xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
                    <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes" />
                </xsl:element><xsl:text> </xsl:text>
            </xsl:if>
            <div class="readonlyBlock"><xsl:value-of select="." disable-output-escaping="yes" /></div>
            <xsl:element name="input">
                <xsl:attribute name="type">hidden</xsl:attribute>
                <xsl:attribute name="name"><xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                </xsl:choose></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
            </xsl:element>
        </div>
    </xsl:if>
</xsl:template>

<!-- Для поля TEXT на которое права только на чтение -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@mode='1'][@type='text']">
    <xsl:if test=".!=''">
        <div class="field">
            <xsl:if test="@title">
                <xsl:element name="label">
                    <xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
                    <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes" />
                </xsl:element><xsl:text> </xsl:text>
            </xsl:if>
            <div class="read"><xsl:value-of select="." disable-output-escaping="yes" /></div>
            <xsl:element name="input">
                <xsl:attribute name="type">hidden</xsl:attribute>
                <xsl:attribute name="name"><xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                </xsl:choose></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
            </xsl:element>
        </div>
    </xsl:if>
</xsl:template>

<!-- Для поля EMAIL на которое права только чтение -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@mode='1'][@type='email']">
    <xsl:if test=".!=''">
        <div class="field">
            <xsl:if test="@title">
                <xsl:element name="label">
                    <xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
                    <xsl:value-of select="concat(@title, ':')" />
                </xsl:element><xsl:text> </xsl:text>
            </xsl:if>
            <a href="mailto:{.}" class="email"><xsl:value-of select="."/></a>
            <xsl:element name="input">
                <xsl:attribute name="type">hidden</xsl:attribute>
                <xsl:attribute name="name"><xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                </xsl:choose></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
            </xsl:element>
        </div>
    </xsl:if>
</xsl:template>

<!-- Для поля FILE на которое права только чтение -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@mode='1'][@type='file']">
	<div class="field">
		<xsl:if test="@title">
			<xsl:element name="label">
				<xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
				<xsl:value-of select="concat(@title, ':')" />
			</xsl:element><xsl:text> </xsl:text>
		</xsl:if>
		<a href="{.}" target="_blank"><xsl:value-of select="."/></a>
		<xsl:element name="input">
			<xsl:attribute name="type">hidden</xsl:attribute>
			<xsl:attribute name="name"><xsl:choose>
				<xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
				<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
			</xsl:choose></xsl:attribute>
			<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
		</xsl:element>
	</div>
</xsl:template>

<!-- Read - only поле типа select -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@mode='1'][@type='select']">
	<div class="field">
		<xsl:if test="@title">
			<xsl:element name="label">
				<xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
				<xsl:value-of select="concat(@title, ':')" />
			</xsl:element><xsl:text> </xsl:text>
		</xsl:if>
		<span class="read"><xsl:value-of select="options/option[@selected='selected']"/></span>
		<xsl:element name="input">
			<xsl:attribute name="type">hidden</xsl:attribute>
			<xsl:attribute name="name"><xsl:choose>
				<xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
				<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
			</xsl:choose></xsl:attribute>
			<xsl:attribute name="value"><xsl:value-of select="options/option[@selected='selected']/@id"/></xsl:attribute>
		</xsl:element>
	</div>
</xsl:template>

<!-- Read - only поле типа multiselect -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@mode='1'][@type='multi']">
	<div class="field">
		<xsl:if test="@title">
			<xsl:element name="label">
				<xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
				<xsl:value-of select="concat(@title, ':')" />
			</xsl:element><xsl:text> </xsl:text>
		</xsl:if>
		<div class="read">
            <xsl:for-each select="options/option[@selected='selected']">
            	<xsl:value-of select="."/><br/>
                <xsl:element name="input">
                    <xsl:attribute name="type">hidden</xsl:attribute>
                    <xsl:attribute name="name"><xsl:choose>
                        <xsl:when test="../../@tableName"><xsl:value-of select="../../@tableName" />[<xsl:value-of select="../../@name" />]</xsl:when>
                        <xsl:otherwise><xsl:value-of select="../../@name" /></xsl:otherwise>
                    </xsl:choose>[]</xsl:attribute>
                    <xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
                </xsl:element>
            </xsl:for-each>
        </div>
	</div>
</xsl:template>


<!-- Read - only поле типа image -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@mode='1'][@type='image']">
    <xsl:if test="@title">
		<xsl:element name="label">
			<xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
			<xsl:value-of select="concat(@title, ':')" />
		</xsl:element><xsl:text> </xsl:text>
	</xsl:if>
    <xsl:if test=".!=''">
        <div class="image">
            <xsl:element name="img">
                <xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
            </xsl:element>
            <xsl:element name="input">
    			<xsl:attribute name="type">hidden</xsl:attribute>
    			<xsl:attribute name="name"><xsl:choose>
    				<xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
    				<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
    			</xsl:choose></xsl:attribute>
    			<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
    		</xsl:element>
        </div>
    </xsl:if>
</xsl:template>

<xsl:template match="field[ancestor::component[@type='form']][@mode='1'][@type='date'] | field[ancestor::component[@type='form']][@mode='1'][@type='datetime']">
    <xsl:if test=".!=''">
        <div class="field">
            <xsl:if test="@title">
                <xsl:element name="label">
                    <xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
                    <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes" />
                </xsl:element><xsl:text> </xsl:text>
            </xsl:if>
            <div class="read"><xsl:value-of select="." disable-output-escaping="yes" /></div>
            <xsl:element name="input">
                <xsl:attribute name="type">hidden</xsl:attribute>
                <xsl:attribute name="name"><xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                </xsl:choose></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
            </xsl:element>
        </div>
    </xsl:if>
</xsl:template>


<!-- Для PK  -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@key='1']">
		<xsl:element name="input">
			<xsl:attribute name="type">hidden</xsl:attribute>
			<xsl:attribute name="name"><xsl:choose>
				<xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
				<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
			</xsl:choose></xsl:attribute>
			<xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
			<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
			<xsl:attribute name="primary">primary</xsl:attribute>
		</xsl:element>

</xsl:template>

<xsl:template name="DATE_TIME">
	<xsl:element name="input">
		<xsl:attribute name="type">text</xsl:attribute>
		<xsl:attribute name="name"><xsl:choose>
					<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
					<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
				</xsl:choose></xsl:attribute>
		<xsl:if test="@length">
			<xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
		</xsl:if>
        <xsl:attribute name="readonly">readonly</xsl:attribute>
		<xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
        <xsl:attribute name="style">width:200px;</xsl:attribute>
		<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <!-- <xsl:attribute name="readonly">readonly</xsl:attribute> -->
        <xsl:if test="@pattern">
        	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
	</xsl:element>

    <xsl:element name="button">
        <xsl:attribute name="type">button</xsl:attribute>
        <xsl:attribute name="style">height:22px;margin-left:2px;</xsl:attribute>
        <xsl:attribute name="onclick">calendarKick(<xsl:value-of select="@name" />,document.body.scrollLeft+event.clientX,document.body.scrollTop+event.clientY,true,null,null,true);</xsl:attribute>...</xsl:element>
</xsl:template>

<xsl:template name="DATE">
	<xsl:element name="input">
		<xsl:attribute name="type">text</xsl:attribute>
		<xsl:if test="@length">
			<xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
		</xsl:if>
		<xsl:attribute name="id">date_<xsl:value-of select="@name" /></xsl:attribute>
        <xsl:attribute name="readonly">readonly</xsl:attribute>
	</xsl:element>

	<input type="hidden">
		<xsl:attribute name="name"><xsl:choose>
			<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
			<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
		</xsl:choose></xsl:attribute>
		<xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
		<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
		<xsl:if test="@pattern">
        	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
	</input>
	<xsl:if test=".!=''">
		<script type="text/javascript">
			window.addEvent('domready', function(){
				<xsl:value-of select="generate-id(../..)"/>.setDate('<xsl:value-of select="@name"/>');
			});
		</script>
	</xsl:if>


    <!--<button style="height: 22px; margin-left: 2px;" type="button" onclick="{generate-id(../..)}.showCalendar('{@name}', event); ">...</button>-->
    <img src="images/calendar.gif" id="calendarImg" onclick="{generate-id(../..)}.showCalendar('{@name}', event); "/>
    <!--<script type="text/javascript">
			window.addEvent('domready', function(){
				new DatePicker('date_<xsl:value-of select="@name" />', {
 			    	additionalShowLinks: ['calendarImg'],
    				showOnInputFocus: false
				});
			});
	</script>-->
</xsl:template>

<!-- Для поля hidden -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@type='hidden']">
<xsl:call-template name="HIDDEN"/>
</xsl:template>

<xsl:template name="HIDDEN">
	<xsl:element name="input">
		<xsl:attribute name="type">hidden</xsl:attribute>
		<xsl:attribute name="name"><xsl:choose>
					<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
					<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
				</xsl:choose></xsl:attribute>
        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
		<xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
	</xsl:element>
</xsl:template>
</xsl:stylesheet>