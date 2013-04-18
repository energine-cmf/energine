<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns="http://www.w3.org/1999/xhtml"
        version="1.0">

    <xsl:template match="component[@class='Vote' and @componentAction='main']">
        <div id="{generate-id(recordset)}" vote_url="{$BASE}{$LANG_ABBR}{@single_template}vote-{@vote_id}/">

        </div>
        <xsl:apply-templates select="javascript"/>
    </xsl:template>

    <xsl:template match="component[@class='Vote' and (@componentAction='getVote' or @componentAction='vote')]">
        <xsl:variable name="TEMPLATE" select="@single_template"/>
        <div class="block">
            <!-- <h2 class="block_title"><xsl:value-of select="@title"/></h2> -->
            <div class="vote">
                <div class="vote_date"><xsl:value-of select="@date"/></div>
                <div class="vote_text"><xsl:value-of select="@question"/></div>
                <div class="vote_options">
                    <xsl:for-each select="recordset/record">
                        <div class="vote_option">
                            <xsl:choose>
                                <xsl:when test="../../@canVote = 0">
                                    <div class="result clearfix">
                                        <span class="name"><xsl:value-of select="field[@name='vote_question_title']"/></span>
                                        <xsl:if test="field[@name='percent'] != ''">
                                            <span class="figure"><xsl:value-of select="field[@name='percent']"/>%</span>
                                        </xsl:if>
                                    </div>
                                </xsl:when>
                                <xsl:otherwise>
                                    <a href="{$BASE}{$LANG_ABBR}{$TEMPLATE}vote/{field[@name='vote_question_id']}/"><xsl:value-of select="field[@name='vote_question_title']"/></a>
                                </xsl:otherwise>
                            </xsl:choose>
                        </div>
                    </xsl:for-each>
                </div>
                <xsl:if test="@count != ''">
                    <div class="vote_count"><xsl:value-of select="$TRANSLATION[@const='TXT_VOTE_COUNT']"/>: <xsl:value-of select="@count"/></div>
                </xsl:if>
            </div>
        </div>
    </xsl:template>

</xsl:stylesheet>
