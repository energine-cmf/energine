<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 

    xmlns:set="http://exslt.org/sets"
    extension-element-prefixes="set">
    
    <!-- обработка компонента типа form -->
    <!--or descendant::field[@type='pfile']
     or descendant::field[@type='prfile']-->
    <xsl:template match="component[@type='form']">
        <form method="post" action="{@action}">
            <xsl:if test="descendant::field[@type='image'] or descendant::field[@type='file']">
            	<xsl:attribute name="enctype">multipart/form-data</xsl:attribute>
            </xsl:if>
			<xsl:choose>
                <xsl:when test="@class='RestorePassword'"><xsl:attribute name="class">base_form restore_password_form</xsl:attribute></xsl:when>
                <xsl:when test="@class='Register'"><xsl:attribute name="class">base_form registration_form</xsl:attribute></xsl:when>
				<xsl:when test="@class='UserProfile'"><xsl:attribute name="class">base_form profile_form</xsl:attribute></xsl:when>
                <xsl:when test="@class='FeedbackForm'"><xsl:attribute name="class">base_form feedback_form</xsl:attribute></xsl:when>
                <xsl:when test="@class='Form'"><xsl:attribute name="class">base_form forms_form</xsl:attribute></xsl:when>
			</xsl:choose>
            <input type="hidden" name="componentAction" value="{@componentAction}" id="componentAction"/>
    		<xsl:apply-templates/>
        </form>
    </xsl:template>
    
    <xsl:template match="component[@type='form' and @exttype='grid']">
        <!--Если есть поля типа code  - добавляем вызовы js и css-->
        <xsl:if test="recordset/record/field[@type='code']">
            <link rel="stylesheet" href="scripts/codemirror/lib/codemirror.css" />
            <script type="text/javascript" src="scripts/codemirror/lib/codemirror.js"></script>
            <script type="text/javascript" src="scripts/codemirror/mode/xml/xml.js"></script>
            <script  type="text/javascript" src="scripts/codemirror/mode/javascript/javascript.js"></script>
            <script  type="text/javascript" src="scripts/codemirror/mode/css/css.js"></script>
            <link rel="stylesheet" href="scripts/codemirror/theme/default.css" />
            <script  type="text/javascript" src="scripts/codemirror/mode/htmlmixed/htmlmixed.js"></script>
            <link rel="stylesheet" href="scripts/codemirror/css/docs.css" />
        </xsl:if>

        <form method="post" action="{@action}" class="e-grid-form">
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
                    <xsl:apply-templates select="$FIELDS[@type='tab']" mode="field_name"/>
                </ul>
            </div>            
            <div class="e-pane-content">
                <xsl:for-each select="set:distinct($FIELDS/@tabName)">
                    <xsl:variable name="TAB_NAME" select="."/>
                    <div id="{generate-id(.)}">
                        <xsl:apply-templates select="$FIELDS[@tabName=$TAB_NAME]"/>
                    </div>
                </xsl:for-each>
                <xsl:apply-templates select="$FIELDS[@type='tab']" mode="field_content"/>
            </div>
            <xsl:if test="../toolbar">
                <div class="e-pane-b-toolbar"></div>
            </xsl:if>
        </div>        
    </xsl:template>
    <!-- обработка сообщения об отправке данных формы -->
    <xsl:template match="component[@type='form'][@componentAction='send']" mode="custom">
        <xsl:choose>
            <xsl:when test="recordset/record/field[@name='error_message']!=''">
                <xsl:apply-templates select="."/>
            </xsl:when>
            <xsl:otherwise>
                <div class="result_message">
                    <xsl:value-of select="recordset/record/field" disable-output-escaping="yes"/>
                </div>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>