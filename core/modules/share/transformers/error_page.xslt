<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">

    <!-- Обработка ошибок -->

    <xsl:template match="errors[parent::component[@type='list']]">
        <div class="errors">
        	<h3><xsl:value-of select="@title"/></h3>
            <ul><xsl:apply-templates/></ul>
        </div>
    </xsl:template>

    <xsl:template match="errors[parent::component[@type='form']]">
    	<div class="errors">
        	<h3><xsl:value-of select="@title"/></h3>
        	<ul><xsl:apply-templates/></ul>
        </div>
    </xsl:template>

    <xsl:template match="error">
        <li><xsl:apply-templates/></li>
    </xsl:template>

</xsl:stylesheet>