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
                Создать тему
            </a>
        </xsl:if>
        <ul>
            <xsl:for-each select="recordset/record">
                <li>
                    <span title="создано"><xsl:value-of select="field[@name='theme_created']"/></span>
                    <h2>
                        <a href="{$BASE}{$LANG_ABBR}{../../@template}{field[@name='theme_id']}"><xsl:value-of select="field[@name='theme_name']"/></a>
                    </h2>
                    <xsl:if test="field[@name='theme_text']">
                        <p><xsl:value-of select="field[@name='theme_text']"/></p>
                    </xsl:if>
                    <xsl:if test="field[@name='comment_num'] != ''">
                        Последний пост:
                        <span><xsl:value-of select="field[@name='u_name']"/></span> :
                        <span><xsl:value-of select="field[@name='comment_name']"/></span>
                        (<span><xsl:value-of select="field[@name='comment_created']"/></span>)
                    </xsl:if>
                </li>
            </xsl:for-each>
        </ul>
    </xsl:template>

    <!--просмотр темы-->
    <xsl:template match="record[ancestor::component[@class='ForumTheme'][@componentAction='view']]">
        <xsl:if test="../../@curr_user_is_admin or ../../@curr_user_id=field[@name='u_id']">
            <a href="{$BASE}{$LANG_ABBR}{../../@template}{field[@name='theme_id']}/remove/">
                Удалить тему
            </a>
            
            <a href="{$BASE}{$LANG_ABBR}{../../@template}{field[@name='theme_id']}/modify/">
                Редактировать
            </a>
            <br/>
        </xsl:if>
        <span>
            <a href="#"><xsl:value-of select="field[@name='u_name']"/></a>
            (<xsl:value-of select="field[@name='theme_created']"/>)
        </span>
        <h2><xsl:value-of select="field[@name='theme_name']"/></h2>
        <p><xsl:value-of select="field[@name='theme_text']"/></p>

        <div class="forum_comments">
                <xsl:if test="field[@name='comments'] != ''">
                <xsl:apply-templates select="field[@name='comments']"/>
            </xsl:if>
            <xsl:if test="field[@name='comments'] = ''">
                <div class="comments" style="display:none">
                        Ответы (<span></span>)
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
