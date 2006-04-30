<html> 
<head> 
  <title>Example 2</title> 
</head> 
<body>
<h1>Example 2</h1>
<h3>Virtual form</h3>
<p>This example demonstrates virtual forms - the unique tool for form construction based both on OPF data
processing systems and OPT components, which define the look of the form. In the template, we place some components
inside special opfForm tag. Then, in the PHP code we extend the <i>opfVirtualForm</i> class, and define, how
to process the values from the form. Everything else is handled automatically.</p>
<hr/>

{* Default message event binded for the later use *}
<opt:bindEvent id="aMessage" name="onMessage" message="msg" position="down">
<ul>
{foreach=@msg; id; simpleMessage}
<li><span class="error">{@simpleMessage}</span></li>
{/foreach}
</ul>
</opt:bind>

{* Invalid form message *}
<opt:if test="$error_msg">
<p>The form has been incorrectly filled in.</p>
</opt:if>

{* The form *}
<opt:opfForm method="post" action="`example2.php`" name="form1">
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
  <td>E-mail</td>
  <td><opt:opfInput name="email"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr>
  <td>Age</td>
  <td><opt:opfInput name="age"><opt:load event="aMessage"/></opt:opfInput></td> 
 </tr>
  <tr>
  <td>Content</td>
  <td><opt:opfTextarea name="content" rows="3" cols="50"><opt:load event="aMessage"/></opt:opfTextarea></td> 
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" value="OK"/></td> 
 </tr>
</table>
</opt:opfForm>
</body>
</html>
