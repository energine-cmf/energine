<?xml version='1.0' encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
    Компонент Редактора разделов
-->

<!-- Вывод дерева разделов -->
<xsl:template match="recordset[parent::component[@class='DivisionEditor'][@type='list']]">
    <div id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}"  single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
        <ul class="tabs">
            <xsl:for-each select="../tabs/tab[@id=$LANG_ID]">
                <xsl:variable name="TAB_NAME" select="@name" />
                <xsl:variable name="TAB_LANG" select="@id" />
                <li>
                    <a href="#{generate-id(../.)}"><xsl:value-of select="$TAB_NAME" /></a>
                    <xsl:if test="$TAB_LANG">
                        <span class="data">{ lang: <xsl:value-of select="$TAB_LANG" /> }</span>
                    </xsl:if>
                </li>
            </xsl:for-each>
        </ul>
        <div class="paneContainer">
            <div id="{generate-id(../tabs)}">
                <div id="treeContainer"></div>
            </div>
        </div>
    </div>
</xsl:template>

<xsl:template match="field[@name='attached_files'][ancestor::component[@class='DivisionEditor']]">
	<xsl:variable name="TRANSLATIONS" select="../../../translations/translation"></xsl:variable>
	<xsl:variable name="JS_OBJECT" select="generate-id(../..)"></xsl:variable>
	<div class="page_rights">
        <table width="100%" id="attached_files">
            <thead>
            <tr>
                <xsl:for-each select="recordset/record[position()=1]/field/@title">
                    <td style="text-align:center;">
                    	<xsl:choose>
                    		<xsl:when test="position() != 1">
                    			<xsl:value-of select="."/>
                    		</xsl:when>
                    		<xsl:otherwise>
                    			<xsl:value-of select="$NBSP" disable-output-escaping="yes"/>
                    		</xsl:otherwise>
                    	</xsl:choose>
                    	</td>
                </xsl:for-each>
             </tr>
            </thead>
            <tbody>
            	<xsl:choose>
            	<xsl:when test="not(recordset/@empty)">
	                <xsl:for-each select="recordset/record">
	                    <tr id="row_{field[@name='upl_id']}">
							<xsl:if test="floor(position() div 2) = position() div 2">
								<xsl:attribute name="class">even</xsl:attribute>
							</xsl:if>
							<td><input type="hidden" name="share_sitemap_uploads[upl_id][]" value="{field[@name='upl_id']}"/><a href="#" onclick="{$JS_OBJECT}.delAttachment({field[@name='upl_id']}); return false;"><xsl:value-of select="$TRANSLATIONS[@const='BTN_DEL_FILE']"/></a></td>
							<td><xsl:value-of select="field[@name='upl_name']"/></td>
							<td>
								<a href="{field[@name='upl_path']/@real_image}" target="blank">
	                            	<xsl:choose>
	                            		<xsl:when test="field[@name='upl_path']/@is_image">
											<img src="{field[@name='upl_path']}" border="0"/>
	                            		</xsl:when>
	                            		<xsl:otherwise>
	                            			<xsl:value-of select="field[@name='upl_path']"/>
	                            		</xsl:otherwise>
	                            	</xsl:choose>
	                            </a>
							</td>
	                    </tr>
	                </xsl:for-each>
           		</xsl:when>
				<xsl:otherwise>
					<tr id="empty_row">
						<td colspan="3"  style="text-align:center;">
							<xsl:value-of select="$TRANSLATIONS[@const='MSG_NO_ATTACHED_FILES']"/>
						</td>
					</tr>
				</xsl:otherwise>
				</xsl:choose>
            </tbody>
            <tfoot>
            	<tr>
            		<td colspan="3" style="text-align:right;">
            			<a href="#" onclick="{$JS_OBJECT}.addAttachment(); return false;"><xsl:value-of select="$TRANSLATIONS[@const='BTN_ADD_FILE']"/></a>
            			<script type="text/javascript">
            				var delete_button_text = '<xsl:value-of select="$TRANSLATIONS[@const='BTN_DEL_FILE']"/>';
            				var no_attached_files = '<xsl:value-of select="$TRANSLATIONS[@const='MSG_NO_ATTACHED_FILES']"/>';
            			</script>
            		</td>
            	</tr>
            </tfoot>
        </table>
    </div>
