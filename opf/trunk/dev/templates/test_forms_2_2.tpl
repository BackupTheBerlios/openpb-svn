<html> 
<head> 
  <title>Example 3</title> 
</head> 
<body>
<h1>Example 3</h1>
<h3>Multiple virtual form</h3>
<p>OPF allows to process forms connected into a step-by-step chain. The previously entered data are automatically
handled between the forms. At the end, the programmer sees all the data like they have come from one form.</p>
<hr/>
<h1>Step 2</h1>

<opt:if test="$error_msg">
<p>The form has been incorrectly filled in.</p>
</opt:if>

<opf:form method="post" action="`test_forms_2.php`" name="form">
<table class="form" width="100%">
  <tr>
  <td>E-mail</td>
  <td><opt:opfInput name="email"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr>
  <td>Age</td>
  <td><opt:opfInput name="age"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" value="OK"/></td> 
 </tr>
</table>
</opf:form>
</body>
</html>
