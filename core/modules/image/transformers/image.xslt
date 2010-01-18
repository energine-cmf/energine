<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
    <!-- компонент GalleryFeed -->
    <xsl:template match="component[@class='GalleryFeed']">
        <script type="text/javascript" src="scripts/EnlargeImage.js"></script>
        
        <script type="text/javascript">
            var currentImage = false;
            var showPhoto = function(obj){
                //Для некоторых ИЕ
                if(window.event){
                    var event = new Event(window.event);
            
                    if (event.stopPropagation) event.stopPropagation();
                    else event.cancelBubble = true;
            
                    if (event.preventDefault) event.preventDefault();
                    else event.returnValue = false;
                }
                
                var createImage = function(){
                        currentImage = new EnlargeImage($(obj).getElement('img'),{duration: 800});    
                };
                
                if(currentImage){
                    currentImage.zoomOut(createImage);                    
                }
                else{
                    createImage();
                }
                
                return false;
            } 
        </script>
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@class='GalleryFeed'][@type='list']]">
        <xsl:if test="not(@empty)">
            <ul id="{generate-id(.)}" class="gallery">
                <xsl:apply-templates/>
            </ul>        
        </xsl:if>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='GalleryFeed'][@type='list']]">
        <li record="{field[@index='PRI']}">
            <div class="image">
                <a href="{$BASE}{field[@name='pg_photo_img']}" onclick="return showPhoto(this);">
                    <img 
                        width="{field[@name='pg_thumb_img']/@width}"
                        height="{field[@name='pg_thumb_img']/@height}"
                        src="{$BASE}{field[@name='pg_thumb_img']}" 
                        alt="{field[@name='pg_title']}" 
                        main="{$BASE}{field[@name='pg_photo_img']}" 
                        real_width="{field[@name='pg_photo_img']/@width}" 
                        real_height="{field[@name='pg_photo_img']/@height}"/>
                </a>
            </div>
            <div class="name"><strong><xsl:value-of select="field[@name='pg_title']"/></strong></div>
            <div class="description"><xsl:value-of select="field[@name='pg_text']"/></div>    
        </li>
    </xsl:template>
    <!-- /компонент GalleryFeed -->
    
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

</xsl:stylesheet>