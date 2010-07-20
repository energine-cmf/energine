<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  
    xmlns="http://www.w3.org/1999/xhtml" 
    version="1.0">
    
    <xsl:template match="field[@name='comments'][ancestor::component[@class='BlogPost'][@type='form']]">
        <!-- <a class="comment_link" href="#">Комментировать</a>  -->

        <xsl:if test="not(recordset[@empty])">
			<xsl:variable name="IS_SHOW_COMMENT_LINK"><xsl:if test="@is_tree='1' and @is_editable='1'">1</xsl:if></xsl:variable>
           
            <div class="comments">
                Комментарии (<span><xsl:value-of select="count(recordset/record)"/></span>)
                <ul>
                    <xsl:for-each select="recordset/record">
                       <li id="{field[@name='comment_id']}_comment">
                            <xsl:if test="field[@name='u_avatar_img'] != ''">
                                <img widht="50" height="50" src="{field[@name='u_avatar_img']}" />
                            </xsl:if>
                            <span><xsl:value-of select="field[@name='comment_created']"/> - <xsl:value-of select="field[@name='u_fullname']"/></span>
                            <p><xsl:value-of select="field[@name='comment_name']"/></p>
                            <!-- a class="edit" href="{$BASE}{$LANG_ABBR}tvslot-editor/{field[@name='tvslot_id']}/edit">Edit</a -->
                            <xsl:apply-templates select="recordset"/>
                            
                            <xsl:if test="$IS_SHOW_COMMENT_LINK">
                                <span><a href="#">Комментировать</a></span>
                            </xsl:if>
                       </li>
                    </xsl:for-each>
                </ul>
            </div>
       </xsl:if>
    </xsl:template>

</xsl:stylesheet>