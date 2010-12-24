<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  
    xmlns="http://www.w3.org/1999/xhtml" 
    version="1.0">    

    
    <!-- список тем в категории -->
    <xsl:template match="component[@class='ForumTheme'][@type='list']">
        <xsl:if test="@is_can_create_theme">
            <br/>
            <a href="{$BASE}{$LANG_ABBR}{@template}create/">
                <xsl:value-of select="$TRANSLATION[@const='FORUM_CREATE_THEME']"/>
            </a>
        </xsl:if>
        <xsl:apply-templates select="component[@name='forumSubCategory'][@class='PageList'][@type='list']" mode="formCategory" />
        <ul>
            <xsl:for-each select="recordset/record">
                <li>
                    <span title="{$TRANSLATION[@const='FORUM_CREATED']}">
                        <xsl:value-of select="field[@name='theme_created']"/>
                    </span>
                    <h2>
                        <a href="{$BASE}{$LANG_ABBR}{../../@template}{field[@name='theme_id']}"><xsl:value-of select="field[@name='theme_name']"/></a>
                    </h2>
                    <xsl:if test="field[@name='theme_text']">
                        <p><xsl:value-of select="field[@name='theme_text']" disable-output-escaping="yes"/></p>
                    </xsl:if>
                    <xsl:if test="field[@name='comment_num'] != ''">
                        <xsl:value-of select="$TRANSLATION[@const='FORUM_LAST_POST']"/>:
                        <span><xsl:value-of select="field[@name='u_name']"/></span> :
                        <span><xsl:value-of select="field[@name='comment_name']"/></span>
                        (<span><xsl:value-of select="field[@name='comment_created']"/></span>)
                    </xsl:if>
                </li>
            </xsl:for-each>
        </ul>
    </xsl:template>

    <!-- Подкатегории -->
    <xsl:template match="component[@name='forumSubCategory'][@class='PageList'][@type='list']"/>
    <xsl:template match="component[@name='forumSubCategory'][@class='PageList'][@type='list']" mode="formCategory">
        <xsl:if test="$COMPONENTS[@name='forumSubCategory'][@class='PageList'][@type='list']/recordset/record">
            <h2>
                <xsl:value-of select="$TRANSLATION[@const='FORUM_SUBCATEGORY']"/>
            </h2>
            <ul>
                <xsl:for-each select="$COMPONENTS[@name='forumSubCategory'][@class='PageList'][@type='list']/recordset/record">
                    <li>
                        <a href="{$BASE}{$LANG_ABBR}{field[@name='Segment']}"><xsl:value-of select="field[@name='Name']"/></a>
                    </li>
                </xsl:for-each>
            </ul>
        </xsl:if>
    </xsl:template>

    <!--просмотр темы-->
    <xsl:template match="record[ancestor::component[@class='ForumTheme'][@componentAction='view']]">
        <xsl:if test="../../@curr_user_is_admin or ../../@curr_user_id=field[@name='u_id']">
            <a href="{$BASE}{$LANG_ABBR}{../../@template}{field[@name='theme_id']}/remove/">
                <xsl:value-of select="$TRANSLATION[@const='FORUM_DELETE_THEME']"/>
            </a>
            
            <a href="{$BASE}{$LANG_ABBR}{../../@template}{field[@name='theme_id']}/modify/">
                <xsl:value-of select="$TRANSLATION[@const='FORUM_EDIT_THEME']"/>
            </a>
            <br/>
        </xsl:if>
        <span>
            <a href="#"><xsl:value-of select="field[@name='u_name']"/></a>
            (<xsl:value-of select="field[@name='theme_created']"/>)
        </span>
        <h2><xsl:value-of select="field[@name='theme_name']"/></h2>
        <p><xsl:value-of select="field[@name='theme_text']" disable-output-escaping="yes"/></p>

        <div class="forum_comments">
                <xsl:if test="field[@name='comments'] != ''">
                <xsl:apply-templates select="field[@name='comments']"/>
            </xsl:if>
            <xsl:if test="field[@name='comments'] = ''">
                <div class="comments" style="display:none">
                        <xsl:value-of select="$TRANSLATION[@const='FORUM_ANSWERS']"/> (<span></span>)
                        <ul/>
                </div>
            </xsl:if>
        </div>
    </xsl:template>

<!--    <xsl:template match="record[ancestor::component[@class='BlogPost'][@componentAction='edit']]">
        <form action="{$BASE}{$LANG_ABBR}blogs/post/{@post_id}/save/" method="post" class="form">
            <xsl:apply-templates/>
        </form>

    </xsl:template>-->

</xsl:stylesheet>
