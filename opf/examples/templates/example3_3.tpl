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
<h1>Step 3</h1>

<opt:bindEvent id="aMessage" name="onMessage" message="msg" position="down">
<ul>
{foreach=@msg; id; simpleMessage}
<li><span class="error">{@simpleMessage}</span></li>
{/foreach}
</ul>
</opt:bind>

<opt:if test="$error_msg">
<p>The form has been incorrectly filled in.</p>
</opt:if>

<opt:opfForm method="post" action="`example3.php`" name="form3">
<table class="form" width="100%">
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
