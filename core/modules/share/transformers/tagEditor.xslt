<?xml version='1.0' encoding="UTF-8" ?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        >

    <xsl:template match="recordset[parent::component[@class='TagEditor'][@type='list']]">
        <xsl:variable name="NAME" select="../@name"/>
        <div id="{generate-id(.)}" class="e-pane e-pane-has-t-toolbar1" template="{$BASE}{$LANG_ABBR}{../@template}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}" tag_id="{../@tag_id}">
            <xsl:if test="../toolbar">
                <xsl:attribute name="class">e-pane e-pane-has-t-toolbar1 e-pane-has-b-toolbar1</xsl:attribute>
            </xsl:if>
            <xsl:call-template name="BUILD_GRID"/>
            <xsl:if test="count($TRANSLATION[@component=$NAME])&gt;0">
                <script type="text/javascript">
                    <xsl:for-each select="$TRANSLATION[@component=$NAME]">
                        Energine.translations.set('<xsl:value-of select="@const"/>', '<xsl:value-of select="."/>');
                    </xsl:for-each>
                </script>
            </xsl:if>
        </div>
    </xsl:template>

</xsl:stylesheet>
