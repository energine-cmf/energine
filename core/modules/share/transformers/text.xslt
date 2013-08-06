<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">

    <!-- Компонент TextBlock -->
    <xsl:template match="component[@class='TextBlock']">
        <xsl:if test="@editable or recordset/record/field != ''">
            <div class="textblock">
                <xsl:apply-templates/>
            </div>
        </xsl:if>
    </xsl:template>

    <!-- Набор данных компонента -->
    <xsl:template match="component[@class='TextBlock']/recordset">
	    <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="component[@class='TextBlock']/recordset/record">
        <xsl:value-of select="." disable-output-escaping="yes"/>
    </xsl:template>

    <xsl:template match="component[@class='TextBlock' and @editable]/recordset/record">
        <div id="{generate-id(.)}" class="nrgnEditor" single_template="{$BASE}{$LANG_ABBR}{../../@single_template}" num="{../../@num}">
            <xsl:if test="not(../../@global)">
                <xsl:attribute name="eID"><xsl:value-of select="$ID"/></xsl:attribute>
            </xsl:if>            
            <xsl:if test=". = ''">
                <p><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></p>
            </xsl:if>
            <xsl:value-of select="." disable-output-escaping="yes"/>
        </div>
    </xsl:template>
    
    <!-- Компонент TextBlockSource (редактор html, выводится в модальном окне) -->
    <xsl:template match="document/translations[translation[@component=//component[@class='TextBlockSource']/@name]]">
            <script type="text/javascript">
                <xsl:for-each select="translation">
                    Energine.translations.set('<xsl:value-of select="@const"/>', '<xsl:value-of select="."/>');
                </xsl:for-each>
            </script>
    </xsl:template>
    
<!--    <xsl:template match="component[@class='TextBlockSource']">
        <link rel="stylesheet" href="scripts/codemirror/lib/codemirror.css" />
        <script type="text/javascript" src="scripts/codemirror/lib/codemirror.js"></script>
        <script type="text/javascript" src="scripts/codemirror/mode/xml/xml.js"></script>
        <script  type="text/javascript" src="scripts/codemirror/mode/javascript/javascript.js"></script>
        <script  type="text/javascript" src="scripts/codemirror/mode/css/css.js"></script>
        <link rel="stylesheet" href="scripts/codemirror/theme/default.css" />
        <script  type="text/javascript" src="scripts/codemirror/mode/htmlmixed/htmlmixed.js"></script>
        <link rel="stylesheet" href="scripts/codemirror/css/docs.css" />
        <form method="post" action="{@action}" class="e-grid-form">
            <input type="hidden" name="componentAction" value="{@componentAction}" id="componentAction"/>
            <xsl:apply-templates/>
        </form>    
    </xsl:template>-->
    
    <xsl:template match="component[@class='TextBlockSource']/recordset">
        <xsl:variable name="paneID"><xsl:value-of select="generate-id(record)" /></xsl:variable>
        <div id="{generate-id(.)}" class="e-pane e-pane-has-t-toolbar1" template="{$BASE}{$LANG_ABBR}{../@template}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
            <xsl:if test="../toolbar">
                <xsl:attribute name="class">e-pane e-pane-has-t-toolbar1 e-pane-has-b-toolbar1</xsl:attribute>
            </xsl:if>
            <div class="e-pane-t-toolbar">
                <ul class="e-pane-toolbar e-tabs">
                    <li>
                        <a href="#{$paneID}"><xsl:value-of select="$TRANSLATION[@const='TXT_TEXTBLOCK_SOURCE_EDITOR']"/></a>
                    </li>
                </ul>
            </div>
            <div class="e-pane-content">
                <div id="{$paneID}">
                    <xsl:apply-templates/>
                </div>
            </div>
            <xsl:if test="../toolbar">
                <div class="e-pane-b-toolbar"></div>
            </xsl:if>         
        </div>
    </xsl:template>

    <xsl:template match="component[@class='TextBlockSource']/toolbar">
        <script type="text/javascript">
	    window.addEvent('domready', function(){
            var toolbar_<xsl:value-of select="generate-id(../recordset)"/> = new Toolbar;
            <xsl:apply-templates/>
            <xsl:value-of select="generate-id(../recordset)"/>.attachToolbar(toolbar_<xsl:value-of select="generate-id(../recordset)"/>);
            toolbar_<xsl:value-of select="generate-id(../recordset)"/>.bindTo(<xsl:value-of select="generate-id(../recordset)"/>);
            });
        </script>
    </xsl:template>

    <xsl:template match="component[@class='TextBlockSource']/toolbar/control">
        var button = new Toolbar.Button({ id: '<xsl:value-of select="@id"/>', title: '<xsl:value-of select="@title"/>', action: '<xsl:value-of select="@onclick"/>' });
        toolbar_<xsl:value-of select="generate-id(../../recordset)"/>.appendControl(button);
    </xsl:template>
    
</xsl:stylesheet>