</xsl:template>


<xsl:template match="recordset[parent::component[@type='form'][@class='DivisionEditor'][@exttype='grid']]">
    <div class="formContainer">
        <div id="{generate-id(.)}" template="{$BASE}{$LANG_ABBR}{../@template}"  single_template="{$BASE}{$LANG_ABBR}{../@single_template}">
            <ul class='tabs'>
                <xsl:for-each select="../tabs/tab">
                    <xsl:variable name="TAB_NAME" select="@name" />
                    <li>
                        <a href="#{generate-id(.)}"><xsl:value-of select="$TAB_NAME" /></a>
                        <xsl:if test="@id">
                            <span class="data">{ lang: <xsl:value-of select="@id" /> }</span>
                        </xsl:if>
                    </li>
                </xsl:for-each>
                <li>
                    <a href="#{generate-id(../rights)}"><xsl:value-of select="../rights/@title"/></a>
                </li>
            </ul>
            <div class="paneContainer">
                <xsl:for-each select="../tabs/tab">
                    <xsl:variable name="TAB_NAME" select="@name" />
                    <div id="{generate-id(.)}">
                        <div>
                            <xsl:apply-templates select="../../recordset/record/field[@tabName=$TAB_NAME]"/>
                        </div>
                    </div>
                </xsl:for-each>
                <xsl:call-template name="BUILD_RIGHTS_TAB"/>
            </div>
        </div>
    </div>
</xsl:template>

<xsl:template name="BUILD_RIGHTS_TAB">
    <div id="{generate-id(../rights)}">
        <div class="page_rights">
            <table width="100%" border="0">
                <thead>
                    <tr>
                        <td><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
                        <xsl:for-each select="../rights/recordset/record[position()=1]/field[@name='right_id']/options/option">
                            <td style="text-align:center;"><xsl:value-of select="."/></td>
                        </xsl:for-each>
                     </tr>
                </thead>
                <tbody>
                    <xsl:for-each select="../rights/recordset/record">
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
    </div>
</xsl:template>

<!-- Рекурсивный Именованный шаблон  -->
<xsl:template name="BUILD_NODE_TREE">
	<xsl:variable name="CURRENT_ID" select="field[@name='smap_id']"/>
		id:<xsl:value-of select="$CURRENT_ID"/>,
        label:"<xsl:value-of select="field[@name='smap_name'][@language='1']"/>",
        isDefault:"<xsl:value-of select="field[@name='smap_default']"/>",
        isSystem:"<xsl:value-of select="field[@name='smap_is_system']"/>",
		childs:<xsl:choose>
		<xsl:when test="count(../record[field[@name='smap_pid'][.=$CURRENT_ID]])>0">[<xsl:for-each select="../record[field[@name='smap_pid']=$CURRENT_ID]">{<xsl:call-template name="BUILD_NODE_TREE" />}<xsl:if test="position()!=last()">,</xsl:if> </xsl:for-each>]</xsl:when>
		<xsl:otherwise>false</xsl:otherwise>
	</xsl:choose>
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
                            <xsl:attribute name="pattern"><xsl:value-of select="@pattern"/></xsl:attribute>
                        </xsl:if>
                        <xsl:if test="@message">
                            <xsl:attribute name="message"><xsl:value-of select="@message"/></xsl:attribute>
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

<!--<xsl:template match="field[@name='smap_redirect_url'][ancestor::component[@class='DivisionEditor'][@type='form'][@exttype='grid']]">
	<div class="field">
		<div class="name">
			<label for="{@name}"><xsl:value-of select="@title"/></label>
        </div>
      <div style="display: block;" id="control__{@name}" class="control">
      	<input type="text" message="{@message}" pattern="{@pattern}" value="{.}" id="{@name}" maxlength="250" name="{@tableName}[{@name}]"/> <button onclick="{generate-id(ancestor::recordset)}.showInternalRedirect(this);">...</button>
      </div>
   </div>
</xsl:template>-->

<xsl:template match="record[parent::recordset[parent::component[@class='DivisionEditor'][@type='list']]]" />
<xsl:template match="rights[parent::component[@class='DivisionEditor']]"/>
</xsl:stylesheet>