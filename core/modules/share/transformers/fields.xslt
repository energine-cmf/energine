<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
     xmlns="http://www.w3.org/1999/xhtml"
     version="1.0">

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
                <xsl:when test="@type='video'">
                    <xsl:call-template name="VIDEOFILE"/>
                </xsl:when>                
    			<xsl:otherwise>
    				<xsl:call-template name="STRING"/>
    			</xsl:otherwise>
    		</xsl:choose>
        </div>
	</div>
</xsl:template>

<!-- строковое поле -->
<xsl:template name="STRING">
    <input type="text" id="{@name}" value="{.}" class="text inp_string">
    	<xsl:attribute name="name">
            <xsl:choose>
            	<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@length">
        	<xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@pattern">
        	<xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
</xsl:template>

<!-- поле для ввода Email -->
<xsl:template name="EMAIL">
    <input type="text" id="{@name}" value="{.}" class="text inp_email">
		<xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
    			<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@pattern">
        	<xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
	</input>
</xsl:template>

<!-- поле для ввода телефона-->
<xsl:template name="PHONE">
    <input type="text" id="{@name}" value="{.}" class="text inp_phone">
        <xsl:attribute name="name">
            <xsl:choose>
				<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
				<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
		<xsl:if test="@length">
			<xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
		</xsl:if>
        <xsl:if test="@pattern">
        	<xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
        	<xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
	</input>
</xsl:template>

<!-- числовое поле (integer) -->
<xsl:template name="INTEGER">
    <input type="text" id="{@name}" value="{.}" length="5" class="text inp_integer">
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@length">
            <xsl:attribute name="maxlength">5</xsl:attribute>
        </xsl:if>
        <xsl:if test="@pattern">
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
</xsl:template>

<!-- числовое поле (float) -->
<xsl:template name="FLOAT">
    <input type="text" id="{@name}" value="{.}" class="text inp_float">
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@length">
            <xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@pattern">
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
</xsl:template>

<!-- поле пароля -->
<xsl:template name="PASSWORD">
    <input type="password" id="{@name}" value="{.}" class="text inp_password">
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@length">
            <xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@pattern">
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
</xsl:template>

<!-- поле логического типа -->
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

<xsl:template name="VIDEOFILE">
    <!-- Вынесено в отдельную функцию для того чтоб была возможность вызвать не в форме -->
    <script type="text/javascript">
        if(!insertVideo){
            var insertVideo = function(videoFile, id){
                new Swiff('images/player.swf',
                    {
                        'container':id + '_preview',
                        'id': id + '_preview_video',
                        'width': 450,
                        'height': 370,
                        'vars': {
                           'beginplay': false,
                           'vidurl': videoFile 
                        } 
                    }
                );
            }
        }
    </script>
    <div class="video" id="{generate-id(.)}_preview">
        
    </div>

    <xsl:if test=".!=''">
        <script type="text/javascript">
            window.addEvent('domready', insertVideo.pass(['<xsl:value-of select="$BASE"/><xsl:value-of select="."/>', '<xsl:value-of select="generate-id(.)"/>']));
        </script>
        <a href="#">
            <xsl:attribute name="onclick">return <xsl:value-of select="generate-id(ancestor::recordset)"/>.removeFilePreview.run(['<xsl:value-of select="generate-id(.)"/>', this], <xsl:value-of select="generate-id(ancestor::recordset)"/>);</xsl:attribute>
            <xsl:value-of select="@deleteFileTitle"/></a>
    </xsl:if>
    <xsl:variable name="FIELD_ID">tmp_<xsl:value-of select="generate-id()"/></xsl:variable>
    <input type="file">
        <xsl:attribute name="id"><xsl:value-of select="$FIELD_ID"/></xsl:attribute>
        <xsl:attribute name="name">file</xsl:attribute>
        <xsl:attribute name="field"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
        <xsl:attribute name="preview"><xsl:value-of select="generate-id(.)"/>_preview</xsl:attribute>
        <xsl:attribute name="onchange"><xsl:value-of select="generate-id(ancestor::recordset)"/>.uploadVideo.bind(<xsl:value-of select="generate-id(ancestor::recordset)"/>)(this);</xsl:attribute>
    </input>
    <input type="hidden">
        <xsl:attribute name="name"><xsl:choose>
            <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
            <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
        </xsl:choose></xsl:attribute>
        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
        <xsl:if test="@pattern">
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
            </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
    
