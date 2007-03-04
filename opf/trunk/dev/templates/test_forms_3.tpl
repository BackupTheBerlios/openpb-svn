{* Definition of CSS classes for the components *}
{$opfDesign->setDesign('row', '', 'revert')}
{$opfDesign->setDesign('input', 'insert', 'incorrect')}
{$opfDesign->setDesign('textarea', 'insert', 'incorrect')}

<html> 
<head> 
  <title>Example 4</title>
  <link rel="stylesheet" href="style.css" type="text/css" /> 
</head> 
<body>
<h1>Example 4</h1>
<h3>Design configuration</h3>
<p>This example shows, how to customize the look of the OPF components. Try to fill in the form incorrectly and see, what happens.</p>
<hr/>

<opt:if test="$error_msg">
<p>The form has been incorrectly filled in.</p>
</opt:if>

<opf:form method="post" action="`test_forms_3.php`" name="form1">
<table class="form" width="100%">
  <tr opf:classfor="username">
  <td>Username</td>
  <td><opt:opfInput name="username"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr opf:classfor="password">
  <td>Password</td>
  <td><opt:opfPassword name="password"><opt:load event="aMessage"/></opt:opfPassword></td> 
 </tr>
  <tr opf:classfor="email">
  <td>E-mail</td>
  <td><opt:opfInput name="email"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr opf:classfor="age">
  <td>Age</td>
  <td><opt:opfInput name="age"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr opf:classfor="content">
  <td>Content</td>
  <td><opt:opfTextarea name="content" rows="3" cols="50"><opt:load event="aMessage"/></opt:opfTextarea></td> 
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" value="OK"/></td> 
 </tr>
</table>
</opf:form>
</body>
</html>
