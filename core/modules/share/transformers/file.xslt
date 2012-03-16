<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <!-- компонент ImageManager (редактор изображения при вставке в текстовый блок, выводится в модальное окно) -->
    <xsl:template match="component[@class='ImageManager']">
        <form method="post" action="{@action}" class="e-grid-form">
            <xsl:if test="descendant::field[@type='image'] or descendant::field[@type='file'] or descendant::field[@type='pfile'] or descendant::field[@type='prfile']">
                <xsl:attribute name="enctype">multipart/form-data</xsl:attribute>
            </xsl:if>
            <input type="hidden" name="componentAction" value="{@componentAction}" id="componentAction"/>
            <xsl:apply-templates/>
        </form>
    </xsl:template>
    
    <xsl:template match="recordset[parent::component[@class='ImageManager']]">
        <xsl:variable name="IDD"><xsl:value-of select="generate-id(record)"/></xsl:variable>
        <div id="{generate-id(.)}" class="e-pane e-pane-has-t-toolbar1" template="{$BASE}{$LANG_ABBR}{../@template}"  single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
            <xsl:if test="../toolbar">
                <xsl:attribute name="class">e-pane e-pane-has-t-toolbar1 e-pane-has-b-toolbar1</xsl:attribute>
            </xsl:if>
            <div class="e-pane-t-toolbar">
                <ul class="e-pane-toolbar e-tabs">
                    <li>
                        <a href="#{$IDD}"><xsl:value-of select="$TRANSLATION[@const='TXT_IMG_MANAGER']"/></a>
                    </li>
                </ul>
            </div>
            <div class="e-pane-content">
                <div id="{$IDD}">
                    <div style="max-height:300px; max-width:650px; overflow:auto;border: thin inset; width: auto;">
                        <img id="thumbnail" alt=""  style="display: block;"/>
                    </div>
                    <!--
                    <div style="padding-top:20px;">
                        <input type="checkbox" id="insThumbnail" name="insThumbnail" value="1" style="width: auto;" disabled="disabled"/><label for="insThumbnail">вставить&#160;превью</label>
                    </div>
                    -->
                    <xsl:apply-templates/>
                </div>
            </div>
            <xsl:if test="../toolbar">
                <div class="e-pane-b-toolbar"></div>
            </xsl:if>            
        </div>
    </xsl:template>
    
    <xsl:template match="toolbar[parent::component[@class='ImageManager']]">
        <script type="text/javascript">
            window.addEvent('domready', function(){
                componentToolbars['<xsl:value-of select="generate-id(../recordset)"/>'] = new Toolbar('<xsl:value-of select="@name"/>');
                <xsl:apply-templates/>
                if(<xsl:value-of select="generate-id(../recordset)"/>)<xsl:value-of select="generate-id(../recordset)"/>.attachToolbar(componentToolbars['<xsl:value-of select="generate-id(../recordset)"/>']);
            });
        </script>
        <!--
        <script language="JavaScript">
            var toolbar_<xsl:value-of select="generate-id(../recordset)"/>;
            toolbar_<xsl:value-of select="generate-id(../recordset)"/> = new Toolbar;
            <xsl:apply-templates/>
        </script> 
        -->
    </xsl:template>
    
    <xsl:template match="control[ancestor::component[@class='ImageManager']]">
        button = new Toolbar.Button({ id: '<xsl:value-of select="@id"/>', title: '<xsl:value-of select="@title"/>', action: '<xsl:value-of select="@onclick"/>' });
        componentToolbars['<xsl:value-of select="generate-id(../../recordset)"/>'].appendControl(button);
    </xsl:template>
    <!-- /компонент ImageManager -->

 <!-- компонент FileLibrary - файловый репозиторий -->
    <xsl:template match="recordset[parent::component[@class='FileLibrary'][@type='list']]">
        <xsl:variable name="TAB_ID" select="generate-id(record[1])"/>
        <div id="{generate-id(.)}" class="e-pane e-pane-has-t-toolbar1" template="{$BASE}{$LANG_ABBR}{../@template}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}" file_type="{../@allowed_file_type}">
            <xsl:if test="../toolbar">
                <xsl:attribute name="class">e-pane e-pane-has-t-toolbar1 e-pane-has-b-toolbar1</xsl:attribute>
            </xsl:if>
            <div class="e-pane-t-toolbar">
                <ul class="e-pane-toolbar e-tabs">
                    <li>
                        <a href="#{$TAB_ID}"><xsl:value-of select="record[1]/field[1]/@tabName"/></a>
                    </li>
                </ul>
            </div>
            <div class="e-pane-content">
                <div id="{$TAB_ID}">
                    <div class="e-filemanager"></div>
                </div>
            </div>
            <xsl:if test="../toolbar">
                <div class="e-pane-b-toolbar"></div>
            </xsl:if>
        </div>                
    </xsl:template>
    
</xsl:stylesheet>
