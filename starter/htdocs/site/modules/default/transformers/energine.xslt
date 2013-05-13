<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:nrgn="http://energine.org">
    
    <xsl:template match="document">
        <xsl:if test="$COMPONENTS[@class='Ads']/recordset/record/field[@name='ad_top_728_90']">
            <div class="top_adblock">
                <xsl:value-of select="$COMPONENTS[@class='Ads']/recordset/record/field[@name='ad_top_728_90']" disable-output-escaping="yes"/>
            </div>
        </xsl:if>
        <xsl:if test="$COMPONENTS[@class='CrossDomainAuth']">
            <img src="{$COMPONENTS[@class='CrossDomainAuth']/@authURL}?return={$COMPONENTS[@class='CrossDomainAuth']/@returnURL}" width="1" height="1" style="display:none;" alt="" onload="document.location = document.location.href;"/>
        </xsl:if>
        <div class="base">
            <div class="header">
                <h1 class="logo">
                    <a>
                        <xsl:if test="$DOC_PROPS[@name='default']!=1">
                            <xsl:attribute name="href"><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/></xsl:attribute>
                        </xsl:if>
                        <img src="images/{$FOLDER}/energine_logo.png" width="246" height="64" alt="Energine"/>
                    </a>
                </h1>
                <xsl:apply-templates select="$COMPONENTS[@class='LangSwitcher']"/>
            </div>
            <div class="main">
                <xsl:apply-templates select="$COMPONENTS[@class='BreadCrumbs']"/>
                <xsl:apply-templates select="content"/>
            </div>
            <div class="footer">
                <xsl:apply-templates select="$COMPONENTS[@name='footerTextBlock']"/>
            </div>
        </div>
    </xsl:template>

    <xsl:template match="recordset[ancestor::component[@class='SitemapTree']]">
        <ul class="menu">
            <xsl:apply-templates/>
        </ul>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='SitemapTree']]">
        <li>
            <a href="{$BASE}{$LANG_ABBR}{field[@name='Segment']}">
                <xsl:if test="field[@name='Id'] = $DOC_PROPS[@name='ID']"><xsl:attribute name="style">background-color:navy;color:white;</xsl:attribute></xsl:if>                
                <xsl:value-of select="field[@name='Name']"/></a>
            <xsl:apply-templates/>
        </li>
    </xsl:template>

    <xsl:template match="/document/translations[translation[@component='questionEditor']]">
        <xsl:if test="$COMPONENTS[@class='QuestionEditor']/@type='form'">
            <script type="text/javascript">
                <xsl:for-each select="translation">
                    Energine.translations.set('<xsl:value-of select="@const"/>', '<xsl:value-of select="."/>');
                </xsl:for-each>
            </script>
        </xsl:if>
    </xsl:template>
    
</xsl:stylesheet>