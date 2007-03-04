<?xml version='1.0'?> 
<xsl:stylesheet  
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"> 

<xsl:import href="docbook/html/docbook.xsl"/> 

<xsl:param name="l10n.gentext.language" select="/book/@lang"/>
<xsl:param name="html.stylesheet" select="'style.css'"/> 
<xsl:param name="section.autolabel" select="1"/>
<xsl:param name="section.label.includes.component.label" select="1"/>
<xsl:param name="toc.section.depth" select="4"/>

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

<xsl:template match="funcprototype">
  <div class="prototype"><xsl:value-of select="funcdef/text()"/> <span class="funcname"><xsl:value-of select="funcdef/function"/></span>(
  <xsl:for-each select="paramdef/parameter">
  	<xsl:value-of select="."/>,   
  </xsl:for-each>);
  </div>
</xsl:template>
</xsl:stylesheet>
