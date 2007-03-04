<html> 
<head> 
  <title>Test forms 4</title> 
</head> 
<body>
<h3>Dynamic forms</h3>
<p>Using the OPT components, you can easily create dynamic forms, where the type of form elements is defined in the PHP
script, not in the template. In this example, we select the data type first. In the next form, we see different component
depending on the type selected.</p>
<hr/>
<h1>Step 1</h1>

<opt:if test="$error_msg">
<p>The form has been incorrectly filled in.</p>
</opt:if>

<opf:form method="post" action="`test_forms_4.php`" name="form">
<table class="form" width="50%">
  <tr>
  <td>Data type</td>
  <td><opt:opfSelect name="type"><opt:load event="aMessage"/></opt:opfSelect></td> 
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" value="Next"/></td> 
 </tr>
</table>
</opf:form>
</body>
</html>
