<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml"
    version="1.0">
    
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
    
    <!-- 
        Шаблон для необязательного (nullable) поля в админчасти вынесен отдельно. 
        В нем добавляется возможность скрыть/раскрыть необязательное поле. 
    -->
    <xsl:template match="field[ancestor::component[@type='form'][@exttype = 'grid']][@nullable]">
        <div class="field">
            <xsl:if test="@title and @type != 'boolean'">
                <div class="name">
                    <label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes" /></label>                    
                    (<a href="#" message1="{$TRANSLATION[@const='TXT_OPEN_FIELD']}" message0="{$TRANSLATION[@const='TXT_CLOSE_FIELD']}">
                        <xsl:attribute name="onclick">return showhideField(this, '<xsl:value-of select="@name"/>'<xsl:if test="@language">, <xsl:value-of select="@language"/></xsl:if>);</xsl:attribute>
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
                                        <xsl:value-of select="$TRANSLATION[@const='TXT_OPEN_FIELD']"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="$TRANSLATION[@const='TXT_CLOSE_FIELD']"/>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:choose>
                                    <xsl:when test=".=''">
                                        <xsl:value-of select="$TRANSLATION[@const='TXT_OPEN_FIELD']"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="$TRANSLATION[@const='TXT_CLOSE_FIELD']"/>
                                    </xsl:otherwise>
                                </xsl:choose>
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
    <xsl:template match="field[ancestor::component[@type='form']][@mode=0]" />
    
    <!-- для любого поля, на которое права только чтение -->
    <xsl:template match="field[ancestor::component[@type='form']][@mode='1']">
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
    
    <!-- read-only поле типа date и datetime -->
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
    
    <!-- для поля hidden -->
    <xsl:template match="field[ancestor::component[@type='form']][@type='hidden']">
        <xsl:apply-imports />
    </xsl:template>

</xsl:stylesheet>
