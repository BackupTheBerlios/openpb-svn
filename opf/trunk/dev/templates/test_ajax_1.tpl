<html> 
<head> 
  <title>Test AJAX 1</title>
  <script type="text/javascript" src="js/advajax.js"></script>
  <script type="text/javascript" src="ajax.js"></script>
<script type="text/javascript">
{literal}
onload = function()
{
	DoCheck();
}
{/literal}
</script>
</head> 
<body>
<h3>An AJAX form</h3>
<p>OPF can be used with AJAX. The form is normally processed, but the result is sent back to the browser
as an XML content. In the PHP code, the programmer decides, what to do, if the data are correct, and in
JS, how to tell about it to the user. Remember, if JavaScript is not supported, the form is processed, like
any other.</p>
<hr/>

<div id="results"></div>
<div id="message">
<opt:if test="$error_msg">
<p>The form has been incorrectly filled in.</p>
</opt:if>
</div>

<div id="ajaxform"> 
<opf:form method="post" action="`test_ajax_1.php`" name="form1" id="form1">
<table class="form" width="100%">
  <tr id="f_username">
  <td>Username</td>
  <td><opt:opfInput name="username"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr id="f_password">
  <td>Password</td>
  <td><opt:opfPassword name="password"><opt:load event="aMessage"/></opt:opfPassword></td> 
 </tr>
  <tr id="f_email">
  <td>E-mail</td>
  <td><opt:opfInput name="email"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr id="f_age">
  <td>Age</td>
  <td><opt:opfInput name="age"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr id="f_content">
  <td>Content</td>
  <td><opt:opfTextarea name="content" rows="3" cols="50"><opt:load event="aMessage"/></opt:opfTextarea></td> 
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" value="OK"/></td> 
 </tr>
</table>
</opf:form>
</div>
</body>
</html>
