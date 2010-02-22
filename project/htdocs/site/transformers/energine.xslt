<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml" 
    xmlns:nrgn="http://energine.org">

    <xsl:template match="document">
        <div id="container">
            <div id="header">
                <h1><img src="images/energine_logo.png" width="246" height="64" alt="Energine Content Management System" /></h1>
            </div>
            <xsl:apply-templates select="$COMPONENTS[@class='BreadCrumbs']" />
            <div id="main">             
                <div id="content">
                    <h2 id="page_title"><xsl:value-of select="$DOC_PROPS[@name='title']" /></h2>
                    <xsl:apply-templates select="content" />                    
                </div>
                <div id="sidebar">
                    <xsl:apply-templates select="$COMPONENTS[@class='LangSwitcher']" />
                    <xsl:apply-templates select="$COMPONENTS[@class='MainMenu']" />                 
                    <xsl:apply-templates select="$COMPONENTS[@class='CurrencySwitcher'][parent::layout]" />
                    <xsl:apply-templates select="$COMPONENTS[@class='BasketList'][parent::layout]" />
                    <xsl:apply-templates select="$COMPONENTS[@class='LoginForm'][parent::layout]" />
                </div>
            </div>
            <div id="footer">
                <xsl:apply-templates select="$COMPONENTS[@name='FooterTextBlock']" />
            </div>
        </div>
    </xsl:template>
       
</xsl:stylesheet>