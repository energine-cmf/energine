<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

     <xsl:template match="component[@class='GalleryFeed'] | component[@class='PhotoListFeed']">
        <script type="text/javascript" src="scripts/EnlargeImage.js"></script>
        <script type="text/javascript">
            var showPhoto = function(obj){
                var img = $E('img', $(obj));
                new EnlargeImage(img,{duration: 800, position: 'this'})
                return false;
            } 
        </script>
        <xsl:apply-templates />
    </xsl:template>

    <xsl:template match="recordset[parent::component[@class='GalleryFeed'][@exttype='feed'][@type='list']] | recordset[parent::component[@class='PhotoListFeed'][@exttype='feed'][@type='list']]">
        <xsl:if test="not(@empty)">
            <ul id="{generate-id(.)}" class="gallery">
                <xsl:apply-templates />
            </ul>        
        </xsl:if>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='GalleryFeed'][@type='list']] | record[ancestor::component[@class='PhotoListFeed'][@type='list']]">
        <li record="{field[@index='PRI']}">
            <div class="image"><a href="{$BASE}{field[@name='pg_photo_img']}" onclick="return showPhoto(this);"><img src="{$BASE}{field[@name='pg_thumb_img']}" alt="{field[@name='pg_title']}" main="{$BASE}{field[@name='pg_photo_img']}" real_width="{field[@name='pg_photo_img']/@width}" real_height="{field[@name='pg_photo_img']/@height}"/></a></div>
            <div class="name"><strong><xsl:value-of select="field[@name='pg_title']" /></strong></div>
            <div class="description"><xsl:value-of select="field[@name='pg_text']" /></div>    
        </li>
    </xsl:template>

</xsl:stylesheet>
