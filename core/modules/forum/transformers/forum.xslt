<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  
    xmlns="http://www.w3.org/1999/xhtml" 
    version="1.0">    

    
    <xsl:template match="record[ancestor::component[@class='ForumCategory'][@type='list']]">
        <li class="forum_category">
            <span><a href="{$BASE}{$LANG_ABBR}forum/category/{field[@name='category_id']}">
                <xsl:value-of select="field[@name='category_name']"/>
            </a></span>

            <xsl:if test="../../../@is_can_create_theme">
                <br/>
                <a href="{$BASE}{$LANG_ABBR}forum/theme/{field[@name='category_id']}/create/">
                    Создать тему
                </a>
            </xsl:if>

            <p><xsl:value-of select="field[@name='category_desc']"/></p>

            <xsl:if test="field[@name='themes'] != ''">
                Темы
                <xsl:apply-templates select="field[@name='themes']"/>
            </xsl:if>
		</li>
    </xsl:template>

    <!--Одна категория с темами-->
    <xsl:template match="record[ancestor::component[@class='ForumCategory'][@type='form']]">
        <h2><xsl:value-of select="field[@name='category_name']"/></h2>
        <p><xsl:value-of select="field[@name='category_desc']"/></p>

        <xsl:if test="field[@name='themes'] != ''">
            Темы
            <xsl:apply-templates select="field[@name='themes']"/>
        </xsl:if>
        <xsl:if test="not(field[@name='themes'])">
            Темы не заведены
        </xsl:if>
    </xsl:template>

    <!-- список тем в категории -->
    <xsl:template match="field[@name='themes'][ancestor::component[@class='ForumCategory'][@type='form' or @type='list']]">
        <xsl:if test="../../../@is_can_create_theme">
            <br/>
            <a href="{$BASE}{$LANG_ABBR}forum/theme/{../field[@name='category_id']}/create/">
                Создать тему
            </a>
        </xsl:if>
        <ul>
            <xsl:for-each select="recordset/record">
                <li>
                    <span title="создано"><xsl:value-of select="field[@name='theme_created']"/></span>
                    <h2>
                        <a href="{$BASE}{$LANG_ABBR}forum/theme/{field[@name='theme_id']}">
                            <xsl:value-of select="field[@name='theme_name']"/>
                        </a>
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
            <a href="{$BASE}{$LANG_ABBR}forum/theme/{field[@name='theme_id']}/remove/">
                Удалить тему
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


    
    <xsl:template match="record[ancestor::component[@class='ForumCategory2222222222222222222222'][@type='list']]">
        <li class="blog_item">
        	<span><xsl:value-of select="field[@name='post_created']"/></span>
        	<xsl:if test="field[@name='u_avatar_img'] != ''">
			    <img alt="" width="50" height="50" src="{field[@name='u_avatar_img']}" />
			</xsl:if>
			<a href="{$BASE}{$LANG_ABBR}blogs/blog/{field[@name='blog_id']}">
				<xsl:if test="field[@name='u_nick'] != ''">
				    <xsl:value-of select="field[@name='u_nick']"/>
				</xsl:if>
				<xsl:if test="field[@name='u_nick'] = ''">
				    <xsl:value-of select="field[@name='u_fullname']"/>
				</xsl:if>
			</a>
			
        	<h4>
        		<a href="{$BASE}{$LANG_ABBR}blogs/post/{field[@name='post_id']}">
                   <xsl:value-of select="field[@name='post_name']"/>
                </a>
        	</h4>
            <p><xsl:value-of select="field[@name='post_text_rtf']" disable-output-escaping="yes"/></p>
            
            <span>Комментариев  
			    <xsl:value-of select="field[@name='comments_num']"/>
			    <xsl:if test="field[@name='comments_num'] = ''">0</xsl:if>
			</span>
			<xsl:if test="(../../@curr_user_id = field[@name='u_id']) or  ../../@curr_user_is_admin">
				<a href="{$BASE}{$LANG_ABBR}blogs/post/{field[@name='post_id']}/edit/">Редактировать</a>
			</xsl:if>
			</li>
    </xsl:template>
    
    <xsl:template match="record[ancestor::component[@class='BlogPost'][@componentAction='view']]">
       	<span><xsl:value-of select="field[@name='post_created']"/></span>
       	<xsl:if test="field[@name='u_avatar_img'] != ''">
		    <img width="50" height="50" src="{field[@name='u_avatar_img']}" />
		</xsl:if>
		<a href="{$BASE}{$LANG_ABBR}blogs/blog/{field[@name='blog_id']}">
			<xsl:if test="field[@name='u_nick'] != ''">
			    <xsl:value-of select="field[@name='u_nick']"/>
			</xsl:if>
			<xsl:if test="field[@name='u_nick'] = ''">
			    <xsl:value-of select="field[@name='u_fullname']"/>
			</xsl:if>
		</a>
		
       	<h4>
               <xsl:value-of select="field[@name='post_name']"/>
       	</h4>
        <xsl:if test="(../../@curr_user_id = field[@name='u_id']) or  ../../@curr_user_is_admin">
            <a href="{$BASE}{$LANG_ABBR}blogs/post/{field[@name='post_id']}/edit/">Редактировать</a>
        </xsl:if>
        <p><xsl:value-of select="field[@name='post_text_rtf']" disable-output-escaping="yes"/></p>
        
        <div class="blog_comments">
                <xsl:if test="field[@name='comments'] != ''">
                <xsl:apply-templates select="field[@name='comments']"/>
            </xsl:if>
            <xsl:if test="field[@name='comments'] = ''">
                <div class="comments" style="display:none">
                        Комментарии (<span></span>)
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
