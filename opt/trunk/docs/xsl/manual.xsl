<?xml version='1.0'?> 
<xsl:stylesheet  
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"> 

<xsl:import href="docbook/html/docbook.xsl"/> 

<xsl:param name="l10n.gentext.language" select="/book/@lang"/>
<xsl:param name="html.stylesheet" select="'style.css'"/> 
<xsl:param name="section.autolabel" select="1"/>
<xsl:param name="section.label.includes.component.label" select="1"/>
<xsl:param name="toc.section.depth" select="2"/>

<xsl:param name="generate.toc">
appendix  toc,title
sect1     toc
sect2     toc
book      toc,title
section   toc
preface   toc,title
chapter   toc,title
</xsl:param>
<xsl:param name="generate.section.toc.level" select="1"/>
<xsl:param name="funcsynopsis.style">ansi</xsl:param>

<xsl:template match="mark">
  <xsl:element name="div">
    <xsl:attribute name="class">
      <xsl:value-of select="@type"/>
    </xsl:attribute>
    <xsl:apply-templates />
  </xsl:element>
</xsl:template>

<xsl:template match="variable">
  <em><xsl:apply-templates /></em>
</xsl:template>

<xsl:template match="parameter">
	<xsl:if test="@choice = 'opt'">[
		<xsl:value-of select="."/>
		<xsl:call-template name="parameter"/>
	]</xsl:if>
</xsl:template>
<xsl:template match="methodsynopsis">
  <div class="{name(.)}">
    <xsl:apply-templates />
  </div>
</xsl:template>

<xsl:template match="methodsynopsis/type">
  <xsl:apply-templates />
  <xsl:text> </xsl:text>
</xsl:template>

<xsl:template match="methodsynopsis/void">
  <xsl:text> ( void )</xsl:text>
</xsl:template>

<xsl:template match="methodsynopsis/methodname">
  <b class="{local-name(.)}">
    <xsl:value-of select="."/>
  </b>
</xsl:template>

<xsl:template match="methodparam/type">
  <xsl:apply-templates />
  <xsl:text> </xsl:text>
</xsl:template>

<xsl:template match="methodparam/parameter">
  <xsl:if test="@role='reference'">
    <xsl:text>&amp;</xsl:text>
  </xsl:if>
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="methodparam">
  <xsl:if test="preceding-sibling::methodparam=false()">
    <xsl:text> ( </xsl:text>
    <xsl:if test="@choice='opt'">
      <xsl:text>[</xsl:text>
    </xsl:if>
  </xsl:if>
  <xsl:apply-templates />
  <xsl:choose>
    <xsl:when test="following-sibling::methodparam">
      <xsl:choose>
        <xsl:when test="following-sibling::methodparam[position()=1]/@choice='opt'">
          <xsl:text> [, </xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>, </xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:for-each select="preceding-sibling::methodparam">
				<xsl:if test="@choice='opt'">
					<xsl:text>]</xsl:text>
				</xsl:if>
      </xsl:for-each>
      <xsl:if test="self::methodparam/@choice='opt'">
        <xsl:text>]</xsl:text>
      </xsl:if>
      <xsl:text> )</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>
</xsl:stylesheet>
