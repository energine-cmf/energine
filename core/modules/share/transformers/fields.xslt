<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >
    <!-- Секция 1. Обвязка для полей формы. -->
    <!--
        Шаблон-контроллер для обработки любого поля из компонента типа форма.
        Создает стандартную обвязку вокруг элемента формы:
        <div class="field">
            <div class="name"><label>Имя поля</label></div>
            <div class="control"><input/></div>
        </div>
    -->
    <xsl:template match="field[ancestor::component[@type='form']]">
        <div>
            <xsl:attribute name="class">form-group field<xsl:if test="not(@nullable) and @type!='boolean'"> required</xsl:if></xsl:attribute>
            <xsl:apply-templates select="." mode="field_name"/>
            <xsl:apply-templates select="." mode="field_content"/>
        </div>
    </xsl:template>

    <xsl:template match="field[ancestor::component[@type='form']]" mode="field_name">
        <xsl:if test="@title and @type!='boolean'">
            <div class="name">
                <label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes"/></label>
                <xsl:if test="not(@nullable) and not(ancestor::component/@exttype='grid') and not(ancestor::component[@class='TextBlockSource'])"><span class="mark">*</span></xsl:if>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template match="field[ancestor::component[@type='form']]" mode="field_content">
        <div class="control" id="control_{@language}_{@name}">
            <xsl:apply-templates select="." mode="field_input"/>
        </div>
    </xsl:template>

    <!--
        Шаблон для необязательного (nullable) поля в админчасти вынесен отдельно.
        В нем добавляется возможность скрыть/раскрыть необязательное поле.
    -->
    <xsl:template match="field[@type='text'][ancestor::component[@type='form' and @exttype='grid']]">
        <div>
            <xsl:attribute name="class">field clearfix<xsl:choose>
                <xsl:when test=".=''"> min</xsl:when>
                <xsl:otherwise> max</xsl:otherwise>
            </xsl:choose></xsl:attribute>
            <xsl:apply-templates select="." mode="field_name"/>
            <xsl:apply-templates select="." mode="field_content"/>
        </div>
    </xsl:template>

    <xsl:template match="field[@type='code'][ancestor::component[@type='form' and @exttype='grid']]">
        <div>
            <xsl:attribute name="class">field editor clearfix</xsl:attribute>
            <xsl:apply-templates select="." mode="field_name"/>
            <xsl:apply-templates select="." mode="field_content"/>
        </div>
    </xsl:template>

    <xsl:template match="field[@type='htmlblock'][ancestor::component[@type='form' and @exttype='grid']]">
        <div>
            <xsl:attribute name="class">field editor clearfix</xsl:attribute>
            <xsl:apply-templates select="." mode="field_name"/>
            <xsl:apply-templates select="." mode="field_content"/>
        </div>
    </xsl:template>

    <xsl:template match="field[@type='file'][ancestor::component[@type='form']]">
        <div>
            <xsl:attribute name="class">field file_upload clearfix</xsl:attribute>
            <xsl:apply-templates select="." mode="field_name"/>
            <xsl:apply-templates select="." mode="field_content"/>
        </div>
    </xsl:template>

    <xsl:template match="field[@type='text' or @type='code'][ancestor::component[@type='form' and @exttype='grid']]" mode="field_name">
        <xsl:if test="@title">
            <div class="name">
                <label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes"/></label>
                <a href="#" class="icon_min_max"></a>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template match="field[@type='htmlblock' or @type='text' or @type='code'][ancestor::component[@type='form' and @exttype='grid']]" mode="field_content">
        <div class="control toggle type_{@type}" id="control_{@language}_{@name}">
            <xsl:apply-templates select="." mode="field_input"/>
        </div>
    </xsl:template>


    <xsl:template match="field[@type='file'][ancestor::component[@type='form']]" mode="field_content">
        <div class="control type_{@type}" id="control_{@language}_{@name}">
            <xsl:apply-templates select="." mode="field_input"/>
        </div>
    </xsl:template>

    <xsl:template match="field[@type='lookup'][ancestor::component[@exttype='grid']]" mode="field_content">
        <div class="control type_{@type}" id="control_{@language}_{@name}" data-url="{@url}" data-value-field="{@value_field}" data-value-table="{@value_table}" data-key-field="{@key_field}">
            <xsl:apply-templates select="." mode="field_input"/>
        </div>
    </xsl:template>
    <!--
        Секция 2. Инпуты.
        В этой секции собраны правила вывода полей формы, которые создают сам html-элемент (input, select, etc.).
    -->
    <!-- строковое поле (string), или поле, к которому не нашлось шаблона -->
    <xsl:template match="field[ancestor::component[@type='form']]" mode="field_input">    
        <input class="text inp_string form-control">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
        </input>
    </xsl:template>

    <!-- поле для почтового адреса (email) -->
    <xsl:template match="field[@type='email'][ancestor::component[@type='form']]" mode="field_input">
        <input class="text inp_email form-control">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">email</xsl:attribute>
        </input>
    </xsl:template>

    <!-- поле для цвета -->
    <xsl:template match="field[@type='color'][ancestor::component[@type='form']]" mode="field_input">
        <input class="text inp_color form-control">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">hidden</xsl:attribute>
        </input>
    </xsl:template>

    <!-- поле для телефона (phone)-->
    <xsl:template match="field[@type='phone'][ancestor::component[@type='form']]" mode="field_input">
        <input class="text inp_phone form-control">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">tel</xsl:attribute>
        </input>
    </xsl:template>

    <!-- поле с автодополнением (textbox) -->
    <xsl:template match="field[@type='textbox'][ancestor::component[@type='form']]" mode="field_input">
        <xsl:variable name="SEPARATOR" select="@separator"/>
        <input class="text acpl tag_acpl">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="data-url">
                <xsl:value-of select="$BASE"/><xsl:value-of
                    select="ancestor::component/@single_template"/><xsl:value-of select="@url"/>
            </xsl:attribute>
            <xsl:attribute name="data-separator">
                <xsl:value-of select="$SEPARATOR"/>
            </xsl:attribute>
            <xsl:attribute name="value">
                <xsl:for-each select="items/item">
                    <xsl:value-of select="."/>
                    <xsl:if test="position()!=last()">
                        <xsl:value-of select="$SEPARATOR"/>
                    </xsl:if>
                </xsl:for-each>
            </xsl:attribute>
            <xsl:if test="@name = 'tags'">
                <xsl:attribute name="component_id">
                    <xsl:value-of select="generate-id(../..)"/>
                </xsl:attribute>
            </xsl:if>
        </input>
        <!--<input class="text inp_textbox">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="value"><xsl:for-each select="items/item"><xsl:value-of select="."/><xsl:if test="position()!=last()">,</xsl:if></xsl:for-each></xsl:attribute>
        </input>-->
    </xsl:template>

    <!-- числовое поле (integer) -->
    <xsl:template match="field[@type='integer'][ancestor::component[@type='form']]" mode="field_input">
        <input length="5" class="text inp_integer">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">number</xsl:attribute>
            <xsl:if test="@length">
                <xsl:attribute name="maxlength">5</xsl:attribute>
            </xsl:if>
        </input>
    </xsl:template>

    <!-- числовое поле (float) -->
    <xsl:template match="field[@type='float'][ancestor::component[@type='form']]" mode="field_input">
        <input class="text inp_float">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
        </input>
    </xsl:template>

    <xsl:template match="field[@type='money'][ancestor::component[@type='form']]" mode="field_input">
        <input class="text inp_money">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
        </input>
    </xsl:template>

    <!-- поле пароля (password) -->
    <xsl:template match="field[@type='password' and ancestor::component[@type='form']]" mode="field_input">
        <input class="text inp_password  form-control">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">password</xsl:attribute>
            <xsl:attribute name="name"><xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
            </xsl:choose></xsl:attribute>
        </input>
    </xsl:template>

    <!-- поле логического типа (boolean) -->
    <xsl:template match="field[@type='boolean'][ancestor::component[@type='form']]" mode="field_input">
        <xsl:variable name="FIELD_NAME">
            <xsl:choose>
                <xsl:when test="@tableName">
                    <xsl:value-of select="@tableName"/>
                    <xsl:if test="@language">
                        <xsl:text>[</xsl:text>
                        <xsl:value-of select="@language"/>
                        <xsl:text>]</xsl:text>
                    </xsl:if>
                    <xsl:text>[</xsl:text>
                    <xsl:value-of select="@name"/>
                    <xsl:text>]</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="@name"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <input type="hidden" name="{$FIELD_NAME}" value="0"/>
        <input class="checkbox" type="checkbox" id="{@name}{@language}" name="{$FIELD_NAME}" style="width: auto;" value="1">
            <xsl:if test=". = 1">
                <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
            <xsl:choose>
                <xsl:when test="@tag">
                    <xsl:attribute name="data-tag"><xsl:value-of select="@tag"/></xsl:attribute>
                    <xsl:attribute name="value"><xsl:value-of select="@tag"/></xsl:attribute>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:attribute name="value">1</xsl:attribute>
                </xsl:otherwise>
            </xsl:choose>
        </input>
        <label for="{@name}{@language}">
            <xsl:value-of select="concat(' ', @title)" disable-output-escaping="yes"/>
        </label>
    </xsl:template>

    <!-- поле загрузки файла (file) -->
    <xsl:template match="field[@type='file'][ancestor::component[@type='form']]" mode="field_input">
        <a class="preview" id="{generate-id(.)}_preview" target="_blank">
            <xsl:choose>
                <xsl:when test=". = ''">
                    <xsl:attribute name="style">display:none;</xsl:attribute>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:attribute name="href"><xsl:value-of select="$MEDIA_URL"/><xsl:value-of select="."/></xsl:attribute>
                </xsl:otherwise>
            </xsl:choose>
            <img alt="">
                <xsl:if test=".!=''">
                    <xsl:attribute name="src"><xsl:value-of select="$MEDIA_URL"/><xsl:choose>
                        <xsl:when test="@media_type='image'"><xsl:value-of select="."/></xsl:when>
                        <xsl:when test="@media_type='video'">resizer/w0-h0/<xsl:value-of select="."/></xsl:when>
                        <xsl:otherwise>images/icons/icon_undefined.gif</xsl:otherwise>
                    </xsl:choose></xsl:attribute>
                </xsl:if>
            </img>
        </a>
        <div class="with_append">
            <input class="text inp_file" readonly="readonly">
                <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
                <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
            </input>
            <div>
                <xsl:attribute name="class">appended_block<xsl:if test="@quickUploadPid"> appended_inner</xsl:if></xsl:attribute>
                <button onclick="{generate-id(../..)}.openFileLib(this);" type="button" link="{generate-id(.)}" preview="{generate-id(.)}_preview">...</button>
            </div>
            <xsl:if test="@quickUploadPid">
                <div class="appended_block">
                    <button onclick="{generate-id(../..)}.openQuickUpload(this);" quick_upload_path="{@quickUploadPath}" quick_upload_pid="{@quickUploadPid}" type="button" link="{generate-id(.)}" preview="{generate-id(.)}_preview">
                        <xsl:choose>
                            <xsl:when test="@quickUploadEnabled!='1'">
                                <xsl:attribute name="disabled">disabled</xsl:attribute>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:attribute name="quick_upload_enabled">
                                    <xsl:value-of select="@quickUploadEnabled"/>
                                </xsl:attribute>
                            </xsl:otherwise>
                        </xsl:choose>
                        <xsl:value-of select="$TRANSLATION[@const='BTN_QUICK_UPLOAD']"/>
                    </button>
                </div>
            </xsl:if>
            <xsl:if test="@nullable">
                <a class="lnk_clear" href="#"
                   onclick="{generate-id(../..)}.clearFileField('{generate-id(.)}',this);return false;">
                    <xsl:if test=". = ''">
                        <xsl:attribute name="style">display:none;</xsl:attribute>
                    </xsl:if>
                    <xsl:value-of select="$TRANSLATION[@const='TXT_CLEAR']"/>
                </a>
            </xsl:if>
        </div>
        <br/>
        <!--<img src="images/loading.gif" alt="" width="32" height="32" class="hidden" id="loader"/>
        <span class="progress_indicator hidden" id="indicator">0%</span>-->
    </xsl:template>

    <!-- поле выбора из списка (select) -->
    <xsl:template match="field[@type='select'][ancestor::component[@type='form']]" mode="field_input">
        <select id="{@name}">
                    <xsl:if test="@pattern">
                <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="@message">
                <xsl:attribute name="nrgn:message" xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="@message2">
                <xsl:attribute name="nrgn:message2" xmlns:nrgn="http://energine.org"><xsl:value-of select="@message2"/></xsl:attribute>
            </xsl:if>
            <xsl:attribute name="name"><xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName"/><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name"/>]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
            </xsl:choose></xsl:attribute>
            <xsl:if test="@nullable='1'">
                <option></option>
            </xsl:if>
            <xsl:apply-templates mode="field_input"/>
        </select>
    </xsl:template>

    <xsl:template match="field[@type='lookup' and ancestor::component[@type='form' and (@exttype='feed' or @exttype='grid')]]" mode="field_input">
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">hidden</xsl:attribute>
            <xsl:attribute name="id"><xsl:value-of select="@name"/></xsl:attribute>
            <xsl:attribute name="value"><xsl:value-of select="value/@id"/></xsl:attribute>
        </input>
        <div class="with_append">
            <input type="text" id="{@name}_name"  class="text acpl" autocomplete="off" spellcheck="false" style="height:32px;" value="{value}"/>
            <div class="appended_block">
                <button type="button"  style="height: 18px;">...</button>
            </div>
        </div>
    </xsl:template>

    <xsl:template match="field[@type='select' and @editor][ancestor::component[@exttype='grid' or @exttype='feed']]" mode="field_input">
            <select id="{@name}">
                <xsl:attribute name="name"><xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName"/><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name"/>]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                </xsl:choose></xsl:attribute>
                <xsl:if test="@nullable='1'">
                    <option></option>
                </xsl:if>
                <xsl:apply-templates mode="field_input"/>
            </select>
        </xsl:template>

    <xsl:template match="option[ancestor::field[@type='select'][ancestor::component[@type='form']]]" mode="field_input">
        <option value="{@id}">
            <xsl:copy-of select="attribute::*[name(.)!='id']"/>
            <xsl:value-of select="."/>
        </option>
    </xsl:template>

    <!-- поле множественного выбора (multi) -->
    <xsl:template match="field[@type='multi'][ancestor::component[@type='form']]" mode="field_input">
        <xsl:variable name="NAME"><xsl:choose>
            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name"/>]</xsl:when>
            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
        </xsl:choose>[]</xsl:variable>
        <div class="checkbox_set">
            <xsl:for-each select="options/option">
                <div>
                    <input type="checkbox" id="{generate-id(.)}" name="{$NAME}" value="{@id}" class="checkbox">
                        <xsl:if test="@selected">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:if>
                    </input>
                    <label for="{generate-id(.)}"><xsl:value-of select="."/></label>
                </div>
            </xsl:for-each>
        </div>
    </xsl:template>

    <!-- текстовое поле (text) -->
    <xsl:template match="field[@type='text'][ancestor::component[@type='form']]" mode="field_input">
        <textarea>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:value-of select="."/>
        </textarea>
    </xsl:template>

    <!-- текстовое поле (text) -->
    <xsl:template match="field[@type='code'][ancestor::component[@type='form']]" mode="field_input">
        <textarea class="code">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:value-of select="."/>
        </textarea>
    </xsl:template>

    <!-- поле типа rtf текст (htmlblock) -->
    <xsl:template match="field[@type='htmlblock'][ancestor::component[@type='form']]" mode="field_input">
        <textarea class="richEditor">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:value-of select="."/>
        </textarea>
    </xsl:template>

    <!-- поле для даты (datetime) -->
    <xsl:template match="field[@type='datetime'][ancestor::component[@type='form']]" mode="field_input">
        <input class="text inp_datetime">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
        </input>
        <script type="text/javascript">
            window.addEvent('domready', function(){
            Energine.createDateTimePicker($('<xsl:value-of select="@name"/>'), <xsl:value-of
                select="boolean(@nullable)"/>);
            });
        </script>
    </xsl:template>

    <!-- поле для даты (date) -->
    <xsl:template match="field[@type='date'][ancestor::component[@type='form']]" mode="field_input">
        <input class="text inp_date">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
        </input>
        <script type="text/javascript">
            window.addEvent('domready', function(){
            Energine.createDatePicker(
            $('<xsl:value-of select="@name"/>'),
            <xsl:value-of select="boolean(@nullable)"/>
            );
            });
        </script>
    </xsl:template>

    <!-- Для полей даты как части стандартной формы навешиваение DatePicker реализуется в js -->

    <!-- поле для даты в гридах (datetime)  -->
    <xsl:template match="field[@type='datetime'][ancestor::component[@type='form' and @exttype='grid']]" mode="field_input">
        <input class="text inp_datetime">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
        </input>
    </xsl:template>

    <!-- поле для даты в гридах (date) -->
    <xsl:template match="field[@type='date'][ancestor::component[@type='form' and @exttype='grid']]" mode="field_input">
        <input class="text inp_date">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
        </input>
    </xsl:template>

    <!-- поле для выбора родительского раздела в гридах (smap) -->
    <xsl:template match="field[@type='smap' and ancestor::component[@type='form' and (@exttype='feed' or @exttype='grid')]]" mode="field_input">
        <div class="with_append">
            <input>
                <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
                <xsl:attribute name="type">hidden</xsl:attribute>
                <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/>_id</xsl:attribute>
            </input>
            <input type="text" id="{generate-id(.)}_name" value="{@smap_name}" readonly="readonly" class="text inp_string" style="height:32px;"/>
            <div class="appended_block">
                <button type="button" style="height:30px;padding:0 12px;-moz-box-sizing:content-box;" class="smap_selector" smap_name="{generate-id(.)}_name" smap_id="{generate-id(.)}_id" field="{@name}">...</button>
            </div>
        </div>
    </xsl:template>

    <!-- поле типа thumb используется только в FileRepository -->
    <xsl:template match="field[@type='thumb'][ancestor::component[@type='form']]" mode="field_input">
        <xsl:variable name="WIDTH">
            <xsl:choose>
                <xsl:when test="@width!=''"><xsl:value-of select="@width"/></xsl:when>
                <xsl:otherwise>0</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="HEIGHT">
            <xsl:choose>
                <xsl:when test="@height!=''"><xsl:value-of select="@height"/></xsl:when>
                <xsl:otherwise>0</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <div class="preview">
            <img border="0" id="preview_{@name}" data="data_{@name}"  width="{@width}" height="{@height}">
                <xsl:choose>
                    <xsl:when test="../field[@name='upl_path']=''">
                        <xsl:attribute name="class">hidden<xsl:if test="@name!='preview'"> thumb</xsl:if></xsl:attribute>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:if test="@name!='preview'"><xsl:attribute name="class">thumb</xsl:attribute></xsl:if>
                        <xsl:attribute name="src"><xsl:value-of select="$RESIZER_URL"/>w<xsl:value-of select="$WIDTH"/>-h<xsl:value-of select="$HEIGHT"/>/<xsl:value-of  select="../field[@name='upl_path']"/>?<xsl:value-of select="generate-id()"/></xsl:attribute>
                    </xsl:otherwise>
                </xsl:choose>
            </img>
        </div>
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">hidden</xsl:attribute>
            <xsl:attribute name="id">data_<xsl:value-of select="@name"/></xsl:attribute>
        </input>
        <input type="file" id="uploader_{@name}" preview="preview_{@name}" data="data_{@name}">
            <xsl:choose>
                <xsl:when test="@name='preview'"><xsl:attribute name="class">preview</xsl:attribute></xsl:when>
                <xsl:otherwise><xsl:attribute name="class">thumb</xsl:attribute></xsl:otherwise>
            </xsl:choose>
        </input>
        <xsl:if test="@name='preview'">
            <hr/>
        </xsl:if>
    </xsl:template>


    <!-- Секция 3. Поля с правами "только на чтение". -->
    <!-- для любого поля, на которое нет прав на просмотр -->
    <xsl:template match="field[@mode=0][ancestor::component[@type='form']]"/>

    <!-- шаблон-обвязка для любого поля, на которое права только чтение -->
    <xsl:template match="field[@mode='1'][ancestor::component[@type='form']]">
        <xsl:if test=".!=''">
            <div class="field readonly">
                <xsl:apply-templates select="." mode="field_name_readonly"/>
                <xsl:apply-templates select="." mode="field_input_readonly"/>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template match="field[@mode='1'][ancestor::component[@type='form']]" mode="field_name_readonly">
        <xsl:if test="@title">
                <div class="name"><label for="{@name}">
                    <xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes"/>
                </label></div>
        </xsl:if>
    </xsl:template>

    <!-- для любого поля, на которое права только чтение -->
    <xsl:template match="field[@mode='1'][ancestor::component[@type='form']]" mode="field_input_readonly">
        <div class="control">
            <span id="{@name}_read"><xsl:value-of select="." disable-output-escaping="yes"/></span>
            <input>
                <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES_READONLY"/>
            </input>
        </div>
    </xsl:template>

    <!-- read-only поле логического типа -->
    <xsl:template match="field[@type='boolean'][@mode=1][ancestor::component[@type='form']]">
        <div class="field">
            <xsl:apply-templates select="." mode="field_name_readonly"/>
            <xsl:apply-templates select="." mode="field_input_readonly"/>
        </div>
    </xsl:template>

    <xsl:template match="field[@type='boolean'][@mode=1][ancestor::component[@type='form']]" mode="field_input_readonly">
        <xsl:variable name="FIELD_NAME"><xsl:choose>
            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name"/>]</xsl:when>
            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
        </xsl:choose></xsl:variable>
        <input type="hidden" name="{$FIELD_NAME}" value="{.}"/>
        <input type="checkbox" id="{@name}" name="{$FIELD_NAME}" disabled="disabled" class="checkbox">
            <xsl:if test=".=1">
                <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
        </input>
    </xsl:template>

    <!-- для полей HTMLBLOCK и TEXT на которые права только чтение -->
    <xsl:template match="field[@type='htmlblock' or @type='text'][@mode='1'][ancestor::component[@type='form']]" mode="field_input_readonly">
        <div class="read control"><xsl:value-of select="." disable-output-escaping="yes"/></div>
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES_READONLY"/>
        </input>
    </xsl:template>

    <xsl:template match="field[@type='code'][@mode='1'][ancestor::component[@type='form']]" mode="field_input_readonly">
        <textarea class="code">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:value-of select="."/>
        </textarea>
    </xsl:template>

    <!-- для поля EMAIL на которое права только чтение -->
    <xsl:template match="field[@type='email'][@mode='1'][ancestor::component[@type='form']]" mode="field_input_readonly">
        <a href="mailto:{.}" class="email"><xsl:value-of select="."/></a>
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES_READONLY"/>
        </input>
    </xsl:template>

    <xsl:template match="field[@type='file'][@mode='1'][ancestor::component[@type='form']]" mode="field_input_readonly">
        <div>
        <xsl:choose>
            <xsl:when test="(@media_type='video' or @media_type='image') and .!=''">
                <a class="preview" id="{generate-id(.)}_preview" target="_blank">
                    <xsl:attribute name="href"><xsl:value-of select="$MEDIA_URL"/><xsl:value-of select="."/></xsl:attribute>
                        <img alt="">
                                <xsl:attribute name="src"><xsl:value-of select="$MEDIA_URL"/><xsl:choose>
                                    <xsl:when test="@media_type='image'"><xsl:value-of select="."/></xsl:when>
                                    <xsl:when test="@media_type='video'">resizer/w0-h0/<xsl:value-of select="."/></xsl:when>
                                    <xsl:otherwise>images/icons/icon_undefined.gif</xsl:otherwise>
                                </xsl:choose></xsl:attribute>
                        </img>
                    </a>
            </xsl:when>
            <xsl:otherwise>
                <a href="{$MEDIA_URL}{.}" target="_blank"><xsl:value-of select="."/></a>
            </xsl:otherwise>
        </xsl:choose>
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES_READONLY"/>
        </input>
        </div>
    </xsl:template>

    <!-- read-only поле типа select -->
    <xsl:template match="field[@type='select'][@mode='1'][ancestor::component[@type='form']]">
        <xsl:if test="options/option[@selected='selected']">
            <div class="field readonly">
                <xsl:apply-templates select="." mode="field_name_readonly"/>
                <xsl:apply-templates select="." mode="field_input_readonly"/>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template match="field[@type='select'][@mode='1'][ancestor::component[@type='form']]" mode="field_input_readonly">
        <span class="read"><xsl:value-of select="options/option[@selected='selected']"/></span>
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES_READONLY"/>
            <xsl:attribute name="value"><xsl:value-of select="options/option[@selected='selected']/@id"/></xsl:attribute>
        </input>
    </xsl:template>

    <!-- read-only поле типа multiselect -->
    <xsl:template match="field[@type='multi'][@mode='1'][ancestor::component[@type='form']]">
        <div class="field">
            <xsl:apply-templates select="." mode="field_name_readonly"/>
            <xsl:apply-templates select="." mode="field_input_readonly"/>
        </div>
    </xsl:template>

    <xsl:template match="field[@type='multi'][@mode='1'][ancestor::component[@type='form']]" mode="field_input_readonly">
        <div class="read">
            <xsl:for-each select="options/option[@selected='selected']">
                <xsl:value-of select="."/><br/>
                <input type="hidden" value="{@id}">
                    <xsl:attribute name="name"><xsl:choose>
                        <xsl:when test="../../@tableName"><xsl:value-of select="../../@tableName"/>[<xsl:value-of select="../../@name"/>]</xsl:when>
                        <xsl:otherwise><xsl:value-of select="../../@name"/></xsl:otherwise>
                    </xsl:choose>[]</xsl:attribute>
                </input>
            </xsl:for-each>
        </div>
    </xsl:template>

    <!-- read-only поле типа image -->
    <xsl:template match="field[@type='image'][@mode='1'][ancestor::component[@type='form']]" mode="field_input_readonly">
        <div class="image">
            <img src="{.}"/>
            <input>
                <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES_READONLY"/>
            </input>
        </div>
    </xsl:template>

    <!-- read-only поле типа date и datetime -->