</xsl:template>

<!-- поле для загрузки изображения из репозитория (используется в админчасти) -->
<xsl:template name="IMAGE">
    <div class="image">
        <img id="{generate-id(.)}_preview">
            <xsl:if test=".!=''">
                <xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
            </xsl:if>
        </img>
    </div>
    <xsl:if test=".!=''">
        <a href="#">
            <xsl:attribute name="onclick"><xsl:value-of select="generate-id(ancestor::recordset)"/>.removeFilePreview.run(['<xsl:value-of select="generate-id(.)"/>', '<xsl:value-of select="generate-id(.)"/>_preview', this], <xsl:value-of select="generate-id(ancestor::recordset)"/>); $(this).destroy();return false;</xsl:attribute>
            <xsl:value-of select="@deleteFileTitle"/></a>
    </xsl:if>
    <input type="text" id="{generate-id(.)}" value="{.}" readonly="readonly" class="text inp_file">
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@pattern">
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
    <button onclick="{generate-id(../..)}.openFileLib(this);" type="button" link="{generate-id(.)}" preview="{generate-id(.)}_preview">...</button>
</xsl:template>

<xsl:template name="FILE">
    <div id="{generate-id(.)}_preview" class="file"></div>
    <xsl:if test=".!=''">
        <a href="{.}" target="_blank"><xsl:value-of select="."/></a>
        <a href="#">
            <xsl:attribute name="onclick">return <xsl:value-of select="generate-id(ancestor::recordset)"/>.removeFilePreview.run(['<xsl:value-of select="generate-id(.)"/>', this], <xsl:value-of select="generate-id(ancestor::recordset)"/>);</xsl:attribute>
            <xsl:value-of select="@deleteFileTitle"/></a>
    </xsl:if>
    <div>

    </div>
    <input type="text" id="{generate-id(.)}" value="{.}" readonly="readonly" class="text inp_file">
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@pattern">
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
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
    <xsl:if test=".!=''">
        <a href="#">
            <xsl:attribute name="onclick">return <xsl:value-of select="generate-id(ancestor::recordset)"/>.removeFilePreview.run(['<xsl:value-of select="generate-id(.)"/>', '<xsl:value-of select="generate-id(.)"/>_preview', this], <xsl:value-of select="generate-id(ancestor::recordset)"/>);</xsl:attribute>
            <xsl:value-of select="@deleteFileTitle"/></a>
    </xsl:if>
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
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
            </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
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
    <xsl:if test=".!=''">
        <a href="#">
            <xsl:attribute name="onclick">return <xsl:value-of select="generate-id(ancestor::recordset)"/>.removeFilePreview.run(['<xsl:value-of select="generate-id(.)"/>', this], <xsl:value-of select="generate-id(ancestor::recordset)"/>);</xsl:attribute>
            <xsl:value-of select="@deleteFileTitle"/></a>
    </xsl:if>

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
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
            </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
</xsl:template>

<!-- поле выбора из списка -->
<xsl:template name="SELECT">
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
        <xsl:for-each select="options/option">
            <option value="{@id}">
                <xsl:if test="@selected">
                    <xsl:attribute name="selected">selected</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="."/>
            </option>
        </xsl:for-each>
    </select>
</xsl:template>

<!-- поле множественного выбора -->
<xsl:template name="MULTI">
    <xsl:variable name="NAME"><xsl:choose>
        <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
        <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
        </xsl:choose>[]</xsl:variable>
        <div style="margin-left:20px;padding-top:5px;">
            <xsl:for-each select="options/option">
                <div>
                    <input type="checkbox" id="{generate-id(.)}" name="{$NAME}" value="{@id}" style="width:auto;vertical-align:middle;border:none;">
                        <xsl:if test="@selected">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:if>
                    </input>
                    <label for="{generate-id(.)}"><xsl:value-of select="."/></label>
                </div>
            </xsl:for-each>
        </div>
</xsl:template>

<!-- поле типа текст -->
<xsl:template name="TEXT">
    <textarea id="{@name}">
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@pattern">
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
        <xsl:value-of select="."/>
    </textarea>
</xsl:template>

<!-- поле типа rtf текст -->
<xsl:template name="HTML_EDITOR">
    <textarea class="richEditor">
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
		<xsl:if test="@pattern">
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>		
		<xsl:value-of select="."/>
	</textarea>
</xsl:template>

