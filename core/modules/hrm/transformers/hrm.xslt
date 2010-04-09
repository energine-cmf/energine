<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">

<!-- Vacancies -->
    <xsl:template match="component[@class='VacancyFeed']">
        <xsl:if test="not(recordset[@empty])">
            <div class="vacancies">
                <xsl:apply-templates/>               
            </div>
        </xsl:if>       
    </xsl:template>
   
    <xsl:template match="recordset[parent::component[@class='VacancyFeed'][@type='list']]">
        <ul id="{generate-id(.)}" class="vacancy_list">
            <xsl:apply-templates/>
        </ul>
    </xsl:template>
   
    <xsl:template match="record[ancestor::component[@class='VacancyFeed'][@type='list']]">
        <xsl:if test="field[@name='vacancy_is_active'] = 1">
            <li class="vacancy_item">
                <xsl:if test="$COMPONENTS[@editable]">
                    <xsl:attribute name="record"><xsl:value-of select="field[@index='PRI']"/></xsl:attribute>
                </xsl:if>               
                <div class="name">
                    <xsl:choose>
                        <xsl:when test="field[@name='vacancy_text_rtf'] != ''">
                            <a href="{$BASE}{$LANG_ABBR}{../../@template}{field[@name='vacancy_url_segment']}/"><xsl:value-of select="field[@name='vacancy_name']"/></a>                   
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="field[@name='vacancy_name']"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
                <div class="date">
                    <xsl:value-of select="translate(field[@name='vacancy_date'], '/', '.')"/>
                    <xsl:if test="field[@name='vacancy_end_date'] != ''">
                        - <xsl:value-of select="translate(field[@name='vacancy_end_date'], '/', '.')"/>
                    </xsl:if>
                </div>
                <div class="announce"><xsl:value-of select="field[@name='vacancy_annotation']" disable-output-escaping="yes"/></div>
            </li>
        </xsl:if>
    </xsl:template>
   
    <xsl:template match="recordset[parent::component[@class='VacancyFeed'][@type='form']]">
        <div class="vacancy_view">
            <xsl:apply-templates/>
            <div class="go_back">
                <a href="{$BASE}{$LANG_ABBR}{../@template}"><xsl:value-of select="$TRANSLATION[@const='TXT_BACK_TO_LIST']"/></a>
            </div>
        </div>
    </xsl:template>
   
    <xsl:template match="record[ancestor::component[@class='VacancyFeed'][@type='form']]">       
        <div class="name"><xsl:value-of select="field[@name='vacancy_name']"/></div>
        <div class="date">
            <xsl:value-of select="translate(field[@name='vacancy_date'], '/', '.')"/>
            <xsl:if test="field[@name='vacancy_end_date'] != ''">
                 - <xsl:value-of select="translate(field[@name='vacancy_end_date'], '/', '.')"/>
            </xsl:if>
        </div>
        <div class="text"><xsl:value-of select="field[@name='vacancy_text_rtf']" disable-output-escaping="yes"/></div>
    </xsl:template>
    <!-- /Vacancies -->
</xsl:stylesheet>
