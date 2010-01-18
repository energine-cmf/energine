<?xml version='1.0' encoding="UTF-8" ?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml" 
    xmlns:set="http://exslt.org/sets"
    extension-element-prefixes="set"
    >

<!--
    Компонент Редактора разделов
-->

<xsl:template match="document/translations[translation[@component=//component[@class='DivisionEditor']/@name]]">
        <script type="text/javascript">
            <xsl:for-each select="translation[@component=$COMPONENTS[@class='DivisionEditor']/@name]">
                var <xsl:value-of select="@const"/>='<xsl:value-of select="."/>';
            </xsl:for-each>
        </script>
</xsl:template>

<!-- Вывод дерева разделов -->
<xsl:template match="recordset[parent::component[@class='DivisionEditor'][@componentAction='main'][@type='list']]">
    <div id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}"  lang_id="{$LANG_ID}" single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
        <div id="treeContainer" class="e-divtree-main"></div>
    </div>
</xsl:template>

<xsl:template match="field[@name='page_rights'][@type='custom']">
    <xsl:variable name="RECORDS" select="recordset/record"/>
        <div class="page_rights">
            <table width="100%" border="0">
                <thead>
                    <tr>
                        <td><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
                        <xsl:for-each select="$RECORDS[position()=1]/field[@name='right_id']/options/option">
                            <td style="text-align:center;"><xsl:value-of select="."/></td>
                        </xsl:for-each>
                     </tr>
                </thead>
                <tbody>
                    <xsl:for-each select="$RECORDS">
                        <tr>
							<xsl:if test="floor(position() div 2) = position() div 2">
								<xsl:attribute name="class">even</xsl:attribute>
							</xsl:if>
                            <td class="group_name"><xsl:value-of select="field[@name='group_id']"/></td>
                            <xsl:for-each select="field[@name='right_id']/options/option">
                                <td style="text-align:center;">
                                    <input type="radio" style="border:none; width:auto;" value="{@id}">
                                        <xsl:attribute name="name">right_id[<xsl:value-of select="../../../field[@name='group_id']/@group_id"/>]</xsl:attribute>
                                        <xsl:if test="@selected">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </input>
                                </td>
                            </xsl:for-each>
                        </tr>
                    </xsl:for-each>
                </tbody>
            </table>
        </div>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='DivisionEditor'][@type='form'][@exttype='grid']][@name='smap_pid']">
	<div class="field">
		<xsl:if test="@title">
		    <div class="name">
    			<xsl:element name="label">
    				<xsl:attribute name="for"><xsl:value-of select="@name" /></xsl:attribute>
    				<xsl:value-of select="concat(@title, ':')" disable-output-escaping="yes" />
    			</xsl:element>
    			<xsl:if test="not(@nullable) and @type != 'boolean'">
				    <span style="color:red;"> *</span>
				</xsl:if>
    			<xsl:text> </xsl:text>
			</div>
		</xsl:if>
		<div class="control">
            <xsl:variable name="FIELD_ID"><xsl:value-of select="generate-id()"/></xsl:variable>
            <span class="read" id="s_{$FIELD_ID}" style="margin-right:5px;"><xsl:value-of select="@data_name" disable-output-escaping="yes" /></span>
            <xsl:element name="input">
                <xsl:attribute name="type">hidden</xsl:attribute>
                <xsl:attribute name="id">h_<xsl:value-of select="$FIELD_ID"/></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
        		<xsl:attribute name="name"><xsl:choose>
					<xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
					<xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
				</xsl:choose></xsl:attribute>
            </xsl:element>
    		<button type="button" onclick="{generate-id(../..)}.showTree(this);" hidden_field="h_{$FIELD_ID}" span_field="s_{$FIELD_ID}">...</button>
        </div>
	</div>
</xsl:template>

