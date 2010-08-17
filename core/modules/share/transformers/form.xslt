<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:set="http://exslt.org/sets"
    extension-element-prefixes="set">
    
    <!-- обработка компонента типа form -->
    <xsl:template match="component[@type='form']">
        <form method="post" action="{@action}">
            <xsl:if test="descendant::field[@type='image'] or descendant::field[@type='file'] or descendant::field[@type='pfile'] or descendant::field[@type='prfile']">
            	<xsl:attribute name="enctype">multipart/form-data</xsl:attribute>
            </xsl:if>
			<xsl:choose>
                <xsl:when test="@class='RestorePassword'"><xsl:attribute name="class">base_form restore_password_form</xsl:attribute></xsl:when>
                <xsl:when test="@class='Register'"><xsl:attribute name="class">base_form registration_form</xsl:attribute></xsl:when>
				<xsl:when test="@class='UserProfile'"><xsl:attribute name="class">base_form profile_form</xsl:attribute></xsl:when>
			</xsl:choose>
            <input type="hidden" name="componentAction" value="{@componentAction}" id="componentAction"/>
    		<xsl:apply-templates/>
        </form>
    </xsl:template>
    
    <xsl:template match="component[@type='form' and @exttype='grid']">
        <form method="post" action="{@action}" class="e-grid-form">
            <xsl:if test="descendant::field[@type='image'] or descendant::field[@type='file'] or descendant::field[@type='pfile'] or descendant::field[@type='prfile'] or descendant::field[@name='attached_files']">
                <script type="text/javascript" src="scripts/Swiff.Uploader.js"></script>
                <script type="text/javascript">
                    window.addEvent('beforeunload', function(){
                    window.__flash__removeCallback = 
                       (function() {
                          return function(instance, name) {
                             if (instance) instance[name] = null;
                          }; // IE 6 dangling semicolon needed
                    })(); // force another closure to prevent IE memory leaks  
                    });
                </script>
            </xsl:if>
