<?xml version='1.0' encoding="UTF-8" ?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 

    xmlns:set="http://exslt.org/sets"
    extension-element-prefixes="set">

    <xsl:template match="document/translations[translation[@component=//component[@sample='DivisionEditor' or @class='SiteEditor']/@name]]">
            <script type="text/javascript">
                <xsl:for-each select="translation[@component=$COMPONENTS[@sample='DivisionEditor' or @class='SiteEditor']/@name]">
                    Energine.translations.set('<xsl:value-of select="@const"/>', '<xsl:value-of select="." disable-output-escaping="yes"/>');
                </xsl:for-each>
            </script>
    </xsl:template>
    
    <!-- вывод дерева разделов -->
    <xsl:template match="recordset[parent::component[javascript/behavior/@name='DivManager' or javascript/behavior/@name='DivSelector'or javascript/behavior/@name='DivTree'][@sample='DivisionEditor'][@type='list']]">
        <xsl:variable name="TAB_ID" select="generate-id(record[1])"/>
        <div id="{generate-id(.)}" class="e-pane e-pane-has-t-toolbar1" template="{$BASE}{$LANG_ABBR}{../@template}" lang_id="{$LANG_ID}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}" site="{../@site}">
            <xsl:if test="../toolbar">
                <xsl:attribute name="class">e-pane e-pane-has-t-toolbar1 e-pane-has-b-toolbar1</xsl:attribute>
            </xsl:if>
            <div class="e-pane-t-toolbar">
                <ul class="e-pane-toolbar e-tabs">
                    <li>
                        <a href="#{$TAB_ID}"><xsl:value-of select="record[1]/field[1]/@tabName" /></a>
                        <!--<span class="data">{ lang: <xsl:value-of select="$LANG_ID" /> }</span>-->
                    </li>
                </ul>
            </div>
            <div class="e-pane-content">
                <div id="{$TAB_ID}">                    
                    <div id="treeContainer" class="e-divtree-select">
                        <xsl:apply-templates select="$COMPONENTS[@class='SiteList']" mode="insideEditor"/>
                    </div>
                </div>
            </div>
            <xsl:if test="../toolbar">
                <div class="e-pane-b-toolbar"></div>
            </xsl:if>
        </div>
    </xsl:template>
        
    <!-- вывод дерева разделов в боковом тулбаре -->
    <xsl:template match="recordset[parent::component[javascript/behavior/@name='DivSidebar'][@sample='DivisionEditor'][@componentAction='main'][@type='list']]">
        <div id="{generate-id(.)}" class="e-divtree-wrapper" template="{$BASE}{$LANG_ABBR}{../@template}"  lang_id="{$LANG_ID}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}" site="{../@site}">
            <div id="treeContainer" class="e-divtree-main"></div>
        </div>
    </xsl:template>
    
    <xsl:template match="field[@name='page_rights'][@type='custom']">
        <xsl:variable name="RECORDS" select="recordset/record"/>
        <div class="table_data">
            <table width="100%" border="0">
                <thead>
                    <tr>
                        <td><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
                        <xsl:for-each select="$RECORDS[position()=1]/field[@name='right_id']/options/option">
                            <td><xsl:value-of select="."/></td>
                        </xsl:for-each>
                    </tr>
                </thead>
                <tbody>
                    <xsl:for-each select="$RECORDS">
                        <tr>
    						<xsl:if test="floor(position() div 2) = position() div 2">
                                <xsl:attribute name="class">even</xsl:attribute>
                            </xsl:if>
                            <td class="group_name"><xsl:value-of select="field[@name='group_id']"/></td>
                            <xsl:for-each select="field[@name='right_id']/options/option">
                                <td>
                                    <input type="radio" style="border:none; width:auto;" value="{@id}">
                                        <xsl:attribute name="name">right_id[<xsl:value-of select="../../../field[@name='group_id']/@group_id"/>]</xsl:attribute>
                                        <xsl:if test="@selected">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </input>
                                </td>
                            </xsl:for-each>
                        </tr>
                    </xsl:for-each>
                </tbody>
            </table>
        </div>
    </xsl:template>    
    
    <!-- поле выбора родительского раздела -->
    <xsl:template match="field[@name='smap_pid'][@mode='2'][ancestor::component[@sample='DivisionEditor'][@type='form']]">
    	<div class="field">
            <xsl:if test="not(@nullable)">
                <xsl:attribute name="class">field required</xsl:attribute>
            </xsl:if>
    		<xsl:if test="@title">
    		    <div class="name">
        			<label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes"/>:</label>
    			</div>
    		</xsl:if>
    		<div class="control">
                <xsl:variable name="FIELD_ID"><xsl:value-of select="generate-id()"/></xsl:variable>
                <div class="with_append">
                    <span class="read" id="s_{$FIELD_ID}" style="margin-right: 5px;"><xsl:value-of select="@data_name" disable-output-escaping="yes" /></span>
                    <input type="hidden" id="h_{$FIELD_ID}" value="{.}">
                        <xsl:attribute name="name"><xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose></xsl:attribute>
                    </input>
                    <div class="appended_block">
                        <button type="button" id="sitemap_selector" hidden_field="h_{$FIELD_ID}" span_field="s_{$FIELD_ID}">...</button>
                    </div>
                </div>
            </div>
    	</div>
    </xsl:template>
   
    <xsl:template match="field[@name='smap_pid'][@mode='1'][@type!='hidden'][ancestor::component[@sample='DivisionEditor'][@type='form']]">
        <div class="field">
            <xsl:if test="@title">
                <div class="name">
                    <label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes"/>:</label>
                </div>
            </xsl:if>
            <span class="read"><xsl:value-of select="@data_name" disable-output-escaping="yes" /></span>
            <input type="hidden" value="{.}">
                <xsl:attribute name="name">
                    <xsl:choose>
                        <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name" />]</xsl:when>
                        <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
            </input>
        </div>
    </xsl:template>
    
    <!-- поле для ввода сегмента раздела -->
    <xsl:template match="field[@name='smap_segment'][ancestor::component[@sample='DivisionEditor' and @type='form']]" mode="field_input">
        <div class="smap_segment">
            <span><xsl:value-of select="../field[@name='smap_pid']/@base"/><xsl:value-of select="$LANG_ABBR"/></span><span id="smap_pid_segment"><xsl:value-of select="../field[@name='smap_pid']/@segment"/></span>
            <xsl:choose>
                <xsl:when test="@mode='2'">
                    <input style="width: 130px; height: 32px; padding: 0;">
                        <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
                    </input>
                </xsl:when>
                <xsl:otherwise>
                    <span class="read current_segment"><xsl:value-of select="." disable-output-escaping="yes"/></span>
                    <input type="hidden" value="{.}">
                        <xsl:attribute name="name"><xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name" />]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose></xsl:attribute>
                    </input>
                </xsl:otherwise>
            </xsl:choose>/
        </div>
    </xsl:template>

    <!-- поле выбора контентного шаблона раздела -->
    <xsl:template match="field[@name='smap_content'][ancestor::component[@sample='DivisionEditor' and @type='form']]" mode="field_input">
        <div>
            <xsl:if test="@reset"><xsl:attribute name="class">with_append with_link</xsl:attribute></xsl:if>
            <select id="{@name}">
                <xsl:attribute name="name"><xsl:choose>
                    <xsl:when test="@tableName"><xsl:value-of select="@tableName"/>[<xsl:value-of select="@name"/>]</xsl:when>
                    <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                </xsl:choose></xsl:attribute>
                <xsl:if test="@nullable='1'">
                    <option></option>
                </xsl:if>
                <xsl:apply-templates mode="field_input"/>
            </select>
            <xsl:if test="@reset">
                <div class="appended_block">
                    <button type="button" onclick="{generate-id(../..)}.resetPageContentTemplate();">
                        <xsl:value-of select="@reset"/>
                    </button>
                </div>
            </xsl:if>
        </div>
    </xsl:template>


    <!--<xsl:template match="field[@name='smap_content_xml'][ancestor::component[@type='form' and @exttype='grid']]">
        <link rel="stylesheet" href="scripts/codemirror/lib/codemirror.css" />
        <script type="text/javascript" src="scripts/codemirror/lib/codemirror.js"></script>
        <script type="text/javascript" src="scripts/codemirror/mode/xml/xml.js"></script>
        <link rel="stylesheet" href="scripts/codemirror/theme/default.css" />

        <div>
            <xsl:attribute name="class">field clearfix<xsl:choose>
                <xsl:when test=".=''"> min</xsl:when>
                <xsl:otherwise> max</xsl:otherwise>
            </xsl:choose></xsl:attribute>
            <xsl:apply-templates select="." mode="field_name"/>
            <xsl:apply-templates select="." mode="field_content"/>
        </div>
    </xsl:template>-->

    <xsl:template match="record[parent::recordset[parent::component[@sample='DivisionEditor'][@type='list']]]"/>
    <!-- /компонент DivisionEditor -->

    <!--Обычный список сайтов-->
    <xsl:template match="component[@class='SiteList']">
        <xsl:if test="not(recordset[@empty])">
            <div class="site_list_box">
                <xsl:apply-templates/>
            </div>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@class='SiteList']]">
        <ul class="site_list">
            <xsl:apply-templates/>
        </ul>
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@class='SiteList']]">
        <li>
            <xsl:if test="field[@name='site_id'] = $COMPONENTS[@sample='DivisionEditor']/@site">
                <xsl:attribute name="class">active</xsl:attribute>
            </xsl:if>
            <a href="{$BASE}{$LANG_ABBR}{../../@template}show/{field[@name='site_id']}/"><xsl:value-of select="field[@name='site_name']"/></a>
        </li>
    </xsl:template>

    <xsl:template match="component[@class='SiteList' and (following::component[@sample='DivisionEditor'] or preceding::component[@sample='DivisionEditor'])]" />

    <xsl:template match="component[@class='SiteList' and (following::component[@sample='DivisionEditor'] or preceding::component[@sample='DivisionEditor'])]"  mode="insideEditor">
        <select onchange="document.location = '{$BASE}{$LANG_ABBR}{@template}show/' + this.options[this.selectedIndex].value + '/';" id="site_selector">
            <xsl:for-each select="recordset/record">
                <option value="{field[@name='site_id']}">
                    <xsl:if test="field[@name='site_id'] = $COMPONENTS[@sample='DivisionEditor']/@site">
                        <xsl:attribute name="selected">selected</xsl:attribute>
                    </xsl:if>
                    <xsl:value-of select="field[@name='site_name']"/></option>
            </xsl:for-each>
        </select>
    </xsl:template>
</xsl:stylesheet>