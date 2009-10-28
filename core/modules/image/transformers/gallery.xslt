<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

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
        <xsl:apply-templates />
    </xsl:template>

    <xsl:template match="recordset[parent::component[@class='GalleryFeed'][@exttype='feed'][@type='list']]">
        <xsl:if test="not(@empty)">
            <ul id="{generate-id(.)}" class="gallery">
                <xsl:apply-templates />
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
            <div class="name"><strong><xsl:value-of select="field[@name='pg_title']" /></strong></div>
            <div class="description"><xsl:value-of select="field[@name='pg_text']" /></div>    
        </li>
    </xsl:template>

</xsl:stylesheet>
