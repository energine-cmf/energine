<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 

    xmlns:set="http://exslt.org/sets"
    extension-element-prefixes="set">

    <xsl:template match="component[@type='list']">
    	<form method="post" action="{@action}">
            <xsl:if test="@exttype='grid'">
                <xsl:attribute name="class">e-grid-form</xsl:attribute>
            </xsl:if>
            <input type="hidden" name="componentAction" value="{@componentAction}"/>
            <xsl:apply-templates/>
    	</form>
    </xsl:template>

    <xsl:template match="component[@componentAction='showPageToolbar' and @exttype='grid' and @type='list' and @sample='DivisionEditor']"/>

    <xsl:template match="component[@type='list']/recordset">
        <xsl:choose>
            <xsl:when test="not(@empty)">
                <ol><xsl:apply-templates/></ol>
            </xsl:when>
            <xsl:otherwise><b><xsl:value-of select="@empty"/></b></xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="component[@type='list']/recordset/record">
        <li><xsl:apply-templates/></li>
    </xsl:template>

    <xsl:template match="component[@type='list' and @exttype='grid']/recordset">
        <xsl:variable name="NAME" select="../@name"/>
        <div id="{generate-id(.)}" class="e-pane e-pane-has-t-toolbar1  e-pane-has-b-toolbar1" template="{$BASE}{$LANG_ABBR}{../@template}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
            <!--<xsl:if test="../toolbar">
                <xsl:attribute name="class">e-pane e-pane-has-t-toolbar1 e-pane-has-b-toolbar1</xsl:attribute>
            </xsl:if>-->
            <xsl:if test="../@quickUploadPath">
                <xsl:attribute name="quick_upload_path">
                    <xsl:value-of select="../@quickUploadPath"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="../@quickUploadPid">
                <xsl:attribute name="quick_upload_pid">
                    <xsl:value-of select="../@quickUploadPid"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="../@quickUploadEnabled">
                <xsl:attribute name="quick_upload_enabled">
                    <xsl:value-of select="../@quickUploadEnabled"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="../@moveFromId">
                <xsl:attribute name="move_from_id">
                    <xsl:value-of select="../@moveFromId"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:call-template name="BUILD_GRID"/>
            <xsl:if test="count($TRANSLATION[@component=$NAME])&gt;0">
                <script type="text/javascript">
                    <xsl:for-each select="$TRANSLATION[@component=$NAME]">
                        Energine.translations.set('<xsl:value-of select="@const"/>', '<xsl:value-of select="."/>');
                    </xsl:for-each>
                </script>
            </xsl:if>
        </div>
    </xsl:template>
    
    <!-- Выводим переводы для WYSIWYG -->
    <xsl:template match="document/translations[translation[@component=//component[@type='form' and @exttype='grid'][descendant::field[@type='htmlblock']]/@name]]">
            <script type="text/javascript">
                <xsl:for-each select="translation[@component=$COMPONENTS[@type='form' and @exttype='grid'][descendant::field[@type='htmlblock']]/@name]">
                    Energine.translations.set('<xsl:value-of select="@const"/>', '<xsl:value-of select="."/>');
                </xsl:for-each>
            </script>
    </xsl:template>

    <!--Фильтр обрабатывается в BUILD_GRID-->
    <xsl:template match="component[@type='list' and @exttype='grid']/filter"/>

    <xsl:template name="BUILD_GRID">
        <xsl:variable name="FIELDS" select="record/field"/>
        <xsl:variable name="TAB_ID" select="generate-id(record)"/>

        <div class="e-pane-t-toolbar">
            <ul class="e-pane-toolbar e-tabs">
                <xsl:choose>
                    <xsl:when test="$FIELDS[@language]">
                        <xsl:for-each select="set:distinct($FIELDS[@language]/@tabName)">
                            <xsl:variable name="TAB_NAME" select="."/>
                            <li>
                                <a href="#{$TAB_ID}"><xsl:value-of select="."/></a>
                                <span class="data">{ lang: <xsl:value-of select="$FIELDS[@tabName=$TAB_NAME]/@language"/> }</span>
                            </li>
                        </xsl:for-each>        
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:for-each select="set:distinct($FIELDS/@tabName)">
                            <!--<xsl:variable name="TAB_NAME" select="."/>-->
                            <li>
                                <a href="#{$TAB_ID}"><xsl:value-of select="."/></a>
                            </li>
                        </xsl:for-each>        
                    </xsl:otherwise>
                </xsl:choose>
            </ul>
        </div>
        <!-- Хитрый фикс для оперы с добавлением просто дива вокруг e-pane-content -->
        <div>
        <!-- /Хитрый фикс для оперы с добавлением просто дива -->
            <div class="e-pane-content">
                <div id="{$TAB_ID}">
                    <div class="grid">                        
                        <!-- если есть хотя бы одно поле с типом string -->
                        <!-- или если есть узел filters -->
                        <xsl:if test="ancestor::component/filter">
                            <div class="grid_toolbar clearfix">
                                <div class="filters_block">
	                                <div class="filters_block_inner toggled clearfix" style="height: 0;">
	                                    <div class="filters">
	                                        <div class="filter">
                                                <span class="f_select_container operand_container">
                                                    <select class="filters_operand">
                                                        <option value="OR">OR</option>
                                                        <option value="AND">AND</option>
                                                    </select>
                                                </span>
		                                        <span class="f_select_container fieldname_container">
		                                            <select name="fieldName" class="f_fields">
		                                                <xsl:for-each select="ancestor::component/filter/field">
		                                                    <option value="[{@tableName}][{@name}]" type="{@type}">
		                                                        <xsl:value-of select="@title"/>
		                                                    </option>
		                                                </xsl:for-each>
		                                            </select>
		                                        </span>
		                                        <span class="f_select_container">
		                                            <select name="condition" class="f_condition">
		                                                <xsl:for-each select="ancestor::component/filter/operators/operator">
		                                                    <option value="{@name}">
		                                                        <xsl:attribute name="data-types">
		                                                            <xsl:for-each select="types/type"><xsl:value-of select="."/><xsl:if test="position()!=last()">|</xsl:if></xsl:for-each></xsl:attribute>
		                                                        <xsl:value-of select="@title"/>
		                                                    </option>
		                                                </xsl:for-each>
		                                            </select>
		                                        </span>
	                                            <span class="f_query_container">
	                                                <input type="text" class="query"/>
	                                            </span>
	                                            <span class="f_query_container hidden">
	                                                <input type="text" class="query"/>
	                                            </span>
	                                            <!--<span class="f_query_date_container hidden"><input type="text" class="query"/><input type="text" class="hidden query"/></span>-->
		                                        <div class="filter_remove_block">
		                                            <button type="button" class="remove_filter" disabled="true">-</button>
			                                    </div>
	                                        </div>
		                                    <div class="filter_add_block">
			                                    <button type="button" class="add_filter">+</button>
		                                    </div>
	                                    </div>
	                                    <div class="filter_controls">
	                                        <button type="button" class="f_apply">
	                                            <xsl:value-of select="ancestor::component/filter/@apply"/>
	                                        </button>
	                                        <!--<xsl:text>&#160;</xsl:text>-->
	                                        <a href="#" class="f_reset">
	                                            <xsl:value-of select="ancestor::component/filter/@reset"/>
	                                        </a>
	                                    </div>
		                                <!--<div class="filter_save_block">
			                                <div class="filter_divider"></div>
			                                <input type="text" class="filter_name" placeholder="FILTER_NAME" />
			                                <button type="button" class="save_filter">BTN_FILTER_SAVE</button>
			                                <select class="load_filter">
				                                <option value="false">LOAD_FILTER</option>
				                                <option value="filter1">Filter1</option>
				                                <option value="filter2">Filter2</option>
			                                </select>
		                                </div>-->
		                                <div class="filter_divider"></div>
		                            </div>
	                                <a href="#" class="filter_toggle">
		                                <i class="fa fa-caret-up"></i>
	                                </a>
                                </div>

                                <xsl:if test="ancestor::component[@sample='FileRepository']">
                                    <div class="grid_breadcrumbs" id="breadcrumbs"><!-- <a href="#">Локальный репозиторий</a><span> / </span><a href="#">Тест</a>--></div>
                                </xsl:if>
                            </div>
                        </xsl:if>
                        <div class="gridHeadContainer">
                        <table class="gridTable" cellspacing="0">
                            <xsl:choose>
                                <xsl:when test="ancestor::component[@sample='FileRepository']">
                                    <xsl:attribute name="class">gridTable fixed_columns</xsl:attribute>
                                    <col id="col_11" style="width:12%"/>
                                    <col id="col_12" style="width:30%"/>
                                    <col id="col_13" style="width:28%"/>
                                    <col id="col_14" style="width:30%"/>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:for-each select="$FIELDS[@type!='hidden']">
                                        <xsl:choose>
                                            <xsl:when test="@language">
                                                <xsl:if test="@language = $LANG_ID">
                                                    <col id="col_1{position()}"/>
                                                </xsl:if>
                                            </xsl:when>
                                            <xsl:otherwise>
                                                <col id="col_1{position()}"/>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </xsl:for-each>
                                </xsl:otherwise>
                            </xsl:choose>
                            <thead>
                                <tr>
                                    <xsl:for-each select="$FIELDS[@type!='hidden']">
                                        <xsl:choose>
                                            <!--<xsl:when test="@index='PRI'"></xsl:when>-->
                                            <xsl:when test="@language">
                                                <xsl:if test="@language = $LANG_ID">
                                                    <th name="{@name}"><xsl:value-of select="@title"/></th>
                                                </xsl:if>
                                            </xsl:when>
                                            <xsl:otherwise>
                                                <th name="{@name}"><xsl:value-of select="@title"/></th>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </xsl:for-each>
                                </tr>
                            </thead>
                        </table>
                        </div>
                        <div class="gridContainer">
                            <div class="gridBodyContainer">
                                <table class="gridTable" cellspacing="0">
                                    <xsl:choose>
                                        <xsl:when test="ancestor::component[@sample='FileRepository']">
                                            <xsl:attribute name="class">gridTable fixed_columns</xsl:attribute>
                                            <col id="col_11a" style="width:12%"/>
                                            <col id="col_12a" style="width:30%"/>
                                            <col id="col_13a" style="width:28%"/>
                                            <col id="col_14a" style="width:30%"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:for-each select="$FIELDS[@type!='hidden']">
                                                <xsl:choose>
                                                    <xsl:when test="@language">
                                                        <xsl:if test="@language = $LANG_ID">
                                                            <col id="col_{position()}a"/>
                                                        </xsl:if>
                                                    </xsl:when>
                                                    <xsl:otherwise>
                                                        <col id="col_{position()}a"/>
                                                    </xsl:otherwise>
                                                </xsl:choose>
                                            </xsl:for-each>
                                            <!--  !!!TODO!!!
                                                чтобы выводить колонки фиксированной ширины, нужно выводить в инлайн-стиль ширину колонки style="width: 25%;"
                                            -->
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    <thead style="visibility: hidden;">
                                        <tr>
                                        <xsl:for-each select="$FIELDS[@type!='hidden']">
                                            <xsl:choose>
                                                <!--<xsl:when test="@index='PRI'"></xsl:when>-->
                                                <xsl:when test="@language">
                                                    <xsl:if test="@language = $LANG_ID">
                                                        <th id="col_{position()}"><xsl:value-of select="@title"/></th>
                                                    </xsl:if>
                                                </xsl:when>
                                                <xsl:otherwise>
                                                    <th id="col_{position()}"><xsl:value-of select="@title"/></th>
                                                </xsl:otherwise>
                                            </xsl:choose>
                                        </xsl:for-each>
                                        </tr>
                                    </thead>
                                    <tbody/>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--<xsl:if test="../toolbar">
            <div class="e-pane-b-toolbar"></div>
        </xsl:if>-->
        <div class="e-pane-b-toolbar"></div>
    </xsl:template>

    <xsl:template match="component[@type='list']/recordset/record/field">
        <span>
            <xsl:if test=". = ''">
                <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
            </xsl:if>
            <xsl:value-of select="." disable-output-escaping="yes"/>
        </span>
    </xsl:template>

