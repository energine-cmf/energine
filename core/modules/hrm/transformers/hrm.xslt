<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml">

    <!-- компонент VacancyFeed -->
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
                <h4 class="name">
                    <xsl:choose>
                        <xsl:when test="field[@name='vacancy_text_rtf'] != ''">
                            <a href="{$BASE}{$LANG_ABBR}{../../@template}{field[@name='vacancy_url_segment']}/"><xsl:value-of select="field[@name='vacancy_name']"/></a>                   
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="field[@name='vacancy_name']"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </h4>
                <div class="date">
                    <xsl:value-of select="field[@name='vacancy_date']"/>
                    <xsl:if test="field[@name='vacancy_end_date'] != ''">
                        - <xsl:value-of select="field[@name='vacancy_end_date']"/>
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
        <h3 class="name"><xsl:value-of select="field[@name='vacancy_name']"/></h3>
        <div class="date">
            <xsl:value-of select="field[@name='vacancy_date']"/>
            <xsl:if test="field[@name='vacancy_end_date'] != ''">
                 - <xsl:value-of select="field[@name='vacancy_end_date']"/>
            </xsl:if>
        </div>
        <div class="text"><xsl:value-of select="field[@name='vacancy_text_rtf']" disable-output-escaping="yes"/></div>
    </xsl:template>
    <!-- /компонент VacancyFeed -->
    
    <!-- компонент StaffFeed -->
    <xsl:template match="component[@class='StaffFeed']">
        <xsl:if test="not(recordset[@empty])">
            <div class="staff">
                <xsl:apply-templates/>
            </div>
        </xsl:if>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@class='StaffFeed'][@type='list']]">
        <ul id="{generate-id(.)}" class="staff_list">
            <xsl:apply-templates/>
        </ul>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@class='StaffFeed'][@type='list']]">
        <li class="clearfix">
            <xsl:if test="$COMPONENTS[@editable]">
                <xsl:attribute name="record"><xsl:value-of select="field[@index='PRI']"/></xsl:attribute>
            </xsl:if>
            <h4 class="name">
                <xsl:choose>
                    <xsl:when test="field[@name='staff_text_rtf'] != 1">
                        <a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}{field[@name='staff_id']}/"><xsl:value-of select="field[@name='staff_title']" /></a>,                   
                    </xsl:when>
                    <xsl:otherwise>
                        <strong><xsl:value-of select="field[@name='staff_title']"/></strong>,
                    </xsl:otherwise>
                </xsl:choose>
                <span class="position"> <xsl:value-of select="field[@name='staff_post']"/></span>
            </h4>
            <xsl:if test="field[@name='staff_photo_img'] != ''">
                <div class="image"><img src="{$BASE}{field[@name='staff_photo_img']/image[@name='default']}" alt="{field[@name='staff_title']}"/></div>
            </xsl:if>
            <div class="announce"><xsl:value-of select="field[@name='staff_announce']" disable-output-escaping="yes"/></div>
        </li>
    </xsl:template>
   
    <xsl:template match="record[ancestor::component[@class='StaffFeed'][@type='form']]">
        <div class="staff">
            <h3 class="name">
                <xsl:value-of select="field[@name='staff_title']" />
                <span class="position">, <xsl:value-of select="field[@name='staff_post']" /></span>
            </h3>
            <xsl:if test="field[@name='staff_photo_prfile'] != ''">
                <div class="image"><img src="{$BASE}{field[@name='staff_photo_img']/image[@name='default']}" alt="{field[@name='staff_title']}" /></div>
            </xsl:if>
            <div class="text"><xsl:value-of select="field[@name='staff_text_rtf']" disable-output-escaping="yes" /></div>
            <div class="go_back"><a href="{$BASE}{$LANG_ABBR}{ancestor::component/@template}"><xsl:value-of select="../../translations/translation[@const='TXT_STAFF_BACK_LINK']" disable-output-escaping="yes" /></a></div>           
        </div>
    </xsl:template>
    <!-- /компонент StaffFeed -->
    
</xsl:stylesheet>
