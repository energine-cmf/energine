<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  
    xmlns="http://www.w3.org/1999/xhtml">

    <!--
        В этом файле собраны базовые правила обработки с низким приоритетом. Файл импортируется в include.xslt,
        что позволяет использовать правило apply-imports в шаблонах более высокого уровня.
        Для переопределения этих правил нужно создать такой же файл и подключить его (импортировать) аналогично 
        в нужный модуль. Также здесь собраны некоторые именованные шаблоны - импортирование позволяет переопределять
        их позже в site/transformers.
    -->
    
    <!-- 
        Form elements
        В этой секции собраны правила вывода полей формы, которые создают сам html-элемент (input, select, etc.).
     -->
    <!-- строковое поле (string), или поле, к которому не нашлось шаблона -->
    <xsl:template match="field[ancestor::component[@type='form']]">
        <input class="text inp_string">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>        	
        </input>
    </xsl:template>

    <!-- поле для почтового адреса (email) -->
    <xsl:template match="field[@type='email'][ancestor::component[@type='form']]">
        <input class="text inp_email">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
        </input>
    </xsl:template>

    <!-- поле для телефона (phone)-->
    <xsl:template match="field[@type='phone'][ancestor::component[@type='form']]">
        <input class="text inp_phone">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
        </input>
    </xsl:template>    

    <!-- числовое поле (integer) -->
    <xsl:template match="field[@type='integer'][ancestor::component[@type='form']]">
        <input length="5" class="text inp_integer">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>            
            <xsl:if test="@length">
                <xsl:attribute name="maxlength">5</xsl:attribute>
            </xsl:if>
        </input>
    </xsl:template>

    <!-- числовое поле (float) -->
    <xsl:template match="field[@type='float'][ancestor::component[@type='form']]">
        <input class="text inp_float">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>            
        </input>
    </xsl:template>

    <!-- поле пароля (password) -->
    <xsl:template match="field[@type='password'][ancestor::component[@type='form']]">
        <input class="text inp_password">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>            
            <xsl:attribute name="type">password</xsl:attribute>
            <xsl:attribute name="name">
                <xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>            
        </input>
    </xsl:template>

    <!-- поле логического типа (boolean) -->
    <xsl:template match="field[@type='boolean'][ancestor::component[@type='form']]">
        <xsl:variable name="FIELD_NAME">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <input type="hidden" name="{$FIELD_NAME}" value="0" />
        <input class="checkbox" type="checkbox" id="{@name}" name="{$FIELD_NAME}" style="width: auto;" value="1">
            <xsl:if test=". = 1">
                <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
        </input>
        <label for="{@name}"><xsl:value-of select="concat(' ', @title)" disable-output-escaping="yes" /></label>
    </xsl:template>
    
    <!-- поле типа видеофайл (video) -->
    <xsl:template match="field[@type='video'][ancestor::component[@type='form']]">
        <!-- вынесено в отдельную функцию для того, чтоб была возможность вызвать не в форме -->
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
        <div class="video" id="{generate-id(.)}_preview"></div>    
        <xsl:if test=".!=''">
            <script type="text/javascript">
                window.addEvent('domready', insertVideo.pass(['<xsl:value-of select="$BASE"/><xsl:value-of select="."/>', '<xsl:value-of select="generate-id(.)"/>']));
            </script>
            <a href="#" onclick="return {generate-id(ancestor::recordset)}.removeFilePreview.run(['{generate-id(.)}', this], {generate-id(ancestor::recordset)});">
                <xsl:value-of select="@deleteFileTitle"/>
            </a>
        </xsl:if>
        <xsl:variable name="FIELD_ID">tmp_<xsl:value-of select="generate-id()"/></xsl:variable>
        <input type="file" id="{$FIELD_ID}" name="file" field="{generate-id(.)}" preview="{generate-id(.)}_preview" onchange="{generate-id(ancestor::recordset)}.uploadVideo.bind({generate-id(ancestor::recordset)})(this);"/>
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">hidden</xsl:attribute>
            <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>            
        </input>
    </xsl:template>

    <!-- поле для загрузки изображения из репозитория, используется в админчасти (image) -->
    <xsl:template match="field[@type='image'][ancestor::component[@type='form']]">
        <div class="image">
            <img id="{generate-id(.)}_preview">
                <xsl:if test=".!=''">
                    <xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
                </xsl:if>
            </img>
        </div>
        <xsl:if test=".!=''">
            <a href="#" onclick="{generate-id(ancestor::recordset)}.removeFilePreview.run(['{generate-id(.)}', '{generate-id(.)}_preview', this], {generate-id(ancestor::recordset)}); $(this).destroy();new Event(arguments[0] || window.event).stop();">
                <xsl:value-of select="@deleteFileTitle"/>
            </a>
        </xsl:if>
        <input class="text inp_file" readonly="readonly">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="id"><xsl:value-of select="generate-id(.)" /></xsl:attribute>
        </input>
        <button onclick="{generate-id(../..)}.openFileLib(this);" type="button" link="{generate-id(.)}" preview="{generate-id(.)}_preview">...</button>
    </xsl:template>
    
    <!-- поле типа file -->
    <xsl:template match="field[@type='file'][ancestor::component[@type='form']]">
        <div id="{generate-id(.)}_preview" class="file"></div>
        <xsl:if test=".!=''">
            <a href="{.}" target="_blank"><xsl:value-of select="."/></a>
            <a href="#" onclick="return {generate-id(ancestor::recordset)}.removeFilePreview.run(['{generate-id(.)}', this], {generate-id(ancestor::recordset)});">
                <xsl:value-of select="@deleteFileTitle"/>
            </a>
        </xsl:if>
        <div></div>
        <input class="text inp_file" readonly="readonly">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
        </input>
        <button onclick="{generate-id(../..)}.openFileLib(this);" type="button" link="{generate-id(.)}" preview="{generate-id(.)}_preview">...</button>
    </xsl:template>
    
    <!-- поле типа pfile -->
    <xsl:template match="field[@type='pfile'][ancestor::component[@type='form']]">
        <div class="image">
            <img alt="" border="0" id="{generate-id(.)}_preview">
                <xsl:if test="@is_image">
                    <xsl:attribute name="style">display: hidden;</xsl:attribute>
                    <xsl:if test=".!=''">
                        <xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
                    </xsl:if>
                </xsl:if>
            </img>
        </div>
        <xsl:if test=".!=''">
            <a href="#" onclick="return {generate-id(ancestor::recordset)}.removeFilePreview.run(['{generate-id(.)}', '{generate-id(.)}_preview', this], {generate-id(ancestor::recordset)});">
                <xsl:value-of select="@deleteFileTitle"/>
            </a>
        </xsl:if>
        <div style="margin-bottom: 5px;">
            <a href="{.}" target="_blank" id='{generate-id(.)}_link'><xsl:value-of select="."/></a>
        </div>
        <xsl:variable name="FIELD_ID">tmp_<xsl:value-of select="generate-id()"/></xsl:variable>
        <input type="file" id="{$FIELD_ID}" name="file" field="{generate-id(.)}" link="{generate-id(.)}_link" preview="{generate-id(.)}_preview" onchange="{generate-id(ancestor::recordset)}.upload.bind({generate-id(ancestor::recordset)})(this);"/>       
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">hidden</xsl:attribute>
            <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
        </input>
    </xsl:template>
    
    <!-- поле типа prfile -->
    <xsl:template match="field[@type='prfile'][ancestor::component[@type='form']]">
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
        <div style="margin-bottom: 5px;">
            <a href="{.}" target="_blank" id='{generate-id(.)}_link'><xsl:value-of select="."/></a>
        </div>
        <xsl:if test=".!=''">
            <a href="#" onclick="return {generate-id(ancestor::recordset)}.removeFilePreview.run(['{generate-id(.)}', this], {generate-id(ancestor::recordset)});">
                <xsl:value-of select="@deleteFileTitle"/>
            </a>
        </xsl:if>    
        <xsl:variable name="FIELD_ID">tmp_<xsl:value-of select="generate-id()"/></xsl:variable>
        <input type="file" id="{$FIELD_ID}" name="file" field="{generate-id(.)}" link="{generate-id(.)}_link" preview="{generate-id(.)}_preview" protected="protected" onchange="{generate-id(ancestor::recordset)}.upload.bind({generate-id(ancestor::recordset)})(this);"/>
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">hidden</xsl:attribute>
            <xsl:attribute name="id"><xsl:value-of select="generate-id(.)"/></xsl:attribute>            
        </input>
    </xsl:template>

    <!-- поле выбора из списка (select) -->
    <xsl:template match="field[@type='select'][ancestor::component[@type='form']]">
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

    <!-- поле множественного выбора (multi) -->
    <xsl:template match="field[@type='multi'][ancestor::component[@type='form']]">
        <xsl:variable name="NAME"><xsl:choose>
            <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
            <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
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
    <xsl:template match="field[@type='text'][ancestor::component[@type='form']]">
        <textarea>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
        </textarea>
    </xsl:template>    

    <!-- поле типа rtf текст (htmlblock) -->
    <xsl:template match="field[@type='htmlblock'][ancestor::component[@type='form']]">
        <textarea class="richEditor">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
        </textarea>
    </xsl:template>
    
    <!-- поле для даты (datetime) - никогда не использовался, устарела верстка -->
    <xsl:template match="field[@type='datetime'][ancestor::component[@type='form']]">
        <input class="text inp_date" readonly="readonly">
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>            
        </input>  
        <button type="button" onclick="calendarKick({@name},document.body.scrollLeft+event.clientX,document.body.scrollTop+event.clientY,true,null,null,true);" style="height:22px;margin-left:2px;">...</button>
    </xsl:template>
    
    <!-- поле для даты (date) -->
    <xsl:template match="field[@type='date'][ancestor::component[@type='form']]">
        <input>
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">hidden</xsl:attribute>
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
            <xsl:text> </xsl:text>
            <img src="images/calendar.gif" class="set_date" onclick="{generate-id(../..)}.showCalendar('{@name}', event); "/>
        </span>
    </xsl:template>
    
    <!-- поле типа hidden -->
    <xsl:template match="field[@type='hidden'][ancestor::component[@type='form']]">
        <input type="hidden" id="{@name}" value="{.}">
            <xsl:attribute name="name">
                <xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
        </input>
    </xsl:template>
    
    <!-- именованный шаблон с дефолтным набором атрибутов для элемента формы - не переписывать в другом месте! -->
    <xsl:template name="FORM_ELEMENT_ATTRIBUTES">
        <xsl:if test="not(@type='text') and not(@type='htmlblock')">
            <xsl:attribute name="type">text</xsl:attribute>
        </xsl:if>
        <xsl:attribute name="id"><xsl:value-of select="@name"/></xsl:attribute>
        <xsl:attribute name="name">
            <xsl:choose>
                <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        <xsl:if test="@length and not(@type='htmlblock')">
            <xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@pattern">
            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@message">
            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
        </xsl:if>
    </xsl:template>
    
    <!-- /form elements -->
    
    <!-- именованный шаблон для построения заголовка окна -->
    <xsl:template name="build_title">
        <xsl:choose>
            <xsl:when test="$DOC_PROPS[@name='title']/@alt!=''">
                <xsl:value-of select="$DOC_PROPS[@name='title']/@alt"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:for-each select="$COMPONENTS[@class='BreadCrumbs']/recordset/record">
                    <xsl:sort data-type="text" order="descending" select="position()"/>
                    <xsl:if test="field[@name='Name'] != ''">
                        <xsl:if test="following-sibling::record/field[@name='Name'] != ''"> / </xsl:if>           
                        <xsl:value-of select="field[@name='Name']" />                        
                    </xsl:if>
                </xsl:for-each>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!-- именованный шаблон для подключения интерфейсных скриптов  -->
    <xsl:template name="interface_js"/>

</xsl:stylesheet>