<!-- для любого поля, на которое нет прав на просмотр -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@mode=0]" />

<!-- для любого поля, на которое права только чтение -->
<xsl:template match="field[parent::record[parent::recordset[parent::component[@type='form']]]][@mode='1']">
    <xsl:if test=".!=''">
        <div class="field">
            <xsl:if test="@title">
                <label for="{@name}">
                    <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes" />
                </label><xsl:text> </xsl:text>
            </xsl:if>
            <span class="read"><xsl:value-of select="." disable-output-escaping="yes" /></span>
            <input type="hidden" value="{.}">
                <xsl:attribute name="name">
                    <xsl:choose>
                        <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                        <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
            </input>
        </div>
    </xsl:if>
</xsl:template>

<!-- read-only поле логического типа -->
<xsl:template match="field[@mode=1][@type='boolean'][ancestor::component[@exttype='grid'][@type='form']]">
    <div class="field">
        <xsl:if test="@title">
            <label for="{@name}">
                <xsl:value-of select="concat(@title, ':')" />
            </label><xsl:text> </xsl:text>
        </xsl:if>
        <xsl:variable name="FIELD_NAME">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <input type="hidden" name="{$FIELD_NAME}" value="{.}" />
        <input type="checkbox" id="{@name}" name="{$FIELD_NAME}" disabled="disabled" class="checkbox">
            <xsl:if test=".=1">
                <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
        </input>
	</div>
</xsl:template>

<!-- для поля HTMLBLOCK на которое права только чтение -->
<xsl:template match="field[ancestor::component[@type='form']][@mode='1'][@type='htmlblock']">
    <xsl:if test=".!=''">
        <div class="field">
            <xsl:if test="@title">
                <label for="{@name}">
                    <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes" />
                </label><xsl:text> </xsl:text>
            </xsl:if>
            <div class="readonlyBlock"><xsl:value-of select="." disable-output-escaping="yes" /></div>
            <input type="hidden" value="{.}">
                <xsl:attribute name="name">
                    <xsl:choose>
                        <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                        <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
            </input>
        </div>
    </xsl:if>
</xsl:template>

<!-- для поля TEXT на которое права только на чтение -->
<xsl:template match="field[ancestor::component[@type='form']][@mode='1'][@type='text']">
    <xsl:if test=".!=''">
        <div class="field">
            <xsl:if test="@title">
                <label for="{@name}">                    
                    <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes" />
                </label><xsl:text> </xsl:text>
            </xsl:if>
            <div class="read"><xsl:value-of select="." disable-output-escaping="yes" /></div>
            <input type="hidden" value="{.}">
                <xsl:attribute name="name">
                    <xsl:choose>
                        <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                        <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
            </input>
        </div>
    </xsl:if>
</xsl:template>

<!-- для поля EMAIL на которое права только чтение -->
<xsl:template match="field[ancestor::component[@type='form']][@mode='1'][@type='email']">
    <xsl:if test=".!=''">
        <div class="field">
            <xsl:if test="@title">
                <label for="{@name}">
                    <xsl:value-of select="concat(@title, ':')" />
                </label><xsl:text> </xsl:text>
            </xsl:if>
            <a href="mailto:{.}" class="email"><xsl:value-of select="."/></a>
            <input type="hidden" value="{.}">
                <xsl:attribute name="name">
                    <xsl:choose>
                        <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                        <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
            </input>
        </div>
    </xsl:if>
</xsl:template>

<!-- для поля FILE на которое права только чтение -->
<xsl:template match="
    field[ancestor::component[@type='form']][@mode='1'][@type='file'] 
    | 
    field[ancestor::component[@type='form']][@mode='1'][@type='pfile']
    |
    field[ancestor::component[@type='form']][@mode='1'][@type='prfile']">
    <div class="field">
        <xsl:if test="@title">
            <label for="{@name}">
                <xsl:value-of select="concat(@title, ':')" />
            </label><xsl:text> </xsl:text>
        </xsl:if>
        <a href="{.}" target="_blank"><xsl:value-of select="."/></a>
        <input type="hidden" value="{.}">
            <xsl:attribute name="name">
                <xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
        </input>
    </div>
</xsl:template>

