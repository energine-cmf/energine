<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns="http://www.w3.org/1999/xhtml">

    <!-- обработка компонента типа feed -->
    <xsl:template match="component[@exttype='feed']">
        <div class="feed">
            <xsl:choose>
                <xsl:when test="recordset/@empty">
                    <div class="empty_message"><xsl:value-of select="recordset/@empty" disable-output-escaping="yes"/></div>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:apply-templates/>
                </xsl:otherwise>
            </xsl:choose>
        </div>
    </xsl:template>

    <!-- компонент feed в режиме списка -->
    <xsl:template match="recordset[parent::component[@exttype='feed'][@type='list']]">
        <ul id="{generate-id(.)}" class="feed_list">
            <xsl:apply-templates/>
        </ul>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@exttype='feed'][@type='list']]">
        <li>
            <xsl:if test="$COMPONENTS[@editable]">
                <xsl:attribute name="record">
                    <xsl:value-of select="field[@index='PRI']"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
            <xsl:if test="../../toolbar[@name!='pager']">
                <xsl:apply-templates select="../../toolbar[@name!='pager']/control" />
            </xsl:if>
        </li>
    </xsl:template>

    <xsl:template match="toolbar[ancestor::component[@exttype='feed'][@type='list']][@name!='pager']"/>

    <xsl:template match="control[parent::toolbar[@name!='pager' and ancestor::component[@exttype='feed'][@type='list']]]">
        <a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}"><xsl:value-of select="@title"/></a>
    </xsl:template>

    <!-- компонент feed в режиме просмотра -->
    <xsl:template match="recordset[parent::component[@exttype='feed'][@type='form']]">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@exttype='feed'][@type='form']]">
        <div class="feed_view" id="{generate-id(../.)}">
            <xsl:if test="$COMPONENTS[@editable]">
                <xsl:attribute name="current">
                    <xsl:value-of select="field[@index='PRI']"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </div>
    </xsl:template>

    <xsl:template match="component[@exttype='feededitor'][@type='list']">
        <xsl:if test="recordset">
            <xsl:variable name="LINK">
                <xsl:value-of select="@linkedComponent"/>
            </xsl:variable>
            <ul id="{generate-id(recordset)}" style="display:none;"
                single_template="{$BASE}{$LANG_ABBR}{@single_template}"
                linkedTo="{generate-id($COMPONENTS[@name=$LINK]/recordset)}">
                <xsl:for-each select="toolbar/control">
                    <li id="{@id}" title="{@title}" type="{@type}" action="{@onclick}"></li>
                </xsl:for-each>
            </ul>
        </xsl:if>
    </xsl:template>

    <xsl:template match="field[@type='htmlblock' and ancestor::component[@exttype='feed' and @type='form']]">
        <xsl:variable name="FIELD_VALUE">
            <xsl:choose>
                <xsl:when test="ancestor::component/@editable and (.='')"><xsl:value-of select="$NBSP" disable-output-escaping="yes"/></xsl:when>
                <xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <div class="feed_text">
            <xsl:if test="ancestor::component/@editable">
                <xsl:attribute name="class">nrgnEditor feed_text</xsl:attribute>
                <xsl:attribute name="num">
                    <xsl:value-of select="@name"/>
                </xsl:attribute>
                <xsl:attribute name="single_template">
                    <xsl:value-of select="$BASE"/><xsl:value-of
                        select="$LANG_ABBR"/><xsl:value-of
                        select="ancestor::component/@single_template"/>
                </xsl:attribute>
                <xsl:attribute name="eID">
                    <xsl:value-of select="../field[@index='PRI']"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:value-of select="$FIELD_VALUE" disable-output-escaping="yes"/>
        </div>
    </xsl:template>

    <xsl:template match="field[ancestor::component[@exttype='feed' and @type='form']][@name='smap_id']"/>

    <!-- обработка attachments для компонента feed -->
    <!-- в виде превью -->
    <xsl:template match="field[@name='attachments'][ancestor::component[@exttype='feed']]" mode="preview">
        <xsl:param name="PREVIEW_WIDTH"/>
        <xsl:param name="PREVIEW_HEIGHT"/>
        <xsl:variable name="URL"><xsl:choose>
            <xsl:when test="name(recordset/record[1]/field[@name='file']/*[1])='video'"><xsl:value-of select="$VIDEO_RESIZER_URL"/>w<xsl:value-of select="$PREVIEW_WIDTH"/>-h<xsl:value-of select="$PREVIEW_HEIGHT"/>-c<xsl:value-of select="$PREVIEW_WIDTH"/>:<xsl:value-of select="$PREVIEW_HEIGHT"/>/<xsl:value-of select="recordset/record[1]/field[@name='file']/*[1]"/>.png</xsl:when>
            <xsl:otherwise><xsl:value-of select="$IMAGE_RESIZER_URL"/>?w=<xsl:value-of select="$PREVIEW_WIDTH"/>&amp;h=<xsl:value-of select="$PREVIEW_HEIGHT"/>&amp;c=<xsl:value-of select="$PREVIEW_WIDTH"/>:<xsl:value-of select="$PREVIEW_HEIGHT"/>&amp;i=<xsl:value-of select="recordset/record[1]/field[@name='file']/*[1]"/></xsl:otherwise>
        </xsl:choose></xsl:variable>
        <img width="{$PREVIEW_WIDTH}" height="{$PREVIEW_HEIGHT}">
            <xsl:choose>
                <xsl:when test="recordset">
                    <xsl:attribute name="src"><xsl:value-of select="$URL"/></xsl:attribute>
                    <xsl:attribute name="alt"><xsl:value-of select="recordset/record[1]/field[@name='name']"/></xsl:attribute>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:attribute name="src"><xsl:value-of select="$MEDIA_URL"/>images/default_<xsl:value-of select="$PREVIEW_WIDTH"/>x<xsl:value-of select="$PREVIEW_HEIGHT"/>.png</xsl:attribute>
                    <xsl:attribute name="alt"><xsl:value-of select="$TRANSLATION[@const='TXT_NO_IMAGE']"/></xsl:attribute>
                </xsl:otherwise>
            </xsl:choose>
        </img>
        <xsl:if test="recordset/record[1]/field[@name='file']/video">
            <i class="icon play_icon"></i>
        </xsl:if>
    </xsl:template>

    <!-- в виде плеера -->
    <xsl:template match="field[@name='attachments'][ancestor::component[@exttype='feed']]" mode="player">
        <xsl:param name="PLAYER_WIDTH"/>
        <xsl:param name="PLAYER_HEIGHT"/>
        <xsl:if test="recordset">
            <!--<xsl:if test="(count(recordset/record) &gt; 1) or (name(recordset/record[1]/field[@name='file']/*[1]) = 'video')">-->

                <div class="feed_media">
                    <!--<xsl:if test="count(recordset/record) &gt; 1">-->
                        <script type="text/javascript" src="scripts/Carousel.js"></script>
                        <script type="text/javascript" src="scripts/Playlist.js"></script>

                        <script type="text/javascript">
                            var carousel, playlist;

                            window.addEvent('domready', function() {
                                    carousel = new Carousel('playlist', {visibleItems : 6, css : 'carousel.css'});
                                    playlist = new Playlist('playlist', 'player', 'playerBox');
                            });
                        </script>
                    <!--</xsl:if>-->
                </div>
            <!--</xsl:if>-->
            <div class="player_box" id="playerBox">
                <xsl:variable name="URL"><xsl:choose>
                    <xsl:when test="name(recordset/record[1]/field[@name='file']/*[1])='video'"><xsl:value-of select="$VIDEO_RESIZER_URL"/>w<xsl:value-of select="$PLAYER_WIDTH"/>-h<xsl:value-of select="$PLAYER_HEIGHT"/>-c<xsl:value-of select="$PLAYER_WIDTH"/>:<xsl:value-of select="$PLAYER_HEIGHT"/>/<xsl:value-of select="recordset/record[1]/field[@name='file']/*[1]"/>.png</xsl:when>
                    <xsl:otherwise><xsl:value-of select="$IMAGE_RESIZER_URL"/>?w=<xsl:value-of select="$PLAYER_WIDTH"/>&amp;h=<xsl:value-of select="$PLAYER_HEIGHT"/>&amp;c=<xsl:value-of select="$PLAYER_WIDTH"/>:<xsl:value-of select="$PLAYER_HEIGHT"/>&amp;i=<xsl:value-of select="recordset/record[1]/field[@name='file']/*[1]"/></xsl:otherwise>
                </xsl:choose></xsl:variable>
                <div class="player" id="player" style="width: {$PLAYER_WIDTH}px; height: {$PLAYER_HEIGHT}px; background: url({$URL}) 50% 50% no-repeat;">
                    <xsl:if test="recordset/record[1]/field[@name='file']/video or count(recordset/record) &gt; 1">
                        <a href="#" class="play_button"></a>
                    </xsl:if>
                </div>
            </div>
        </xsl:if>
    </xsl:template>

    <!-- в виде карусели -->
    <xsl:template match="field[@name='attachments'][ancestor::component[@exttype='feed']]" mode="carousel">
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
                                                <xsl:when test="field[@name='file']/video"><img src="{$VIDEO_RESIZER_URL}w{$PREVIEW_WIDTH}-h{$PREVIEW_HEIGHT}-c{$PREVIEW_WIDTH}:{$PREVIEW_HEIGHT}/{field[@name='file']/*[1]}.png" alt="{field[@name='name']}" width="{$PREVIEW_WIDTH}" height="{$PREVIEW_HEIGHT}"/></xsl:when>
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
                     <a class="previous_control" href="#"><i></i></a>
                     <a class="next_control" href="#"><i></i></a>
                 </div>
             </div>
        </xsl:if>
    </xsl:template>
    <!-- /обработка attachments для для компонента feed -->

    <!-- фид новостей -->
    <xsl:template match="component[@class='NewsFeed']">
        <div class="feed news">
            <xsl:apply-templates/>
        </div>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='NewsFeed'][@type='list']]">
        <li>
            <xsl:if test="$COMPONENTS[@editable]">
                <xsl:attribute name="record">
                    <xsl:value-of select="field[@index='PRI']"/>
                </xsl:attribute>
            </xsl:if>
            <div class="feed_date">
                <strong>
                    <xsl:value-of select="field[@name='news_date']"/>
                </strong>
            </div>
            <h4 class="feed_name">
                <a href="{$BASE}{$LANG_ABBR}{field[@name='category']/@url}{field[@name='news_id']}--{field[@name='news_segment']}/">
                    <xsl:value-of select="field[@name='news_title']"/>
                </a>
            </h4>
            <div class="feed_image">
                <xsl:apply-templates select="field[@name='attachments']" mode="preview">
                    <xsl:with-param name="PREVIEW_WIDTH">90</xsl:with-param>
                    <xsl:with-param name="PREVIEW_HEIGHT">68</xsl:with-param>
                </xsl:apply-templates>
            </div>
            <div class="feed_announce">
                <xsl:value-of select="field[@name='news_announce_rtf']" disable-output-escaping="yes"/>
            </div>
        </li>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='NewsFeed'][@type='form']]">
        <div class="feed_view" id="{generate-id(../.)}">
            <xsl:if test="$COMPONENTS[@editable]">
                <xsl:attribute name="current">
                    <xsl:value-of select="field[@index='PRI']"/>
                </xsl:attribute>
            </xsl:if>
            <div class="feed_date">
                <strong>
                    <xsl:value-of select="field[@name='news_date']"/>
                </strong>
            </div>
            <h4 class="feed_name">
                <xsl:value-of select="field[@name='news_title']"/>
            </h4>
            <xsl:apply-templates select="field[(@name!='news_date') and (@name!='news_title') and(@name!='smap_id')]"/>
        </div>
    </xsl:template>
    <!-- /фид новостей -->

</xsl:stylesheet>