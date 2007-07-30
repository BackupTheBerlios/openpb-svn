{* Definition of CSS classes for the components *}
{$opfDesign->setDesign('row', '', 'revert')}
{$opfDesign->setDesign('input', 'insert', 'incorrect')}
{$opfDesign->setDesign('textarea', 'insert', 'incorrect')}
 
<html> 
<head> 
  <title>Example with JavaScript</title>
  <link rel="stylesheet" href="style.css" type="text/css" /> 
  <script type="text/javascript" src="js/mintAjax.js"></script>
  <script type="text/javascript" src="../js/opfMapType.js"></script>
  <script type="text/javascript" src="../js/opf.js"></script>
  <script type="text/javascript" src="../js/opfForm.js"></script>
  <script type="text/javascript" src="../js/opfAjax.js"></script>
  <script type="text/javascript" src="../js/opfEventHandler.js"></script>
  <script type="text/javascript" src="../js/opfStandardContainer.js"></script>
  <script type="text/javascript" src="../js/opfConstraint.js"></script>
  <script type="text/javascript" src="../js/opfError.js"></script>
</head> 
<body onLoad="var _opf = opf.getInstance(); _opf.Load()">
<h1>Example with JavaScript</h1>
<h3>JavaScript validation</h3>
<p>This example shows the JavaScript form validation. Note this is an experimental code and some parts of
it may change in the future.</p>
<hr/>
 
<opt:if test="$error_msg">
<p>The form has been incorrectly filled in.</p>
</opt:if>
 
 <p id="errors"></p>

<opf:form method="post" action="`test_javascript_3.php`" name="form1">
<table class="form" width="100%">
  <tr opf:classfor="username">
  <td>Username</td>
  <td><opt:opfInput name="username" id="username"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr opf:classfor="password">
  <td>Password</td>
  <td><opt:opfPassword name="password2" id="password2"><opt:load event="aMessage"/></opt:opfPassword></td> 
 </tr>
  <tr opf:classfor="password">
  <td>Re-assword</td>
  <td><opt:opfPassword name="password" id="password"><opt:load event="aMessage"/></opt:opfPassword></td> 
 </tr>
  <tr opf:classfor="email">
  <td>E-mail</td>
  <td><opt:opfInput name="email" id="email"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr opf:classfor="age">
  <td>Age</td>
  <td><opt:opfInput name="age" id="age"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr opf:classfor="content">
  <td>Content</td>
  <td><opt:opfTextarea name="content" id="content" rows="3" cols="50"><opt:load event="aMessage"/></opt:opfTextarea></td> 
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" value="OK"/></td> 
 </tr>
</table>
</opf:form>
<opf:javascript form="form1" />

 
</body>
</html> 