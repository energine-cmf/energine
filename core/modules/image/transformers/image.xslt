<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">
    
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
                <xsl:apply-templates select="field[@name='pg_photo_img']"/>
            </div>
            <div class="name"><strong><xsl:value-of select="field[@name='pg_title']"/></strong></div>
            <div class="description"><xsl:value-of select="field[@name='pg_text']"/></div>    
        </li>
    </xsl:template>

    <!-- /компонент GalleryFeed -->
</xsl:stylesheet>