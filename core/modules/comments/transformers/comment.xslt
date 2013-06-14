<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns="http://www.w3.org/1999/xhtml"
        version="1.0">

    <xsl:template match="component[@class='CommentsList'][not(following-sibling::component[@class='ForumTheme'])]">
        <xsl:variable name="IS_ADMIN"><xsl:value-of select="@is_admin"/></xsl:variable>
        <xsl:variable name="IS_EDITABLE"><xsl:value-of select="@is_editable"/></xsl:variable>
        <xsl:variable name="IS_SHOW_COMMENT_LINK"><xsl:if test="@is_tree='1' and @is_editable='1'">1</xsl:if></xsl:variable>
        <div class="comments">

            <h3 class="comments_title">
                <xsl:value-of select="$TRANSLATION[@const='COMMENTS']"/>
                <xsl:text> </xsl:text>
                <span class="figure">(<xsl:value-of select="@comment_count"/>)</span>
            </h3>

            <ul class="comment_list">
                <xsl:if test="$IS_SHOW_COMMENT_LINK = 1">
                    <xsl:call-template name="BUILD_COMMENT">
                        <xsl:with-param name="CLASS">hidden</xsl:with-param>
                        <xsl:with-param name="IS_SHOW_COMMENT_LINK"><xsl:value-of select="$IS_SHOW_COMMENT_LINK"/></xsl:with-param>
                        <xsl:with-param name="IS_ADMIN" select="$IS_ADMIN"/>
                        <xsl:with-param name="IS_EDITABLE" select="$IS_EDITABLE"/>
                        <xsl:with-param name="IS_TEMPLATE">1</xsl:with-param>
                    </xsl:call-template>
                </xsl:if>
                <xsl:if test="not(recordset[@empty])">
                    <xsl:call-template name="COMMENT">
                        <xsl:with-param name="IS_SHOW_COMMENT_LINK" select="$IS_SHOW_COMMENT_LINK" />
                        <xsl:with-param name="IS_ADMIN" select="$IS_ADMIN"/>
                        <xsl:with-param name="IS_EDITABLE" select="$IS_EDITABLE"/>
                    </xsl:call-template>
                </xsl:if>
            </ul>
            <xsl:apply-templates select="$COMPONENTS[@name='commentsForm']" mode="comments"/>
        </div>

    </xsl:template>

    <xsl:template name="COMMENT">
        <xsl:param name="IS_SHOW_COMMENT_LINK" />
        <xsl:param name="IS_ADMIN" />
        <xsl:param name="IS_EDITABLE" />
        <xsl:for-each select="recordset/record">
            <xsl:call-template name="BUILD_COMMENT">
                <xsl:with-param name="CLASS">
                    <xsl:if test="position() = 1">first_item </xsl:if>
                    <xsl:if test="position() = last()">last_item </xsl:if>
                </xsl:with-param>
                <xsl:with-param name="COMMENT_ID"><xsl:value-of select="field[@name='comment_id']"/></xsl:with-param>
                <xsl:with-param name="USER_NAME"><xsl:value-of select="field[@name='u_nick']"/></xsl:with-param>
                <xsl:with-param name="DATE"><xsl:value-of select="field[@name='comment_created']"/></xsl:with-param>
                <xsl:with-param name="TEXT"><xsl:value-of select="field[@name='comment_name']"/></xsl:with-param>
                <xsl:with-param name="IS_SHOW_COMMENT_LINK"><xsl:value-of select="$IS_SHOW_COMMENT_LINK"/></xsl:with-param>
                <xsl:with-param name="IS_ADMIN" select="$IS_ADMIN"/>
                <xsl:with-param name="IS_EDITABLE" select="$IS_EDITABLE"/>
                <xsl:with-param name="U_ID" select="field[@name='u_id']"/>
            </xsl:call-template>
        </xsl:for-each>
    </xsl:template>

    <xsl:template match="component[@name='commentsForm'][not(preceding-sibling::component[@name='forumTheme'])]">
        <xsl:apply-templates select="$COMPONENTS[@name='commentsList']"/>
    </xsl:template>

    <xsl:template match="component[@class='CommentsForm']" mode="comments">
        <div class="comment_inputblock">
            <xsl:if test="$COMPONENTS[@name='commentsForm'][@hide_form=1]">
                <xsl:attribute name="style">display:none;</xsl:attribute>
            </xsl:if>
            <a href="#" class="link_comment"><xsl:value-of select="$TRANSLATION[@const='COMMENT_DO_NEWS']"/></a>
        </div>
        <xsl:call-template name="COMMENT_FORM"/>
    </xsl:template>

    <xsl:template name="COMMENT_FORM">
        <form class="comment_form" method="POST" action="">
            <xsl:if test="$COMPONENTS[@name='commentsForm'][@hide_form=1]">
                <xsl:attribute name="style">display:none;</xsl:attribute>
            </xsl:if>
            <xsl:attribute name="comment_remain"><xsl:value-of select="$TRANSLATION[@const='COMMENT_REMAIN']"/></xsl:attribute>
            <xsl:attribute name="comment_symbol1"><xsl:value-of select="$TRANSLATION[@const='COMMENT_SYMBOL1']"/></xsl:attribute>
            <xsl:attribute name="comment_symbol2"><xsl:value-of select="$TRANSLATION[@const='COMMENT_SYMBOL2']"/></xsl:attribute>
            <xsl:attribute name="comment_symbol3"><xsl:value-of select="$TRANSLATION[@const='COMMENT_SYMBOL3']"/></xsl:attribute>
            <xsl:attribute name="comment_realy_remove"><xsl:value-of select="$TRANSLATION[@const='COMMENT_REALY_REMOVE']"/></xsl:attribute>
            <xsl:attribute name="limit"><xsl:value-of select="@limit"/></xsl:attribute>
            <div class="comment_inputblock" id="{generate-id(recordset)}" single_template="{$BASE}{$LANG_ABBR}{@single_template}">
                <div class="comment_title"><xsl:value-of select="@title"/></div>
                <input type="hidden" name="target_id" value="{recordset/record/field[@name='target_id']}"></input>
                <div class="comment_field">
                    <textarea rows="10" cols="10" id="comment_name" name="comment_name" nrgn:message="{recordset/record/field[@name='comment_name']/@message}" nrgn:pattern="{recordset/record/field[@name='comment_name']/@pattern}" xmlns:nrgn="http://energine.org"></textarea>
                </div>
                <div class="comment_controlset">
                    <xsl:call-template name="BUILD_COMMENT_BUTTON">
                        <xsl:with-param name="CONTENT">
                            <xsl:value-of select="toolbar/control[@id='saveComment']/@title"/>
                        </xsl:with-param>
                        <xsl:with-param name="CLASS">btn_comment</xsl:with-param>
                        <xsl:with-param name="URL">#</xsl:with-param>
                    </xsl:call-template>
                    <xsl:if test="@limit!='-1'">
                        <span class="note">
                            <xsl:value-of select="$TRANSLATION[@const='COMMENT_REMAIN']"/>
                            <xsl:text> </xsl:text><span>250</span><xsl:text> </xsl:text>
                            <xsl:value-of select="$TRANSLATION[@const='COMMENT_SYMBOL3']"/>
                        </span>
                    </xsl:if>
                    <xsl:call-template name="BUILD_COMMENT_BUTTON">
                        <xsl:with-param name="CONTENT"><xsl:value-of select="$TRANSLATION[@const='BTN_CANCEL']"/></xsl:with-param>
                        <xsl:with-param name="CLASS">btn_cancel hidden</xsl:with-param>
                        <xsl:with-param name="URL">#</xsl:with-param>
                    </xsl:call-template>
                </div>
            </div>
        </form>
    </xsl:template>

    <xsl:template name="BUILD_COMMENT">
        <xsl:param name="IS_SHOW_COMMENT_LINK" />
        <xsl:param name="COMMENT_ID"/>
        <xsl:param name="CLASS" as=""/>
        <xsl:param name="USER_NAME"/>
        <xsl:param name="DATE"/>
        <xsl:param name="TEXT"/>
        <xsl:param name="IS_ADMIN" as="0"/>
        <xsl:param name="IS_EDITABLE" as="0"/>
        <xsl:param name="IS_TEMPLATE" as="0"/>
        <xsl:param name="U_ID" as="0"/>

        <xsl:variable name="SHOW_EDIT" select="($IS_ADMIN='1') or ($IS_EDITABLE='1' and $IS_TEMPLATE='1') or (($IS_EDITABLE='1') and ($U_ID = $DOC_PROPS[@name='CURRENT_UID']))"/>
        <li class="comment_item {$CLASS}">
            <xsl:if test="$COMMENT_ID">
                <xsl:attribute name="id"><xsl:value-of select="$COMMENT_ID"/>_comment</xsl:attribute>
            </xsl:if>
            <div class="comment_userinfo clearfix">
                <div class="comment_username"><xsl:value-of select="$USER_NAME"/></div>
                <div class="comment_date"><xsl:value-of select="$DATE"/></div>
            </div>
            <div class="comment_text">
                <xsl:value-of select="$TEXT" disable-output-escaping="yes" />
            </div>
            <xsl:if test="$IS_SHOW_COMMENT_LINK='1' or $SHOW_EDIT">
                <div class="comment_inputblock">
                    <xsl:if test="$IS_SHOW_COMMENT_LINK">
                        <xsl:call-template name="BUILD_COMMENT_BUTTON">
                            <xsl:with-param name="CLASS">btn_comment</xsl:with-param>
                            <xsl:with-param name="URL">#</xsl:with-param>
                            <xsl:with-param name="CONTENT"><xsl:value-of select="$TRANSLATION[@const='COMMENT_DO']"/></xsl:with-param>
                        </xsl:call-template>
                    </xsl:if>

                    <xsl:if test="$SHOW_EDIT">
                        <xsl:call-template name="BUILD_COMMENT_BUTTON">
                            <xsl:with-param name="CLASS">btn_delete</xsl:with-param>
                            <xsl:with-param name="URL">#</xsl:with-param>
                            <xsl:with-param name="CONTENT"><xsl:value-of select="$TRANSLATION[@const='BTN_DELETE']"/></xsl:with-param>
                        </xsl:call-template>

                        <xsl:call-template name="BUILD_COMMENT_BUTTON">
                            <xsl:with-param name="CLASS">btn_edit</xsl:with-param>
                            <xsl:with-param name="URL">#</xsl:with-param>
                            <xsl:with-param name="CONTENT"><xsl:value-of select="$TRANSLATION[@const='BTN_EDIT']"/></xsl:with-param>
                        </xsl:call-template>
                    </xsl:if>
                </div>
            </xsl:if>
            <xsl:if test="$COMMENT_ID and recordset">
                <div class="comment_thread">
                    <i class="icon20x20 comment_thread_icon"><i></i></i>
                    <ul class="comment_list">
                        <xsl:call-template name="COMMENT">
                            <xsl:with-param name="IS_SHOW_COMMENT_LINK" select="$IS_SHOW_COMMENT_LINK" />
                            <xsl:with-param name="IS_ADMIN" select="$IS_ADMIN"/>
                            <xsl:with-param name="IS_EDITABLE" select="$IS_EDITABLE"/>
                        </xsl:call-template>
                    </ul>
                </div>
            </xsl:if>
        </li>
    </xsl:template>

    <xsl:template name="BUILD_COMMENT_BUTTON">
        <xsl:param name="URL"/>
        <xsl:param name="CLICK"/>
        <xsl:param name="CLASS"/>
        <xsl:param name="CONTENT"/>
        <xsl:if test="$CONTENT != ''">
            <a href="{$URL}" class="btn {$CLASS}">
                <xsl:if test="$CLICK">
                    <xsl:attribute name="onclick">
                        <xsl:value-of select="$CLICK"/>
                    </xsl:attribute>
                </xsl:if>
                <span class="btn_content"><xsl:copy-of select="$CONTENT"/></span>
            </a>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>