<xsl:template match="field[ancestor::component[@class='DivisionEditor'][@type='form'][@exttype='grid']][@name='smap_segment']">
	<div class="field">
		    <xsl:attribute name="class">field required</xsl:attribute>
		    <div class="name">
    			<label for="{@name}">
    				<xsl:value-of select="@title" disable-output-escaping="yes" />
    			</label>
			</div>
		<div class="control" style="font-size: 10px; color: gray;">
            <span><xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/></span><span id="smap_pid_segment"><xsl:value-of select="../field[@name='smap_pid']/@segment"/></span>
            <xsl:choose>
                <xsl:when test="@mode='2'">
                    <xsl:element name="input">
                        <xsl:attribute name="type">text</xsl:attribute>
                        <xsl:attribute name="style">width:150px;</xsl:attribute>
                        <xsl:attribute name="name"><xsl:choose>
                                    <xsl:when test="@tableName"><xsl:value-of select="@tableName" /><xsl:if test="@language">[<xsl:value-of select="@language"/>]</xsl:if>[<xsl:value-of select="@name" />]</xsl:when>
                                    <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                                </xsl:choose></xsl:attribute>
                        <xsl:if test="@length">
                            <xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
                        </xsl:if>
                        <xsl:attribute name="id"><xsl:value-of select="@name" /></xsl:attribute>
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                        <xsl:if test="@pattern">
                            <xsl:attribute name="nrgn:pattern" xmlns:nrgn="http://energine.org"><xsl:value-of select="@pattern"/></xsl:attribute>
                        </xsl:if>
                        <xsl:if test="@message">
                            <xsl:attribute name="nrgn:message"  xmlns:nrgn="http://energine.org"><xsl:value-of select="@message"/></xsl:attribute>
                        </xsl:if>
                    </xsl:element>
                </xsl:when>
                <xsl:otherwise>
                    <span class="read" style="color: #000; font-size: 11px;"><xsl:value-of select="." disable-output-escaping="yes" /></span>
                    <xsl:element name="input">
                        <xsl:attribute name="type">hidden</xsl:attribute>
                        <xsl:attribute name="name"><xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                        </xsl:choose></xsl:attribute>
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    </xsl:element>
                </xsl:otherwise>
            </xsl:choose>/
        </div>
	</div>
</xsl:template>

<!-- Поле выбора из списка -->
<xsl:template match="field[ancestor::component[@class='DivisionEditor'][@type='form'][@exttype='grid']][@name='tmpl_id']">
<div class="field">
	    <xsl:attribute name="class">field required</xsl:attribute>
        <div class="name">
            <label for="{@name}">
                <xsl:value-of select="@title" disable-output-escaping="yes" />
            </label>
        </div>
		<div class="control">
            <xsl:element name="select">
                <xsl:attribute name="name"><xsl:choose>
                            <xsl:when test="@tableName"><xsl:value-of select="@tableName" />[<xsl:value-of select="@name" />]</xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                        </xsl:choose></xsl:attribute>
                <xsl:attribute name="id"><xsl:value-of select="@name"/></xsl:attribute>
                <xsl:if test="@nullable='1'">
                    <xsl:element name="option"></xsl:element>
                </xsl:if>
                <xsl:for-each select="options/option[@tmpl_is_system='']">
                	<xsl:sort select="@tmpl_order_num" order="ascending" data-type="number"/>
                    <xsl:element name="option">
                        <xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
                        <xsl:if test="@selected">
                            <xsl:attribute name="selected">selected</xsl:attribute>
                        </xsl:if>
                        <xsl:value-of select="."/>
                    </xsl:element>
                </xsl:for-each>
                <optgroup label="System templates">
                <xsl:for-each select="options/option[@tmpl_is_system='1']">
                	<xsl:sort select="@tmpl_order_num" order="ascending" data-type="number"/>
                    <xsl:element name="option">
                        <xsl:attribute name="disabled">disabled</xsl:attribute>
                        <xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
                        <xsl:if test="@selected">
                            <xsl:attribute name="selected">selected</xsl:attribute>
                        </xsl:if>
                        <xsl:value-of select="."/>
                    </xsl:element>
                </xsl:for-each>
                </optgroup>
            </xsl:element>
        </div>
	</div>
</xsl:template>

<xsl:template match="record[parent::recordset[parent::component[@class='DivisionEditor'][@type='list']]]" />
<xsl:template match="rights[parent::component[@class='DivisionEditor']]"/>
</xsl:stylesheet>