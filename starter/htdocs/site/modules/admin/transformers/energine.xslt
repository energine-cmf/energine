<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml" 
    xmlns:nrgn="http://energine.org">

    <xsl:template match="document">
        <div id="container">
            <xsl:apply-templates select="$COMPONENTS[@class='BreadCrumbs']" />
            <div id="main">             
                <div id="content">
                    <h2 id="page_title">
                        <xsl:value-of select="$DOC_PROPS[@name='title']" />
                    </h2>
                    <xsl:apply-templates select="content" />                    
                </div>
                <div id="sidebar">
                    <xsl:apply-templates select="$COMPONENTS[@class='LangSwitcher']" />
                    <xsl:apply-templates select="$COMPONENTS[@name='menu']" />                 
                    <xsl:apply-templates select="$COMPONENTS[@class='LoginForm'][parent::layout]" />
                </div>
            </div>
            <div id="footer">
                <xsl:apply-templates select="$COMPONENTS[@name='FooterTextBlock']" />
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