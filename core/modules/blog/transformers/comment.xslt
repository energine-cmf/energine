<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  

    version="1.0">
    
    <xsl:template match="field[@name='comments'][ancestor::component[@class='BlogPost'][@type='form']]">

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


</xsl:stylesheet>