<!-- read-only поле типа select -->
<xsl:template match="field[ancestor::component[@type='form']][@mode='1'][@type='select']">
    <div class="field">
        <xsl:if test="@title">
            <label for="{@name}">
                <xsl:value-of select="concat(@title, ':')" />
            </label><xsl:text> </xsl:text>
        </xsl:if>
        <span class="read"><xsl:value-of select="options/option[@selected='selected']"/></span>
        <input type="hidden" value="{options/option[@selected='selected']/@id}">
            <xsl:attribute name="name">
                <xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
        </input>
    </div>
</xsl:template>

<!-- read-only поле типа multiselect -->
<xsl:template match="field[ancestor::component[@type='form']][@mode='1'][@type='multi']">
    <div class="field">
        <xsl:if test="@title">
            <label for="{@name}">
                <xsl:value-of select="concat(@title, ':')" />
            </label><xsl:text> </xsl:text>
        </xsl:if>
        <div class="read">
            <xsl:for-each select="options/option[@selected='selected']">
                <xsl:value-of select="."/><br/>
                    <input type="hidden" value="{@id}">
                        <xsl:attribute name="name">
                            <xsl:choose>
                                <xsl:when test="../../@tableName"><xsl:value-of select="../../@tableName" />[<xsl:value-of select="../../@name" />]</xsl:when>
                                <xsl:otherwise><xsl:value-of select="../../@name" /></xsl:otherwise>
                            </xsl:choose>[]
                        </xsl:attribute>
                    </input>
            </xsl:for-each>
        </div>
    </div>
</xsl:template>

<!-- read-only поле типа image -->
<xsl:template match="field[ancestor::component[@type='form']][@mode='1'][@type='image']">
    <xsl:if test="@title">
        <label for="{@name}">
			<xsl:value-of select="concat(@title, ':')" />
        </label><xsl:text> </xsl:text>
    </xsl:if>
    <xsl:if test=".!=''">
        <div class="image">
            <img src="{.}" />
            <input type="hidden" value="{.}">
    			<xsl:attribute name="name">
                    <xsl:choose>
    				    <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
    				    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
    		</input>
        </div>
    </xsl:if>
</xsl:template>

<xsl:template match="
    field[ancestor::component[@type='form']][@mode='1'][@type='date'] 
    |
    field[ancestor::component[@type='form']][@mode='1'][@type='datetime']">
    <xsl:if test=".!=''">
        <div class="field">
            <xsl:if test="@title">
                <label for="{@name}">
                    <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes" />
                </label><xsl:text> </xsl:text>
            </xsl:if>
            <div class="read"><xsl:value-of select="." disable-output-escaping="yes" /></div>
            <input type="hidden" value="{.}">
                <xsl:attribute name="name">
                    <xsl:choose>
                        <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                        <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
            </input>
        </div>
    </xsl:if>
</xsl:template>

<!-- для PK  -->
<xsl:template match="field[ancestor::component[@type='form']][@key='1']">
    <input type="hidden" id="{@name}" value="{.}" primary="primary">
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
    </input>
</xsl:template>

<xsl:template name="DATE_TIME">
    <input type="text" id="{@name}" value="{.}" readonly="readonly" class="text inp_date" style="width:200px;">
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@length">
            <xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@pattern">
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
    
    <button type="button" onclick="calendarKick({@name},document.body.scrollLeft+event.clientX,document.body.scrollTop+event.clientY,true,null,null,true);" style="height:22px;margin-left:2px;">...</button>
</xsl:template>

<xsl:template name="DATE">
    <input type="hidden" id="{@name}" value="{.}">
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@pattern">
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </input>
    <input type="text" id="date_{@name}" readonly="readonly" class="text inp_date">
        <xsl:if test="@length">
            <xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
        </xsl:if>
    </input>        
    <xsl:if test=".!=''">
        <script type="text/javascript">
            window.addEvent('domready', function(){
                <xsl:value-of select="generate-id(../..)"/>.setDate('<xsl:value-of select="@name"/>');
            });
        </script>
    </xsl:if>
    <span class="calendar_box">
        <img src="images/calendar.gif" class="set_date" onclick="{generate-id(../..)}.showCalendar('{@name}', event); "/>
    </span>
</xsl:template>

<!-- для поля hidden -->
<xsl:template match="field[ancestor::component[@type='form']][@type='hidden']">
    <xsl:call-template name="HIDDEN"/>
</xsl:template>

<xsl:template name="HIDDEN">
    <input type="hidden" id="{@name}" value="{.}">
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
    </input>
</xsl:template>

</xsl:stylesheet>