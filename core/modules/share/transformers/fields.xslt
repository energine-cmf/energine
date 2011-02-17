<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <!--
        Шаблон - контроллер для любого поля из компонента типа форма.
        Создает стандартную обвязку вокруг элемента формы:
            <div class="field">
                <div class="name"><label>Имя поля</label></div>
                <div class="control">** импорт шаблона, который создает HTML-элемент формы из share/base.xslt **</div>     
            </div>
    -->
    <xsl:template match="field[ancestor::component[@type='form']]">
    	<div class="field">
    	    <xsl:if test="not(@nullable) and @type != 'boolean'">
    		    <xsl:attribute name="class">field required</xsl:attribute>
    		</xsl:if>
    		<xsl:if test="@title and @type != 'boolean'">
    		    <div class="name">
        			<label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes" /></label>
    				<xsl:if test="not(@nullable) and not(ancestor::component/@exttype = 'grid') and not(ancestor::component[@class='TextBlockSource'])"><span class="mark">*</span></xsl:if>                    
    			</div>
    		</xsl:if>   
    		<div class="control" id="control_{@language}_{@name}">                
                <!-- импорт шаблона, который создает сам HTML-элемент (input, select, etc.) -->
        		<xsl:apply-imports />
            </div>
    	</div>
    </xsl:template>
    
    <!-- Отображение поля типа image для не гридовых элементов формы -->
