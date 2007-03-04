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
<h1>Step 1</h1>

<opt:if test="$error_msg">
<p>The form has been incorrectly filled in.</p>
</opt:if>

<opf:form method="post" action="`test_forms_2.php`" name="form">
<table class="form" width="100%">
  <tr>
  <td>Username</td>
  <td><opt:opfInput name="username"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr>
  <td>Password</td>
  <td><opt:opfPassword name="password"><opt:load event="aMessage"/></opt:opfPassword></td> 
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" value="OK"/></td> 
 </tr>
</table>
</opf:form>
</body>
</html>
