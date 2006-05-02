<html> 
<head> 
  <title>Example 5</title> 
</head> 
<body>
<h1>Example 5</h1>
<h3>Dynamic forms</h3>
<p>Using the OPT components, you can easily create dynamic forms, where the type of form elements is defined in the PHP
script, not in the template. In this example, we select the data type first. In the next form, we see different component
depending on the type selected.</p>
<hr/>
<h1>Step 2</h1>

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

<opt:opfForm method="post" action="`example5.php`" name="form2">
<table class="form" width="50%">
  <tr>
  <td>Value</td>
  <td><opt:component id="$value" name="value"><opt:load event="aMessage"/></opt:component></td> 
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" value="Next"/></td> 
 </tr>
</table>
</opt:opfForm>
</body>
</html>
