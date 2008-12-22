<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="component[@type='list']">
    	<form method="post" action="{@action}">
            <input type="hidden" name="componentAction" value="{@componentAction}" />
            <xsl:apply-templates />
    	</form>
    </xsl:template>

    <xsl:template match="component[@type='list']/recordset">
        <xsl:if test="not(@empty)">
            <ol><xsl:apply-templates /></ol>
        </xsl:if>
    </xsl:template>

    <xsl:template match="component[@type='list']/recordset/record">
        <li><xsl:apply-templates /></li>
    </xsl:template>

    <xsl:template match="component[@type='list' and @exttype='grid']/recordset">
        <div id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
            <xsl:call-template name="BUILD_GRID"/>
        </div>
    </xsl:template>

    <xsl:template name="BUILD_GRID">
        <xsl:variable name="FIRST_TAB_LANG" select="../tabs/tab[position()=1]/@id" />
        <ul class="tabs">
            <xsl:for-each select="../tabs/tab">
                <xsl:variable name="TAB_NAME" select="@name" />
                <xsl:variable name="TAB_LANG" select="@id" />
                <li>
                    <a href="#{generate-id(../.)}"><xsl:value-of select="$TAB_NAME" /></a>
                    <xsl:if test="$TAB_LANG">
                        <span class="data">{ lang: <xsl:value-of select="$TAB_LANG" /> }</span>
                    </xsl:if>
                </li>
            </xsl:for-each>
        </ul>
        <div class="paneContainer">
            <div id="{generate-id(../tabs)}">
                <div class="grid">
                    <xsl:if test="record/field[@type = 'string']">
                        <div class="filter">
                            <xsl:value-of select="../translations/translation[@const = 'TXT_FILTER']" />:<xsl:text>&#160;</xsl:text>
                            <select name="fieldName">
                                <xsl:for-each select="record/field[@type!='hidden']">
                                    <xsl:choose>
                                        <xsl:when test="@index = 'PRI'">
                                        </xsl:when>
                                        <xsl:when test="@language">
                                            <xsl:if test="@language = $FIRST_TAB_LANG and (@type = 'string' or @type = 'htmlblock')">
                                                <option value="[{@tableName}][{@name}]"><xsl:value-of select="@title"/></option>
                                            </xsl:if>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:if test="@type = 'string' or @type = 'htmlblock' or @type='email'">
                                                <option value="[{@tableName}][{@name}]"><xsl:value-of select="@title"/></option>
                                            </xsl:if>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:for-each>
                            </select>
                            <xsl:text>&#160;</xsl:text>
                            <input type="text" name="query" />
                            <xsl:text>&#160;</xsl:text>
                            <button type="button" onclick="{generate-id(.)}.applyFilter();"><xsl:value-of select="../translations/translation[@const = 'BTN_APPLY_FILTER']" /></button>
                            <xsl:text>&#160;</xsl:text>
                            <a href="javascript:{generate-id(.)}.removeFilter();"><xsl:value-of select="../translations/translation[@const = 'TXT_RESET_FILTER']" /></a>
                        </div>
                    </xsl:if>
                    <div class="gridHeadContainer">
                    <table class="gridTable" cellspacing="0">
                        <xsl:for-each select="record/field[@type!='hidden']">
                            <xsl:choose>
                                <xsl:when test="@index = 'PRI'" />
                                <xsl:when test="@language">
                                    <xsl:if test="@language = $FIRST_TAB_LANG">
                                        <col id="col_1{position()}" />
                                    </xsl:if>
                                </xsl:when>
                                <xsl:otherwise>
                                    <col id="col_1{position()}" />
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:for-each>
                        <thead>
                            <tr>
                                <xsl:for-each select="record/field[@type!='hidden']">
                                    <xsl:choose>
                                        <xsl:when test="@index = 'PRI'" />
                                        <xsl:when test="@language">
                                            <xsl:if test="@language = $FIRST_TAB_LANG">
                                                <th><xsl:value-of select="@title"/></th>
                                            </xsl:if>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <th><xsl:value-of select="@title"/></th>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:for-each>
                            </tr>
                        </thead>
                    </table>
                    </div>
                    <div class="gridContainer">
                        <table class="gridTable" cellspacing="0">
                        <xsl:for-each select="record/field[@type!='hidden']">
                            <xsl:choose>
                                <xsl:when test="@index = 'PRI'" />
                                <xsl:when test="@language">
                                    <xsl:if test="@language = $FIRST_TAB_LANG">
                                        <col id="col_{position()}a" />
                                    </xsl:if>
                                </xsl:when>
                                <xsl:otherwise>
                                    <col id="col_{position()}a" />
                                </xsl:otherwise>
                            </xsl:choose>
                         </xsl:for-each>
                            <thead style="visibility: hidden;">
                                <tr>
                                <xsl:for-each select="record/field[@type!='hidden']">
                                    <xsl:choose>
                                        <xsl:when test="@index = 'PRI'" />
                                        <xsl:when test="@language">
                                            <xsl:if test="@language = $FIRST_TAB_LANG">
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
    </xsl:template>

    <xsl:template match="component[@type='list']/recordset/record/field">
                <span>
                    <xsl:if test=". = ''">
                        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                    </xsl:if>
                    <xsl:value-of select="." disable-output-escaping="yes" />
                </span>
    </xsl:template>

    <xsl:template match="component[@type='list']/recordset/record/field[@type='image']">
                <div style="width: 100px; height: 100px; overflow: auto;">
                    <img src="{$BASE}/{.}" alt="" />
                </div>
    </xsl:template>

    <xsl:template match="component[@type='list']/recordset/record/field[@type='select']">
            	<span>
            	    <xsl:if test=". = ''">
            	        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
            	    </xsl:if>
            	    <xsl:value-of select="options/option[@selected='selected']" />
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

    <xsl:template match="component[@type='list']/javascript">
    	<script type="text/javascript">
    		<xsl:apply-templates />
    	</script>
    </xsl:template>

    <xsl:template match="component/translations">
    	<script type="text/javascript">
            <xsl:apply-templates />
    	</script>
    </xsl:template>

    <xsl:template match="translation">
    	var <xsl:value-of select="@const" /> = '<xsl:value-of select="." />';
    </xsl:template>

    <xsl:template match="component[@class!='PageToolBar']/javascript/object">
    	var <xsl:value-of select="generate-id(../../recordset)" />;
    </xsl:template>


    <xsl:template match="component[@type='list'][@exttype='print']">
    <STYLE type="text/css">

THEAD { display: table-header-group; }

</STYLE>
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