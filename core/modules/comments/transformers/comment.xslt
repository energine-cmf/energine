<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  
    xmlns="http://www.w3.org/1999/xhtml" 
    version="1.0">

    <xsl:template match="field[@name='comments'][ancestor::component[@class='News'][@type='form']]
                        | field[@name='comments'][ancestor::component[@class='CastingDigest'][@type='form']]
                        | field[@name='comments'][ancestor::component[@class='StaffFeed'][@type='form']]">
        <xsl:if test="not(recordset[@empty])">
            <xsl:variable name="IS_SHOW_COMMENT_LINK"><xsl:if test="@is_tree='1' and @is_editable='1'">1</xsl:if></xsl:variable>
            <div class="comments">
                Комментарии (<span><xsl:value-of select="count(recordset/record)"/></span>)
                 <ul>
                    <xsl:call-template name="COMMENT">
                        <xsl:with-param name="IS_SHOW_COMMENT_LINK" select="$IS_SHOW_COMMENT_LINK" />
                    </xsl:call-template>
                </ul>
            </div>
       </xsl:if>
    </xsl:template>

    <xsl:template name="COMMENT">
        <xsl:param name="IS_SHOW_COMMENT_LINK" />
        <xsl:for-each select="recordset/record">
           <li id="{field[@name='comment_id']}_comment">
                <xsl:if test="field[@name='u_avatar_img'] != ''">
                    <img width="50" height="50" src="{field[@name='u_avatar_img']}" alt="" />
                </xsl:if>
                <span>
                    <xsl:value-of select="field[@name='comment_created']"/> - <xsl:value-of select="field[@name='u_fullname']"/>
                </span>
                <p>
                    <xsl:value-of select="field[@name='comment_name']"/>
                </p>
                <xsl:if test="$IS_SHOW_COMMENT_LINK='1'">
                    <span><a href="#">Комментировать</a></span>
                </xsl:if>

                <xsl:if test="recordset">
                    <ul>
                        <xsl:call-template name="COMMENT">
                            <xsl:with-param name="IS_SHOW_COMMENT_LINK" select="$IS_SHOW_COMMENT_LINK" />
                        </xsl:call-template>
                    </ul>
                </xsl:if>
           </li>
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>
