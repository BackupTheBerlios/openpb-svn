<html> 
<head> 
  <title>Example 8</title> 
</head> 
<body>
<h1>Example 8</h1>
<h3>PHP-defined components</h3>
<p>In this example we create the component inside the PHP code. In the template, there is only a place where
we may link it using normal assign() method.</p>
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
