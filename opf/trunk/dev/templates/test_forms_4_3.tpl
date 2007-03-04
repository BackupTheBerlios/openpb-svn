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
<h1>The result</h1>


<table class="form" width="50%">
  <tr>
  <td>Type</td>
  <td>{$type}</td> 
 </tr>
  <tr>
  <td>Value</td>
  <td>{$value}</td> 
 </tr>
</table>
</body>
</html>