<!--            <xsl:if test="descendant::field[@type='textbox']">
                <script type="text/javascript" src="scripts/TextboxList.js"></script>
            </xsl:if>-->

            <input type="hidden" name="componentAction" value="{@componentAction}" id="componentAction"/>
            <xsl:apply-templates/>
        </form>
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@type='form']]">
    	<div id="{generate-id(.)}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}" template="{$BASE}{$LANG_ABBR}{../@template}">
    		<xsl:apply-templates/>
    	</div>
		<xsl:if test="$TRANSLATION[@const='TXT_REQUIRED_FIELDS']">
			<div class="note">
				<xsl:value-of select="$TRANSLATION[@const='TXT_REQUIRED_FIELDS']" disable-output-escaping="yes"/>
			</div>
		</xsl:if>
    </xsl:template>    

    <xsl:template match="record[ancestor::component[@type='form']]">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="javascript[parent::component[@type='form']]">
    	<script type="text/javascript">
    		<xsl:apply-templates/>
    	</script>
    </xsl:template>
    
    <xsl:template match="toolbar[parent::component[@type='form']]">
        <div class="controlset">
            <xsl:apply-templates/>
        </div>
    </xsl:template>
    
    <!-- форма как часть grid-а выводится в другом стиле -->
    <xsl:template match="recordset[parent::component[@type='form' and @exttype='grid']]">
        <xsl:variable name="FIELDS" select="record/field"/>
        <div id="{generate-id(.)}" class="e-pane e-pane-has-t-toolbar1" template="{$BASE}{$LANG_ABBR}{../@template}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
            <xsl:if test="../toolbar">
                <xsl:attribute name="class">e-pane e-pane-has-t-toolbar1 e-pane-has-b-toolbar1</xsl:attribute>
            </xsl:if>
            <div class="e-pane-t-toolbar">
                <ul class="e-pane-toolbar e-tabs">
                    <xsl:for-each select="set:distinct($FIELDS/@tabName)">
                        <xsl:variable name="TAB_NAME" select="."></xsl:variable>
                        <xsl:if test="count(set:distinct($FIELDS[not(@index='PRI') and not(@type='hidden')][@tabName=$TAB_NAME]))&gt;0">
                            <li>
                                <a href="#{generate-id(.)}"><xsl:value-of select="$TAB_NAME" /></a>
                                <xsl:if test="$FIELDS[@tabName=$TAB_NAME][1]/@language">
                                    <span class="data">{ lang: <xsl:value-of select="$FIELDS[@tabName=$TAB_NAME][1]/@language" /> }</span>                                
                                </xsl:if>
                            </li>
                        </xsl:if>
                    </xsl:for-each>
                </ul>
            </div>            
            <div class="e-pane-content">
                <xsl:for-each select="set:distinct($FIELDS/@tabName)">
                    <xsl:variable name="TAB_NAME" select="."/>
                    <div id="{generate-id(.)}">
                        <xsl:apply-templates select="$FIELDS[@tabName=$TAB_NAME]"/>
                    </div>
                </xsl:for-each>
            </div>
            <xsl:if test="../toolbar">
                <div class="e-pane-b-toolbar"></div>
            </xsl:if>
        </div>        
    </xsl:template>

    <xsl:template match="field[@name='attached_files'][@type='custom']">
        <!--<xsl:variable name="JS_OBJECT" select="generate-id(../..)"></xsl:variable>-->
        <div class="table_data">
            <table width="100%" id="attached_files">
                <thead>
                <tr>
                    <xsl:for-each select="recordset/record[position()=1]/field/@title">
                        <td>
                            <xsl:choose>
                                <xsl:when test="position() != 1">
                                    <xsl:value-of select="."/>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="$NBSP" disable-output-escaping="yes"/>
                                </xsl:otherwise>
                            </xsl:choose>
                            </td>
                    </xsl:for-each>
                 </tr>
                </thead>
                <tbody>
                    <xsl:choose>
                    <xsl:when test="not(recordset/@empty)">
                        <xsl:for-each select="recordset/record">
                            <tr id="row_{field[@name='upl_id']}">
                                <xsl:if test="floor((position()- 1) div 2) = (position() -1) div 2">
                                    <xsl:attribute name="class">even</xsl:attribute>
                                </xsl:if>
                                <td>
                                    <input type="hidden" name="uploads[upl_id][]" value="{field[@name='upl_id']}"/>
                                    <button type="button" class="delete_attachment" upl_id="{field[@name='upl_id']}"><xsl:value-of select="$TRANSLATION[@const='BTN_DEL_FILE']"/></button> 
                                    <button type="button" class="up_attachment" upl_id="{field[@name='upl_id']}"><xsl:value-of select="$TRANSLATION[@const='BTN_UP']"/></button><!--</xsl:if>-->
                                    <button type="button" class="down_attachment" upl_id="{field[@name='upl_id']}"><xsl:value-of select="$TRANSLATION[@const='BTN_DOWN']"/></button><!--</xsl:if>-->
                                    </td>
                                    
                                <td><xsl:value-of select="field[@name='upl_name']"/></td>
                                <td>
                                    <a href="{field[@name='upl_path']/@real_image}" target="blank">
                                        <xsl:choose>
                                            <xsl:when test="field[@name='upl_path']/@is_image">
                                                <img src="{field[@name='upl_path']}" border="0"/>
                                            </xsl:when>
                                            <xsl:otherwise>
                                                <xsl:value-of select="field[@name='upl_path']"/>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </a>
                                </td>
                            </tr>
                        </xsl:for-each>
                    </xsl:when>
                    <xsl:otherwise>
                        <tr id="empty_row">
                            <td colspan="4">
                                <xsl:value-of select="$TRANSLATION[@const='MSG_NO_ATTACHED_FILES']"/>
                            </td>
                        </tr>
                    </xsl:otherwise>
                    </xsl:choose>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:right;">
                            <a href="#" id="add_attachment"><xsl:value-of select="$TRANSLATION[@const='BTN_LOAD_FILE']"/></a>
                            <img src="images/loading.gif" alt="" width="32" height="32" class="hidden" id="loader"/>
                            <span class="progress_indicator hidden" id="indicator">0%</span>                            
                        </td>
                        <td style="text-align:right;">
                            <a href="#" id="insert_attachment"><xsl:value-of select="$TRANSLATION[@const='BTN_ADD_FILE']"/></a>
                            <script type="text/javascript">
                                Energine.translations.extend({
                                BTN_DEL_FILE: '<xsl:value-of select="$TRANSLATION[@const='BTN_DEL_FILE']"/>',
                                BTN_UP: '<xsl:value-of select="$TRANSLATION[@const='BTN_UP']"/>',
                                BTN_DOWN: '<xsl:value-of select="$TRANSLATION[@const='BTN_DOWN']"/>',
                                MSG_NO_ATTACHED_FILES: '<xsl:value-of select="$TRANSLATION[@const='MSG_NO_ATTACHED_FILES']"/>'});
                            </script>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </xsl:template>
    
    <!-- обработка сообщения об отправке данных формы -->
    <xsl:template match="component[@type='form'][@componentAction='send']">
        <div class="result_message">
            <xsl:value-of select="recordset/record/field" disable-output-escaping="yes"/>
        </div>
    </xsl:template>
</xsl:stylesheet>