<!--    <xsl:template match="field[@type='image'][ancestor::component[@type='form' or @type='list'][not(@exttype='grid')]]">
        <xsl:variable name="THUMB" select="image[@name='default']"/>
        <xsl:variable name="MAIN" select="image[@name='main']"/>                        
        <a href="{$MAIN}" target="_blank" class="thumbnail">
            <img src="{$THUMB}" width="{$THUMB/@width}" height="{$THUMB/@height}">
                <xsl:attribute name="nrgn:image_width"  xmlns:nrgn="http://energine.org"><xsl:value-of select="$MAIN/@width"/></xsl:attribute>
                <xsl:attribute name="nrgn:image_height"  xmlns:nrgn="http://energine.org"><xsl:value-of select="$MAIN/@height"/></xsl:attribute>
                <xsl:attribute name="nrgn:image_src"  xmlns:nrgn="http://energine.org"><xsl:value-of select="$MAIN"/></xsl:attribute>
            </img>
        </a>
    

    </xsl:template>    -->
    
    <!-- 
        Шаблон для необязательного (nullable) поля в админчасти вынесен отдельно. 
        В нем добавляется возможность скрыть/раскрыть необязательное поле. 
    -->
    <xsl:template match="field[@nullable and (@type='htmlblock' or @type='text')][ancestor::component[@type='form'][@exttype = 'grid']]">
        <div class="field">
            <xsl:if test="@title">
                <div class="name">
                    <label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes" /></label>                    
                    (<a href="#" message1="{@msgOpenField}" message0="{@msgCloseField}">
                        <xsl:attribute name="onclick">return showhideField(this, '<xsl:value-of select="@name"/>'<xsl:if test="@language">, <xsl:value-of select="@language"/></xsl:if>);</xsl:attribute>
                        <xsl:attribute name="is_hidden">
                                    <xsl:choose>
                                        <xsl:when test=".=''">1</xsl:when>
                                        <xsl:otherwise>0</xsl:otherwise>
                                    </xsl:choose>
                        </xsl:attribute>
                                <xsl:choose>
                                    <xsl:when test=".=''">
                                        <xsl:value-of select="@msgOpenField"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="@msgCloseField"/>
                                    </xsl:otherwise>
                                </xsl:choose>
                    </a>)
                </div>
            </xsl:if>    
            <div class="control" id="control_{@language}_{@name}">
                <xsl:choose>
                    <xsl:when test="@type='select'">
                        <xsl:if test="not(options/option/@selected)">
                            <xsl:attribute name="style">display: none;</xsl:attribute>
                        </xsl:if>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:if test=".=''">
                            <xsl:attribute name="style">display: none;</xsl:attribute>
                        </xsl:if>
                    </xsl:otherwise>
                </xsl:choose>                
                <!-- импорт шаблона, который создает сам HTML-элемент (input, select, etc.) -->
                <xsl:apply-imports />
            </div>
        </div>
    </xsl:template>
    
    <!-- для любого поля, на которое нет прав на просмотр -->
    <xsl:template match="field[@mode=0][ancestor::component[@type='form']]"/>
    
    <!-- для любого поля, на которое права только чтение -->
    <xsl:template match="field[@mode='1'][ancestor::component[@type='form']]">
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
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name" />]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </input>
            </div>
        </xsl:if>
    </xsl:template>
    
    <!-- read-only поле логического типа -->
    <xsl:template match="field[@type='boolean'][@mode=1][ancestor::component[@exttype='grid'][@type='form']]">
        <div class="field">
            <xsl:if test="@title">
                <label for="{@name}">
                    <xsl:value-of select="concat(@title, ':')"/>
                </label><xsl:text> </xsl:text>
            </xsl:if>
            <xsl:variable name="FIELD_NAME">
                <xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName"/><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name"/>]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            <input type="hidden" name="{$FIELD_NAME}" value="{.}"/>
            <input type="checkbox" id="{@name}" name="{$FIELD_NAME}" disabled="disabled" class="checkbox">
                <xsl:if test=".=1">
                    <xsl:attribute name="checked">checked</xsl:attribute>
                </xsl:if>
            </input>
        </div>
    </xsl:template>
    
    <!-- для поля HTMLBLOCK на которое права только чтение -->
    <xsl:template match="field[@type='htmlblock'][@mode='1'][ancestor::component[@type='form']]">
        <xsl:if test=".!=''">
            <div class="field">
                <xsl:if test="@title">
                    <label for="{@name}">
                        <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes"/>
                    </label><xsl:text> </xsl:text>
                </xsl:if>
                <div class="readonlyBlock"><xsl:value-of select="." disable-output-escaping="yes"/></div>
                <input type="hidden" value="{.}">
                    <xsl:attribute name="name">
                        <xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </input>
            </div>
        </xsl:if>
    </xsl:template>
    
    <!-- для поля TEXT на которое права только на чтение -->
    <xsl:template match="field[@type='text'][@mode='1'][ancestor::component[@type='form']]">
        <xsl:if test=".!=''">
            <div class="field">
                <xsl:if test="@title">
                    <label for="{@name}">                    
                        <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes"/>
                    </label><xsl:text> </xsl:text>
                </xsl:if>
                <div class="read"><xsl:value-of select="." disable-output-escaping="yes"/></div>
                <input type="hidden" value="{.}">
                    <xsl:attribute name="name">
                        <xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </input>
            </div>
        </xsl:if>
    </xsl:template>
    
    <!-- для поля EMAIL на которое права только чтение -->
    <xsl:template match="field[@type='email'][@mode='1'][ancestor::component[@type='form']]">
        <xsl:if test=".!=''">
            <div class="field">
                <xsl:if test="@title">
                    <label for="{@name}">
                        <xsl:value-of select="concat(@title, ':')"/>
                    </label><xsl:text> </xsl:text>
                </xsl:if>
                <a href="mailto:{.}" class="email"><xsl:value-of select="."/></a>
                <input type="hidden" value="{.}">
                    <xsl:attribute name="name">
                        <xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </input>
            </div>
        </xsl:if>
    </xsl:template>
    
    <!-- для поля FILE на которое права только чтение -->
    <xsl:template match="
        field[@type='file'][@mode='1'][ancestor::component[@type='form']] 
        | 
        field[@type='pfile'][@mode='1'][ancestor::component[@type='form']]
        |
        field[@type='prfile'][@mode='1'][ancestor::component[@type='form']]">
        <div class="field">
            <xsl:if test="@title">
                <label for="{@name}">
                    <xsl:value-of select="concat(@title, ':')"/>
                </label><xsl:text> </xsl:text>
            </xsl:if>
            <a href="{.}" target="_blank"><xsl:value-of select="."/></a>
            <input type="hidden" value="{.}">
                <xsl:attribute name="name">
                    <xsl:choose>
                        <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                        <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
            </input>
        </div>
    </xsl:template>
    
    <!-- read-only поле типа select -->
    <xsl:template match="field[@type='select'][@mode='1'][ancestor::component[@type='form']]">
        <div class="field">
            <xsl:if test="@title">
                <label for="{@name}">
                    <xsl:value-of select="concat(@title, ':')"/>
                </label><xsl:text> </xsl:text>
            </xsl:if>
            <span class="read"><xsl:value-of select="options/option[@selected='selected']"/></span>
            <input type="hidden" value="{options/option[@selected='selected']/@id}">
                <xsl:attribute name="name">
                    <xsl:choose>
                        <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                        <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
            </input>
        </div>
    </xsl:template>
    
    <!-- read-only поле типа multiselect -->
    <xsl:template match="field[@type='multi'][@mode='1'][ancestor::component[@type='form']]">
        <div class="field">
            <xsl:if test="@title">
                <label for="{@name}">
                    <xsl:value-of select="concat(@title, ':')"/>
                </label><xsl:text> </xsl:text>
            </xsl:if>
            <div class="read">
                <xsl:for-each select="options/option[@selected='selected']">
                    <xsl:value-of select="."/><br/>
                        <input type="hidden" value="{@id}">
                            <xsl:attribute name="name">
                                <xsl:choose>
                                    <xsl:when test="../../@tableName"><xsl:value-of select="../../@tableName"/>[<xsl:value-of select="../../@name"/>]</xsl:when>
                                    <xsl:otherwise><xsl:value-of select="../../@name"/></xsl:otherwise>
                                </xsl:choose>[]
                            </xsl:attribute>
                        </input>
                </xsl:for-each>
            </div>
        </div>
    </xsl:template>
    
    <!-- read-only поле типа image -->
    <xsl:template match="field[@type='image'][@mode='1'][ancestor::component[@type='form']]">
        <xsl:if test="@title">
            <label for="{@name}">
                <xsl:value-of select="concat(@title, ':')"/>
            </label><xsl:text> </xsl:text>
        </xsl:if>
        <xsl:if test=".!=''">
            <div class="image">
                <img src="{.}" />
                <input type="hidden" value="{.}">
                    <xsl:attribute name="name">
                        <xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </input>
            </div>
        </xsl:if>
    </xsl:template>
    
    <!-- read-only поле типа date и datetime -->
    <xsl:template match="
        field[@type='date'][@mode='1'][ancestor::component[@type='form']] 
        |
        field[@type='datetime'][@mode='1'][ancestor::component[@type='form']]">
        <xsl:if test=".!=''">
            <div class="field">
                <xsl:if test="@title">
                    <label for="{@name}">
                        <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes"/>
                    </label><xsl:text> </xsl:text>
                </xsl:if>
                <div class="read"><xsl:value-of select="." disable-output-escaping="yes"/></div>
                <input type="hidden" value="{.}">
                    <xsl:attribute name="name">
                        <xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </input>
            </div>
        </xsl:if>
    </xsl:template>
    
    <!-- для PK  -->
    <xsl:template match="field[@key='1'][ancestor::component[@type='form']]">
        <input type="hidden" id="{@name}" value="{.}" primary="primary">
            <xsl:attribute name="name">
                <xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name" />]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
        </input>
    </xsl:template>
    
    <!-- для поля hidden -->
    <xsl:template match="field[@type='hidden'][ancestor::component[@type='form']]">
        <xsl:apply-imports/>
    </xsl:template>

    <xsl:template match="field[@type='captcha'][ancestor::component[@type='list']]"></xsl:template>

    <xsl:template match="field[@type='captcha'][ancestor::component[@type='form']]">
        <script type="text/javascript">
            var RecaptchaOptions = {
               lang : '<xsl:value-of select="$DOC_PROPS[@name='lang']/@real_abbr"/>'
            };
        </script>
        <xsl:value-of select="." disable-output-escaping="yes"/>
    </xsl:template>

</xsl:stylesheet>
