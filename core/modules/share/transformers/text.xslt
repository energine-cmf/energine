<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

    <!-- Компонент в нормальном режиме -->
    <xsl:template match="component[@class='TextBlock']">
        <xsl:apply-templates />
    </xsl:template>

    <!-- Компонент в режиме редактирования -->
    <xsl:template match="component[@class='TextBlock' and @editable]">
	    <xsl:apply-templates />
    </xsl:template>

    <!-- Набор данных компонента -->
    <xsl:template match="component[@class='TextBlock']/recordset">
	    <xsl:apply-templates />
    </xsl:template>

    <xsl:template match="component[@class='TextBlock']/recordset/record">
        <xsl:value-of select="." disable-output-escaping="yes" />
    </xsl:template>

    <!-- Выводим переводы для WYSIWYG -->
    <xsl:template match="document/translations[translation[@component=//component[@class='TextBlock' and @editable]/@name]]">
            <script type="text/javascript">
                <xsl:for-each select="translation[@component=$COMPONENTS[@class='TextBlock' and @editable]/@name]">
                    var <xsl:value-of select="@const"/>='<xsl:value-of select="."/>';
                </xsl:for-each>
            </script>
    </xsl:template>

    <xsl:template match="component[@class='TextBlock' and @editable]/recordset/record">
        <div id="{generate-id(.)}" class="nrgnEditor" componentPath="{$BASE}{$LANG_ABBR}{../../@single_template}" componentName="{../../@name}" num="{../../@num}">
            <xsl:if test="ancestor::content">
                <xsl:attribute name="docID"><xsl:value-of select="$ID" /></xsl:attribute>
            </xsl:if>
            <xsl:if test=". = ''">
                <p><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></p>
            </xsl:if>
            <xsl:value-of select="." disable-output-escaping="yes" />
        </div>
    </xsl:template>

    <xsl:template match="component[@type='form' and @class='TextBlockSource']/recordset">
        <div class="formContainer">
            <xsl:variable name="paneID"><xsl:value-of select="generate-id(record)" /></xsl:variable>
            <div id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
                <ul class="tabs"><li><a href="#{$paneID}"><xsl:value-of select="$TRANSLATION[@const='FIELD_TEXTBLOCK_SOURCE']"/></a></li></ul>
                <div class="paneContainer">
                    <div id="{$paneID}">
                        <xsl:apply-templates />
                    </div>
                </div>
            </div>
        </div>
    </xsl:template>

    <xsl:template match="component[@class='TextBlockSource']/toolbar">
        <script type="text/javascript">
	    window.addEvent('domready', function(){
            var toolbar_<xsl:value-of select="generate-id(../recordset)" /> = new Toolbar;
            <xsl:apply-templates />
            <xsl:value-of select="generate-id(../recordset)"/>.attachToolbar(toolbar_<xsl:value-of select="generate-id(../recordset)"/>);
            toolbar_<xsl:value-of select="generate-id(../recordset)"/>.bindTo(<xsl:value-of select="generate-id(../recordset)"/>);
            });
        </script>
    </xsl:template>

    <xsl:template match="component[@class='TextBlockSource']/toolbar/control">
        var button = new Toolbar.Button({ id: '<xsl:value-of select="@id" />', title: '<xsl:value-of select="@title" />', action: '<xsl:value-of select="@onclick" />' });
        toolbar_<xsl:value-of select="generate-id(../../recordset)" />.appendControl(button);
    </xsl:template>
</xsl:stylesheet>