<!--    <xsl:template match="component[@type='list']/recordset/record/field[@type='image']">
        <div style="width: 100px; height: 100px; overflow: auto;">
            <img src="{$BASE}/{.}" alt=""/>
        </div>
    </xsl:template>
-->
    <xsl:template match="component[@type='list']/recordset/record/field[@type='select']">
        <span>
            <xsl:if test=". = ''">
                <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
            </xsl:if>
            <xsl:value-of select="options/option[@selected='selected']"/>
        </span>
    </xsl:template>

    <xsl:template match="component[@type='list']/recordset/record/field[@type='boolean']">
        <input type="checkbox" disabled="disabled">
            <xsl:if test=". = 1">
                <xsl:attribute name="checked">checked</xsl:attribute>
            </xsl:if>
        </input>
    </xsl:template>

    <xsl:template match="component[@type='list']/recordset/record/field[@key='1']">
        <span><b><xsl:value-of select="."/></b> </span>
    </xsl:template>

    <xsl:template match="component[@type='list'][@exttype='print']">
        <style type="text/css">
            THEAD { display: table-header-group; }
        </style>
        <table border="1">
            <caption><xsl:value-of select="@title"/></caption>
            <thead>
                <tr>
                    <th>...</th>
                    <xsl:for-each select="recordset/record[1]/field[@type!='hidden'][@index != 'PRI' or not(@index)]">
                            <th><xsl:value-of select="@title"/></th>
                    </xsl:for-each>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="recordset/record">
                        <tr>
                            <td><xsl:number value="position()" format="1. "/></td>
                            <xsl:for-each select="field[@type!='hidden'][@index != 'PRI' or not(@index)]">
                                <td><xsl:choose>
                                    <xsl:when test="@type='select'">
                                        <xsl:value-of select="options/option[@selected]"/>
                                    </xsl:when>
                                    <xsl:when test="@type='image'">
                                        <img src="{.}" border="0"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="."/>
                                    </xsl:otherwise>
                                </xsl:choose></td>
                            </xsl:for-each>
                        </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
</xsl:stylesheet>