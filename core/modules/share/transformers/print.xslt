<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >
        <xsl:import href="list.xslt" />

        <xsl:variable name="DOC_PROPS" select="/document/properties/property"/>
        <xsl:variable name="COMPONENTS" select="//component[@name][@module]"/>
        <xsl:variable name="TRANSLATION" select="/document/translations/translation"/>
        <xsl:variable name="ID" select="$DOC_PROPS[@name='ID']"/>
        <xsl:variable name="BASE" select="$DOC_PROPS[@name='base']"/>
        <xsl:variable name="FOLDER" select="$DOC_PROPS[@name='base']/@folder"/>
        <xsl:variable name="LANG_ID" select="$DOC_PROPS[@name='lang']"/>
        <xsl:variable name="LANG_ABBR" select="$DOC_PROPS[@name='lang']/@abbr"/>
        <xsl:variable name="NBSP"><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></xsl:variable>
        <xsl:variable name="STATIC_URL"><xsl:value-of select="$BASE/@static"/></xsl:variable>
        <xsl:variable name="MEDIA_URL"><xsl:value-of select="$BASE/@media"/></xsl:variable>

        <xsl:template match="/">
            <html>
                <head>
                    <base href="{$BASE}"/>
                    <link rel="stylesheet" type="text/css" href="{$STATIC_URL}/stylesheets/energine.css"/>
                </head>
                <body style="padding: 0; margin: 0;">
                    <xsl:apply-templates select="document"/>
                </body>
            </html>
        </xsl:template>

        <xsl:template match="document">
            <xsl:apply-templates select="$COMPONENTS" />
        </xsl:template>

        <xsl:template match="component[@type='list']">
            <table border="1" cellpadding="10" cellspacing="0">
                <caption></caption>
                <thead>
                    <tr>
                        <th>...</th>
                        <xsl:for-each select="recordset/record[1]/field[not(@type = 'hidden') and not(@index = 'PRI')]">
                            <th><xsl:value-of select="@title"/></th>
                        </xsl:for-each>
                    </tr>
                </thead>
                <tbody>
                    <xsl:for-each select="recordset/record">
                        <tr>
                        <td><xsl:value-of select="position()"/></td>
                        <xsl:for-each select="field[not(@type = 'hidden') and not(@index = 'PRI')]">
                            <td><xsl:apply-templates select="."/></td>
                        </xsl:for-each>
                        </tr>
                    </xsl:for-each>
                </tbody>
            </table>
        </xsl:template>

</xsl:stylesheet>