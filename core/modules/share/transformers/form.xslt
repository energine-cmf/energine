<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="component[@type='form']">
        <form method="post" action="{@action}">
            <xsl:if test="descendant::field[@type='image'] or descendant::field[@type='file'] or descendant::field[@type='pfile'] or descendant::field[@type='prfile']">
            	<xsl:attribute name="enctype">multipart/form-data</xsl:attribute>
            </xsl:if>
			<xsl:choose>
				<xsl:when test="@class='FeedbackForm'"><xsl:attribute name="class">base_form feedback_form</xsl:attribute></xsl:when>
				<xsl:when test="@class='UserProfile'"><xsl:attribute name="class">base_form profile_form</xsl:attribute></xsl:when>
				<xsl:otherwise></xsl:otherwise>
			</xsl:choose>
            <input type="hidden" name="componentAction" value="{@componentAction}" id="componentAction"/>
    		<xsl:apply-templates />
        </form>
    </xsl:template>

    <xsl:template match="component[@type='form'][@class='FeedbackForm'][@componentAction='send']">
	    <xsl:value-of select="recordset/record/field[@name='result']" disable-output-escaping="yes"/>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@type='form']]">
    	<div id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}">
    		<xsl:apply-templates />
    	</div>
		<xsl:if test="../translations/translation[@const='TXT_REQUIRED_FIELDS']">
			<div class="note">
				<xsl:value-of select="../translations/translation[@const='TXT_REQUIRED_FIELDS']" disable-output-escaping="yes" />
			</div>
		</xsl:if>
    </xsl:template>

    <xsl:template match="translation[@const='TXT_REQUIRED_FIELDS'][ancestor::component[@type='form']]" />

    <!-- Форма как часть grid-а выводится в другом стиле -->
    <xsl:template match="recordset[parent::component[@type='form' and @exttype='grid']]">
        <div class="formContainer">
            <div id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
                <ul class="tabs">
                    <xsl:for-each select="../tabs/tab">
                    <xsl:variable name="TAB_NAME" select="@name" />
                        <xsl:if test="count(../../recordset/record/field[@tabName=$TAB_NAME][not(@index='PRI')])&gt;0">
                            <li><a href="#{generate-id(.)}"><xsl:value-of select="@name" /></a></li>
                        </xsl:if>
                     </xsl:for-each>
                </ul>
                <div class="paneContainer">
                <xsl:choose>
                    <xsl:when test="count(../tabs/tab)&gt;1">
                        <xsl:for-each select="../tabs/tab">
                            <xsl:variable name="TAB_NAME" select="@name" />
                            <div id="{generate-id(.)}">
                                <div>
                                    <xsl:apply-templates select="../../recordset/record/field[@tabName=$TAB_NAME]"/>
                                </div>
                            </div>
                        </xsl:for-each>
                    </xsl:when>
                    <xsl:otherwise>
                        <div id="{generate-id(../tabs/tab[1])}">
                            <div>
                                <xsl:apply-templates select="record/field"/>
                            </div>
                        </div>
                    </xsl:otherwise>
                </xsl:choose>

                </div>
            </div>
        </div>
    </xsl:template>

    <xsl:template match="record[ancestor::component[@type='form']]">
        <xsl:apply-templates />
    </xsl:template>

    <xsl:template match="javascript[parent::component[@type='form']]">
    	<script type="text/javascript">
    		<xsl:apply-templates />
    	</script>
    </xsl:template>

	<!-- Template Editor -->

	<xsl:template match="
		field[@name='tmpl_content'][ancestor::component[@class='TemplateEditor'][@type='form']]
		|
		field[@name='tmpl_layout'][ancestor::component[@class='TemplateEditor'][@type='form']]
	">
		<xsl:variable name="TEXT"><xsl:choose>
			<xsl:when test="@name='tmpl_content'">.content.xml</xsl:when>
			<xsl:otherwise>.layout.xml</xsl:otherwise>
		</xsl:choose></xsl:variable>
		<div class="field">
		    <xsl:attribute name="class">field required</xsl:attribute>
		    <div class="name">
    			<label for="{@name}"><xsl:value-of select="@title" disable-output-escaping="yes" /></label>
				<xsl:if test="not(@nullable) and not(ancestor::component/@exttype = 'grid')"><span class="mark">*</span></xsl:if>
                <xsl:if test="@help">
                    <xsl:variable name="HELP_IMG"><xsl:value-of select="generate-id()"/></xsl:variable>
                    <img src="images/help.gif" width="11" height="11" border="0" title="{@help}"/>
                </xsl:if>
			</div>
			<div class="control" id="control_{@language}_{@name}">
				<xsl:element name="input">
					<xsl:attribute name="style">width:182px; text-align:right;</xsl:attribute>
					<xsl:attribute name="type">text</xsl:attribute>
					<xsl:attribute name="name">
								<xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:attribute>
					<xsl:if test="@length">
						<xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
					</xsl:if>
					<xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
					<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
			        	<xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
			        	<xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
				</xsl:element><span><xsl:value-of select="$TEXT"/></span>
        	</div>
		</div>
	</xsl:template>

</xsl:stylesheet>