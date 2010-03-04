<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <!-- компонент ImageManager -->
    <xsl:template match="recordset[parent::component[@class='ImageManager']]">
        <div class="formContainer">
            <xsl:variable name="IDD"><xsl:value-of select="generate-id(record)"/></xsl:variable>
            <div id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}"  single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
                <ul class="tabs">
                    <li>
                        <a href="#{$IDD}"><xsl:value-of select="../@title"/></a>
                    </li>
                </ul>
                <div class="paneContainer">
                    <div id="{$IDD}">
                        <div style="padding: 1em;">
                            <div>
                                <img id="thumbnail" width="50" height="50" alt=""  style="border: thin inset; width: auto; display: block;"/>
                            </div>
                            <!--
                            <div style="padding-top:20px;">
                                <input type="checkbox" id="insThumbnail" name="insThumbnail" value="1" style="width: auto;" disabled="disabled"/><label for="insThumbnail">вставить&#160;превью</label>
                            </div>
                            -->
                        </div>
                        <xsl:apply-templates/>
                    </div>
                </div>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="toolbar[parent::component[@class='ImageManager']]">
        <script type="text/javascript">
         var toolbar_<xsl:value-of select="generate-id(../recordset)"/>;
            window.addEvent('domready', function(){
                toolbar_<xsl:value-of select="generate-id(../recordset)"/> = new Toolbar('<xsl:value-of select="@name"/>');
                <xsl:apply-templates/>
                <xsl:value-of select="generate-id(../recordset)"/>.attachToolbar(toolbar_<xsl:value-of select="generate-id(../recordset)"/>);
                toolbar_<xsl:value-of select="generate-id(../recordset)"/>.bindTo(<xsl:value-of select="generate-id(../recordset)"/>);
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
        toolbar_<xsl:value-of select="generate-id(../../recordset)"/>.appendControl(button);
    </xsl:template>
    <!-- /компонент ImageManager -->

 <!-- компонент FileLibrary - файловый репозиторий -->
    <xsl:template match="recordset[parent::component[@class='FileLibrary'][@type='list']]">
        <xsl:variable name="TAB_ID" select="generate-id(record[1])"></xsl:variable>
        <div id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}" file_type="{../@allowed_file_type}">
            <ul class="tabs">
                <li>
                    <a href="#{$TAB_ID}"><xsl:value-of select="record[1]/field[1]/@tabName" /></a>
                </li>
            </ul>
            <div class="paneContainer">
                <div id="{$TAB_ID}">
                    <div class="dirArea">
                        <div class="scrollHelper"></div>
                    </div>
                </div>
            </div>
        </div>
    </xsl:template>
    
</xsl:stylesheet>