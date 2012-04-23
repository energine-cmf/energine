<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns="http://www.w3.org/1999/xhtml">

    <!--
        Шаблон - контроллер для любого поля из компонента типа форма.
        Создает стандартную обвязку вокруг элемента формы:
            <div class="field">
                <div class="name"><label>Имя поля</label></div>
                <div class="control">** импорт шаблона, который создает HTML-элемент формы из share/base.xslt **</div>
            </div>
    -->
    <xsl:template match="field[ancestor::component[@type='form']]">
    	<!--<div class="field">
    	    <xsl:if test="not(@nullable) and @type != 'boolean'">
    		    <xsl:attribute name="class">field required</xsl:attribute>
    		</xsl:if>
    		<xsl:if test="@title and @type != 'boolean'">
    		    <div class="name">
        			<label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes" /></label>
    				<xsl:if test="not(@nullable) and not(ancestor::component/@exttype = 'grid') and not(ancestor::component[@class='TextBlockSource'])"><span class="mark">*</span></xsl:if>
    			</div>
    		</xsl:if>
    		<div class="control" id="control_{@language}_{@name}">
        		<xsl:apply-imports />
            </div>
    	</div>-->
    </xsl:template>

    <!-- Отображение поля типа image для не гридовых элементов формы -->
<!--    <xsl:template match="field[@type='image'][ancestor::component[@type='form' or @type='list'][not(@exttype='grid')]]">
        <xsl:variable name="THUMB" select="image[@name='default']"/>
        <xsl:variable name="MAIN" select="image[@name='main']"/>
        <a href="{$MAIN}" target="_blank" class="thumbnail">
            <img src="{$THUMB}" width="{$THUMB/@width}" height="{$THUMB/@height}">
                <xsl:attribute name="nrgn:image_width"  xmlns:nrgn="http://energine.org"><xsl:value-of select="$MAIN/@width"/></xsl:attribute>
                <xsl:attribute name="nrgn:image_height"  xmlns:nrgn="http://energine.org"><xsl:value-of select="$MAIN/@height"/></xsl:attribute>
                <xsl:attribute name="nrgn:image_src"  xmlns:nrgn="http://energine.org"><xsl:value-of select="$MAIN"/></xsl:attribute>
            </img>
        </a>


    </xsl:template>    -->

    <!--
        Шаблон для необязательного (nullable) поля в админчасти вынесен отдельно.
        В нем добавляется возможность скрыть/раскрыть необязательное поле.
    -->


    <!-- для любого поля, на которое нет прав на просмотр -->
    <!--<xsl:template match="field[@mode=0][ancestor::component[@type='form']]"/>-->

    <!-- для любого поля, на которое права только чтение -->


    <!-- read-only поле логического типа -->


    <!-- для поля HTMLBLOCK на которое права только чтение -->


    <!-- в виде плеера -->
    <xsl:template match="field[@name='attachments']" mode="player">
        <xsl:param name="PLAYER_WIDTH"/>
        <xsl:param name="PLAYER_HEIGHT"/>
        <xsl:if test="recordset">
            <!--<xsl:if test="(count(recordset/record) &gt; 1) or (name(recordset/record[1]/field[@name='file']/*[1]) = 'video')">-->
                <!--<xsl:if test="count(recordset/record) &gt; 1">-->
                    <script type="text/javascript" src="{$STATIC_URL}scripts/flowplayer-3.2.6.min.js"></script>
                    <script type="text/javascript" src="{$STATIC_URL}scripts/Carousel.js"></script>
                    <script type="text/javascript" src="{$STATIC_URL}scripts/Playlist.js"></script>

                    <script type="text/javascript">
                        var carousel, playlist;

                        window.addEvent('domready', function() {
                                carousel = new Carousel('playlist', {visibleItems : 6, css : 'carousel.css'});
                                playlist = new Playlist('playlist', 'player', 'playerBox');
                        });
                    </script>
                <!--</xsl:if>-->
            <!--</xsl:if>-->
            <div class="player_box" id="playerBox">
                <xsl:variable name="URL"><xsl:choose>
                    <xsl:when test="name(recordset/record[1]/field[@name='file']/*[1])='video'"><xsl:value-of select="$VIDEO_RESIZER_URL"/>?w=<xsl:value-of select="$PLAYER_WIDTH"/>&amp;h=<xsl:value-of select="$PLAYER_HEIGHT"/>&amp;c=<xsl:value-of select="$PLAYER_WIDTH"/>:<xsl:value-of select="$PLAYER_HEIGHT"/>&amp;i=<xsl:value-of select="recordset/record[1]/field[@name='file']/*[1]"/></xsl:when>
                    <xsl:otherwise><xsl:value-of select="$IMAGE_RESIZER_URL"/>?w=<xsl:value-of select="$PLAYER_WIDTH"/>&amp;h=<xsl:value-of select="$PLAYER_HEIGHT"/>&amp;c=<xsl:value-of select="$PLAYER_WIDTH"/>:<xsl:value-of select="$PLAYER_HEIGHT"/>&amp;i=<xsl:value-of select="recordset/record[1]/field[@name='file']/*[1]"/></xsl:otherwise>
                </xsl:choose></xsl:variable>
                <div class="player" id="player" style="width: {$PLAYER_WIDTH}px; height: {$PLAYER_HEIGHT}px; background: black url({$URL}) 50% 50% no-repeat;">
                    <xsl:if test="recordset/record[1]/field[@name='file']/video or count(recordset/record) &gt; 1">
                        <a href="#" class="play_button"></a>
                    </xsl:if>
                </div>
            </div>
        </xsl:if>
    </xsl:template>

    <!-- в виде карусели -->
    <xsl:template match="field[@name='attachments']" mode="carousel">
        <xsl:param name="PREVIEW_WIDTH"/>
        <xsl:param name="PREVIEW_HEIGHT"/>
        <xsl:if test="(count(recordset/record) &gt; 1) or not(recordset/record/field[@name='file']/image)">
            <div class="carousel_box">
                <xsl:if test="not(recordset/record/field[@name='file']/image)">
                    <xsl:attribute name="style">display:none;</xsl:attribute>
                </xsl:if>
                <!--<div class="carousel_title">
                    <xsl:value-of select="@title"/>
                </div>-->
                <div class="carousel" id="playlist">
                    <div class="carousel_viewbox viewbox">
                        <ul>
                            <xsl:for-each select="recordset/record">
                                <li>
                                    <div class="carousel_image" id="{field[@name='id']}_imgc">
                                        <a href="{field[@name='file']/video | field[@name='file']/image}" xmlns:nrgn="http://energine.org" nrgn:media_type="{name(field[@name='file']/*[1])}">
                                            <xsl:choose>
                                                <xsl:when test="field[@name='file']/video"><img src="{$VIDEO_RESIZER_URL}?w={$PREVIEW_WIDTH}&amp;h={$PREVIEW_HEIGHT}&amp;c={$PREVIEW_WIDTH}:{$PREVIEW_HEIGHT}&amp;i={field[@name='file']/*[1]/@image}" alt="{field[@name='name']}" width="{$PREVIEW_WIDTH}" height="{$PREVIEW_HEIGHT}"/></xsl:when>
                                                <xsl:otherwise><img src="{$IMAGE_RESIZER_URL}?w={$PREVIEW_WIDTH}&amp;h={$PREVIEW_HEIGHT}&amp;c={$PREVIEW_WIDTH}:{$PREVIEW_HEIGHT}&amp;i={field[@name='file']/*[1]/@image}" alt="{field[@name='name']}" width="{$PREVIEW_WIDTH}" height="{$PREVIEW_HEIGHT}"/></xsl:otherwise>
                                            </xsl:choose>
                                             <xsl:if test="field[@name='file']/video">
                                                 <i class="icon play_icon"></i>
                                             </xsl:if>
                                         </a>
                                     </div>
                                 </li>
                             </xsl:for-each>
                         </ul>
                     </div>
                     <a class="previous_control" href="#"><xsl:text disable-output-escaping="yes">&amp;lt;</xsl:text></a>
                     <a class="next_control" href="#"><xsl:text disable-output-escaping="yes">&amp;gt;</xsl:text></a>
                 </div>
             </div>
        </xsl:if>
    </xsl:template>
    <!-- /обработка attachments -->

</xsl:stylesheet>
