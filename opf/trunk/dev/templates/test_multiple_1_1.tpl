<html> 
<head> 
  <title>Test multiple 1</title> 
</head> 
<body>
<h3>Many forms</h3>
<p>OPF can handle many forms at the same time. If you fill one, the library knows, which one is it.</p>
<hr/>

<h3>Form 1</h3>
{* Invalid form message *}
<opt:if test="$form1_error">
<p>The form has been incorrectly filled in.</p>
</opt:if>

{* The form *}
<opf:form method="post" action="`test_multiple_1.php`" name="form1">
<table class="form1" width="100%">
  <tr>
  <td>Some text</td>
  <td><opt:opfInput name="someText"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" value="OK"/></td> 
 </tr>
</table>
</opf:form>

<h3>Form 2</h3>
{* Invalid form message *}
<opt:if test="$form2_error">
<p>The form has been incorrectly filled in.</p>
</opt:if>

{* The form *}
<opf:form method="post" action="`test_multiple_1.php`" name="form2">
<table class="form1" width="100%">
  <tr>
  <td>Some text</td>
  <td><opt:opfInput name="someText"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" value="OK"/></td> 
 </tr>
</table>
</opf:form>
</body>
</html>
