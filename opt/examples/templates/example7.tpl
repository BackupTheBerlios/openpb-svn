<html> 
<head> 
  <title>Example 8</title> 
</head> 
<body>
<h1>Example 8</h1>
<h3>Template-defined components</h3>
<p>In this example whole component is defined inside the template. The application provides the data using standard blocks.</p>
<hr/>
<form method="get" action="example7.php">
Select the category:
{selectComponent datasource="$list"}
	{param name="name" value="selected"}
	{param name="selected" value="$selected"}
	{param name="message" value="$message"}
	{onmessage message="msg" position="down"}
		<font color="red">{@msg}</font>
	{/onmessage}
{/selectComponent}
<input type="submit" value="OK"/>
</body> 
</html>
