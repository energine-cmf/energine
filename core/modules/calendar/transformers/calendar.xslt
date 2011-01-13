<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns="http://www.w3.org/1999/xhtml">


    <xsl:template match="component[@exttype='calendar']">
        <xsl:if test="not(recordset/@empty)">
            <div class="rcbox">
                <div class="rcbox_t">
                    <i class="rcbox_tl"></i>
                    <i class="rcbox_tr"></i>
                </div>
                <!--
                <div class="rcbox_header clearfix">
                    <h2 class="rcbox_name"></h2>
                </div>
                -->
                <div class="rcbox_content clearfix">

                    <div class="calendar">
                        <!--
                       <div class="calendar_filter">
                           <select>
                               <option>1</option>
                               <option>2</option>
                               <option>3</option>
                               <option>4</option>
                               <option selected="selected">5</option>
                               <option>6</option>
                               <option>7</option>
                               <option>8</option>
                               <option>9</option>
                               <option>10</option>
                               <option>11</option>
                               <option>12</option>
                               <option>13</option>
                               <option>14</option>
                           </select>
                           <select>
                               <option>серпня</option>
                               <option selected="selected">вересня</option>
                               <option>жовтня</option>
                               <option>листопада</option>
                               <option>грудня</option>
                           </select>
                           <select>
                               <option>2008</option>
                               <option selected="selected">2009</option>
                               <option>2010</option>
                           </select>
                           <a href="#" class="btn btn_submit">
                               <i class="btn_tl"></i><i class="btn_tr"></i>
                               <span class="btn_tc">
                                   <span class="btn_bc">
                                       <span class="btn_ml">
                                           <span class="btn_mr">
                                               <span class="btn_content">обрати</span>
                                           </span>
                                       </span>
                                   </span>
                               </span>
                               <i class="btn_bl"></i><i class="btn_br"></i>
                           </a>
                       </div>
                        -->
                        <xsl:apply-templates select="toolbar"/>
                        <xsl:apply-templates select="recordset"/>
                    </div>

                </div>
                <div class="rcbox_b">
                    <i class="rcbox_bl"></i>
                    <i class="rcbox_br"></i>
                </div>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@exttype='calendar']]">
        <div class="calendar_content">
           <table  cellspacing="0" border="1" width="100%" class="calendar_table">
            <tbody>
                <tr  class="names">
                    <td><xsl:value-of select="$NBSP" disable-output-escaping="yes"/></td>
                    <xsl:for-each select="record[1]/field">
                        <td class="day">
                            <a><xsl:value-of select="@title"/></a>
                        </td>
                    </xsl:for-each>
                    <td class="last"><xsl:value-of select="$NBSP" disable-output-escaping="yes"/></td>
                </tr>
                <xsl:apply-templates/>
                <tr>
                <xsl:for-each select="record[1]/field">
                    <td>
                        <xsl:if test="position() = last()"><xsl:attribute name="class">last</xsl:attribute></xsl:if>
                        <xsl:value-of select="$NBSP" disable-output-escaping="yes"/>
                    </td>
                </xsl:for-each>
            </tr>
            </tbody>

        </table>
                        </div>

    </xsl:template>

    <xsl:template match="record[ancestor::component[@exttype='calendar']]">
        <!--current_week-->
        <tr class="week">
            <td><xsl:value-of select="$NBSP" disable-output-escaping="yes"/></td>
            <xsl:for-each select="field">
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
            </xsl:for-each>
            <td class="last"><xsl:value-of select="$NBSP" disable-output-escaping="yes"/></td>
        </tr>
    </xsl:template>

    <xsl:template match="toolbar[parent::component[@exttype='calendar']]">
        <div class="calendar_header">
            <div class="calendar_header_t">
                <i class="calendar_header_tl"></i>
                <i class="calendar_header_tr"></i>
            </div>
            <div class="calendar_header_c">
                <xsl:apply-templates select="control[@id='previous']"/>
                <xsl:apply-templates select="control[@id='current']"/>
                <xsl:apply-templates select="control[@id='next']"/>
            </div>
        </div>

    </xsl:template>

    <xsl:template
            match="control[parent::toolbar[parent::component[@exttype='calendar']]]">
        <a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}{@year}/{@month}/">
            <xsl:choose>
                <xsl:when test="@id='current'">
                    <xsl:value-of select="@monthName"/>,
                    <xsl:value-of select="@year"/>
                </xsl:when>
                <xsl:when test="@id='next'">
                    <xsl:attribute name="class">icon20x20 next_control</xsl:attribute>
                    <i></i>
                </xsl:when>
                <xsl:when test="@id='previous'">
                    <xsl:attribute name="class">icon20x20 previous_control</xsl:attribute>
                    <i></i>
                </xsl:when>
            </xsl:choose>
        </a>
    </xsl:template>

</xsl:stylesheet>