<!--    <xsl:template match="field[@type='date' or @type='datetime'][@mode='1'][ancestor::component[@type='form']]" mode="field_input_readonly">

        <div class="read"><xsl:value-of select="." disable-output-escaping="yes"/></div>
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES_READONLY"/>
        </input>
    </xsl:template>-->


    <!--
        Секция 4. Остальные поля формы.
        В этой секции собраны остальные поля, которым не нужна обычная обвязка.
     -->
    <!-- поле типа hidden -->
    <xsl:template match="field[@type='hidden'][ancestor::component[@type='form']]">
        <input type="hidden" id="{@name}" value="{.}">
            <xsl:attribute name="name"><xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName"/><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name"/>]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
            </xsl:choose></xsl:attribute>
        </input>
    </xsl:template>

    <!-- для PK  -->
    <xsl:template match="field[@key='1' and @type='hidden'][ancestor::component[@type='form']]">
        <input type="hidden" id="{@name}" value="{.}" primary="primary">
            <xsl:attribute name="name"><xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
            </xsl:choose></xsl:attribute>
        </input>
    </xsl:template>

    <!-- поле типа captcha -->
    <xsl:template match="field[@type='captcha'][ancestor::component[@type='list']]"/>

    <xsl:template match="field[@type='captcha'][ancestor::component[@type='form']]">
        <div class="field">
            <div class="g-recaptcha" data-sitekey="{.}"></div>
        </div>
    </xsl:template>

    <!-- поле error -->
    <xsl:template match="field[@name='error_message'][ancestor::component[@type='form']]">
        <div class="error"><xsl:value-of select="." disable-output-escaping="yes"/></div>
    </xsl:template>


    <!-- Секция 5. Поля, которые не относятся к стандартному выводу. -->
    <!-- поле для загрузки файла в файловом репозитории -->
    <xsl:template match="field[@name='upl_path'][ancestor::component[@sample='FileRepository' and @type='form']]" mode="field_input">
        <div class="preview">
            <img border="0" id="preview" class="hidden"/>
        </div>
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">hidden</xsl:attribute>
            <xsl:attribute name="id">data</xsl:attribute>
        </input>
        <input type="file" id="uploader"/>
    </xsl:template>

    <!-- заполненное поле для загрузки файла в файловом репозитории -->
    <xsl:template match="field[@name='upl_path'][.!=''][ancestor::component[@sample='FileRepository' and @type='form']]" mode="field_input">
        <div class="preview">
            <img border="0" id="preview" src="{$RESIZER_URL}w298-h224/{.}?anticache={generate-id()}" alt=""/>
        </div>
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">hidden</xsl:attribute>
            <xsl:attribute name="id">data</xsl:attribute>
        </input>
        <input type="file" id="uploader"/>
    </xsl:template>

    <xsl:template match="field[@name='upl_path'][@mode='1'][ancestor::component[@sample='FileRepository' and @type='form']]">
        <div class="field">
            <div class="name">
                <label for="{@name}">
                    <xsl:value-of select="@title" disable-output-escaping="yes"/>
                </label>
            </div>
            <div class="control" >
                <div class="preview">
                    <img border="0" id="preview" src="{$RESIZER_URL}w298-h224/{.}" alt=""/>
                </div>
                <input>
                    <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
                    <xsl:attribute name="type">hidden</xsl:attribute>
                    <xsl:attribute name="id">data</xsl:attribute>
                </input>
            </div>
        </div>
    </xsl:template>

    <!-- поле копирования структуры в редакторе сайтов -->
    <xsl:template match="field[@name='copy_site_structure']" mode="field_input">
        <input type="checkbox" onchange="document.getElementById('{@name}').disabled = !this.checked;" class="checkbox"/>
        <select id="{@name}" disabled="disabled">
            <xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
            <xsl:apply-templates mode="field_input"/>
        </select>
    </xsl:template>


    <!-- Секция 6. Обработка attachments. -->
    <!-- в виде превью -->
    <xsl:template match="field[@name='attachments']" mode="preview">
        <xsl:param name="PREVIEW_WIDTH"/>
        <xsl:param name="PREVIEW_HEIGHT"/>
        <xsl:variable name="URL"><xsl:value-of select="$RESIZER_URL"/>w<xsl:value-of select="$PREVIEW_WIDTH"/>-h<xsl:value-of select="$PREVIEW_HEIGHT"/>/<xsl:value-of select="recordset/record[1]/field[@name='file']"/></xsl:variable>
        <img width="{$PREVIEW_WIDTH}" height="{$PREVIEW_HEIGHT}">
            <xsl:choose>
                <xsl:when test="recordset">
                    <xsl:attribute name="src"><xsl:value-of select="$URL"/></xsl:attribute>
                    <xsl:attribute name="alt"><xsl:value-of select="recordset/record[1]/field[@name='name']"/></xsl:attribute>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:attribute name="src">http://placehold.it/<xsl:value-of select="$PREVIEW_WIDTH"/>x<xsl:value-of select="$PREVIEW_HEIGHT"/>/</xsl:attribute>
                    <xsl:attribute name="alt"><xsl:value-of select="$TRANSLATION[@const='TXT_NO_IMAGE']"/></xsl:attribute>
                </xsl:otherwise>
            </xsl:choose>
        </img>
        <xsl:if test="recordset/record[1]/field[@name='file']/video">
            <i class="play_button"></i>
            <span class="image_info">00:00</span>
        </xsl:if>
    </xsl:template>

    <!-- в виде плеера -->
    <xsl:template match="field[@name='attachments']" mode="player">
        <xsl:param name="PLAYER_WIDTH"/>
        <xsl:param name="PLAYER_HEIGHT"/>
        <xsl:if test="recordset and (count(recordset/record[field[@name='type'] = 'video']) &gt; 0)">
            <div class="player_box" id="playerBox">
                <xsl:call-template name="VIDEO_PLAYER">
                    <xsl:with-param name="PLAYER_WIDTH" select="$PLAYER_WIDTH"/>
                    <xsl:with-param name="PLAYER_HEIGHT" select="$PLAYER_HEIGHT"/>
                    <xsl:with-param name="FILE" select="recordset/record[field[@name='type'] = 'video'][1]/field[@name='file']"/>
                </xsl:call-template>
            </div>
        </xsl:if>
    </xsl:template>

    <!-- в виде карусели -->
    <xsl:template match="field[@name='attachments']" mode="carousel">
        <xsl:param name="PREVIEW_WIDTH"/>
        <xsl:param name="PREVIEW_HEIGHT"/>
        <xsl:if test="(count(recordset/record) &gt; 1) or not(recordset/record/field[@name='file']/image)">
            <div class="carousel_box">
                <xsl:if test="not(recordset/record/field[@name='file']/image)">
                    <xsl:attribute name="style">display:none;</xsl:attribute>
                </xsl:if>
                <!--<div class="carousel_title">
                    <xsl:value-of select="@title"/>
                </div>-->
                <div class="carousel" id="playlist">
                    <div class="carousel_viewbox viewbox">
                        <ul>
                            <xsl:for-each select="recordset/record">
                                <li>
                                    <div class="carousel_image" id="{field[@name='id']}_imgc">
                                        <a href="{field[@name='file']/video | field[@name='file']/image}" xmlns:nrgn="http://energine.org" nrgn:media_type="{name(field[@name='file']/*[1])}">
                                            <xsl:choose>
                                                <xsl:when test="field[@name='file']/video"><img src="{$RESIZER_URL}w{$PREVIEW_WIDTH}-h{$PREVIEW_HEIGHT}/{field[@name='file']/*[1]/@image}" alt="{field[@name='name']}" width="{$PREVIEW_WIDTH}" height="{$PREVIEW_HEIGHT}"/></xsl:when>
                                                <xsl:otherwise><img src="{$RESIZER_URL}w{$PREVIEW_WIDTH}-h{$PREVIEW_HEIGHT}/{field[@name='file']/*[1]/@image}" alt="{field[@name='name']}" width="{$PREVIEW_WIDTH}" height="{$PREVIEW_HEIGHT}"/></xsl:otherwise>
                                            </xsl:choose>
                                             <xsl:if test="field[@name='file']/video">
                                                 <i class="icon play_icon"></i>
                                             </xsl:if>
                                         </a>
                                     </div>
                                 </li>
                             </xsl:for-each>
                         </ul>
                     </div>
                     <a class="previous_control" href="#"><xsl:text disable-output-escaping="yes">&amp;lt;</xsl:text></a>
                     <a class="next_control" href="#"><xsl:text disable-output-escaping="yes">&amp;gt;</xsl:text></a>
                 </div>
             </div>
        </xsl:if>
    </xsl:template>
    <xsl:template match="field[@name='upl_id'][ancestor::component[@type='form' and @exttype='grid']]" mode="field_content">
            <div class="control toggle type_file" id="control_{@language}_{@name}">
                <xsl:apply-templates select="." mode="field_input"/>
            </div>
        </xsl:template>

    <!-- поле для выбора upl_id гридах -->
    <xsl:template match="field[@name='upl_id' and ancestor::component[@type='form' and (@exttype='feed' or @exttype='grid')]]" mode="field_input">
        <a class="preview" id="{generate-id(.)}_preview" target="_blank">
                <xsl:attribute name="href"><xsl:value-of select="$MEDIA_URL"/><xsl:value-of select="@upl_path"/></xsl:attribute>
                <xsl:if test="not(@upl_path)">
                    <xsl:attribute name="class">hidden</xsl:attribute>
                </xsl:if>
                <img alt="">
                    <xsl:attribute name="src"><xsl:value-of select="$MEDIA_URL"/><xsl:choose>
                        <xsl:when test="@media_type='image'"><xsl:value-of select="@upl_path"/></xsl:when>
                        <xsl:when test="@media_type='video'">resizer/w0-h0/<xsl:value-of select="@upl_path"/></xsl:when>
                        <xsl:otherwise>images/icons/icon_undefined.gif</xsl:otherwise>
                    </xsl:choose></xsl:attribute>
                </img>
        </a>
        <div class="with_append">
            <input>
                <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
                <xsl:attribute name="type">hidden</xsl:attribute>
                <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/>_id</xsl:attribute>
            </input>
            <input type="text" id="{generate-id(.)}_name" value="{@upl_path}" readonly="readonly" class="text inp_string" style="height: 32px;"/>
            <div class="appended_block">
                <button type="button" style="width:22px;height:18px;" class="attachment_selector" data-name="{generate-id(.)}_name" data-id="{generate-id(.)}_id" data-field="{@name}" data-preview="{generate-id(.)}_preview">...</button>
            </div>
        </div>
    </xsl:template>

    <xsl:template match="field[@type='tab'][ancestor::component[@type='form']]"/>

    <xsl:template match="field[@type='tab'][ancestor::component[@type='form']]" mode="field_name">
        <li data-src="{ancestor::component/@single_template}{.}">
            <a href="#{generate-id(.)}"><xsl:value-of select="@title" /></a>
        </li>
    </xsl:template>

    <xsl:template match="field[@type='tab'][ancestor::component[@type='form']]" mode="field_content">
        <div id="{generate-id(.)}"></div>
    </xsl:template>

    <xsl:template match="field[@type='textbox'][@mode='1'][ancestor::component[@type='form']]" mode="field_input_readonly">
           <xsl:variable name="SEPARATOR" select="@separator"/>
           <input>
               <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES_READONLY"/>
               <xsl:attribute name="value">
                   <xsl:for-each select="items/item">
                       <xsl:value-of select="."/>
                       <xsl:if test="position()!=last()">
                           <xsl:value-of select="$SEPARATOR"/>
                       </xsl:if>
                   </xsl:for-each>
               </xsl:attribute>
           </input>

           <!--<input class="text inp_textbox">
               <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
               <xsl:attribute name="value"><xsl:for-each select="items/item"><xsl:value-of select="."/><xsl:if test="position()!=last()">,</xsl:if></xsl:for-each></xsl:attribute>
           </input>-->
       </xsl:template>
    <xsl:template name="FORM_ELEMENT_ATTRIBUTES">
            <xsl:if test="not(@type='text') and not(@type='htmlblock')">
                <xsl:attribute name="type">text</xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
            </xsl:if>
            <xsl:attribute name="id">
                <xsl:value-of select="@name"/>
                <xsl:if test="@language">_<xsl:value-of select="@language"/></xsl:if>
            </xsl:attribute>
            <xsl:attribute name="name"><xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName"/><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name"/>]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
            </xsl:choose></xsl:attribute>
            <xsl:if test="@length and not(@type='htmlblock')">
                <xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="@pattern">
                <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="@message">
                <xsl:attribute name="nrgn:message" xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="@message2">
                <xsl:attribute name="nrgn:message2" xmlns:nrgn="http://energine.org"><xsl:value-of select="@message2"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="@placeholder">
                <xsl:attribute name="placeholder"><xsl:value-of select="@placeholder"/></xsl:attribute>
            </xsl:if>
        </xsl:template>

        <!-- именованный шаблон с дефолтным набором атрибутов для элемента формы, на который права только на чтение - НЕ ПЕРЕПИСЫВАТЬ В ДРУГОМ МЕСТЕ! -->
        <xsl:template name="FORM_ELEMENT_ATTRIBUTES_READONLY">
            <xsl:attribute name="type">hidden</xsl:attribute>
            <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
            <xsl:attribute name="id"><xsl:value-of select="@name"/></xsl:attribute>
            <xsl:attribute name="name"><xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName"/><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name"/>]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
            </xsl:choose></xsl:attribute>
        </xsl:template>
</xsl:stylesheet>
