<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns="http://www.w3.org/1999/xhtml">


    <xsl:template match="component[@exttype='calendar']">
        <xsl:if test="not(recordset/@empty)">
            <div class="calendar">
                <xsl:apply-templates select="toolbar"/>
                <xsl:apply-templates select="recordset"/>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@exttype='calendar']]">
        <div class="calendar_content">
           <table cellspacing="0" border="1" width="100%" class="calendar_table">
               <thead>
                   <tr class="names">
                       <th><xsl:value-of select="$NBSP" disable-output-escaping="yes"/></th>
                       <xsl:for-each select="record[1]/field">
                           <th class="day">
                               <a><xsl:value-of select="@title"/></a>
                           </th>
                       </xsl:for-each>
                       <th class="last"><xsl:value-of select="$NBSP" disable-output-escaping="yes"/></th>
                   </tr>
               </thead>
               <tbody>
                   <xsl:apply-templates/>
               </tbody>
           </table>
        </div>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@exttype='calendar']]">
        <tr class="week">
            <xsl:if test="field[@today='today']">
                <xsl:attribute name="class">week current_week</xsl:attribute>
            </xsl:if>
            <td><xsl:value-of select="$NBSP" disable-output-escaping="yes"/></td>
            <xsl:apply-templates />
            <td class="last"><xsl:value-of select="$NBSP" disable-output-escaping="yes"/></td>
        </tr>
    </xsl:template>

    <xsl:template match="field[ancestor::component[@exttype='calendar']]">
        <td>
            <xsl:attribute name="class">day<xsl:if test="position() = last()"> last_day</xsl:if><xsl:if test="@today"> current_day</xsl:if><xsl:if test="not(@current)"> foreign_day</xsl:if><xsl:if test="@marked"> active_day</xsl:if><xsl:if test="not(@selected) and not(@marked)"> inactive_day</xsl:if></xsl:attribute>
            <a>
                <xsl:if test="@selected">
                    <xsl:attribute name="href">
                        <xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of select="../../../@template"/><xsl:value-of select="@year"/>/<xsl:value-of select="@month"/>/<xsl:value-of select="@day"/>/
                    </xsl:attribute>
                </xsl:if>
                <xsl:value-of select="."/>
            </a>
        </td>
    </xsl:template>

    <xsl:template match="toolbar[parent::component[@exttype='calendar']]">
        <div class="calendar_header">
            <xsl:apply-templates select="control[@id='previous']"/>
            <xsl:apply-templates select="control[@id='current']"/>
            <xsl:apply-templates select="control[@id='next']"/>
        </div>
    </xsl:template>

    <xsl:template match="control[parent::toolbar[parent::component[@exttype='calendar']]]">
        <a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}{@year}/{@month}/">
            <xsl:choose>
                <xsl:when test="@id='current'">
                    <xsl:value-of select="@monthName"/>,
                    <xsl:value-of select="@year"/>
                </xsl:when>
                <xsl:when test="@id='next'">
                    <xsl:text disable-output-escaping="yes">&gt;</xsl:text>
                </xsl:when>
                <xsl:when test="@id='previous'">
                    <xsl:text disable-output-escaping="yes">&lt;</xsl:text>
                </xsl:when>
            </xsl:choose>
        </a>
    </xsl:template>

</xsl:stylesheet>
