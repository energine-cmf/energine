<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">


    <xsl:template match="field[@type='pfile'][ancestor::component[@class='ResumeForm']]">
        <input id="{@name}">       
            <xsl:call-template name="FORM_ELEMENT_ATTRIBUTES"/>
            <xsl:attribute name="type">file</xsl:attribute>
            <xsl:attribute name="name">file</xsl:attribute>            
        </input>
    </xsl:template>    
    
</xsl:stylesheet>
