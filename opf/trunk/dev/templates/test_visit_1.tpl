<html> 
<head> 
  <title>User detection</title> 
</head> 
<body>
<p>Your IP: {$ip}</p>
<p>Page address: {$address}</p>
<p>Browser: {$browser}</p>
<p>Operating system: {$os}</p>
<p>Secure connection: {$ssl}</p>
<h3>Options</h3>
<p>RichEdit: {$settings.richedit}</p>
<p>DOM: {$settings.dom}</p>
<p>gZip: {$settings.gzip}</p>
<p>XHTML: {$settings.xhtml}</p>
<h3>Languages</h3>

<ul>
{foreach=$languages; name}
	<li>{@name}</li>
{/foreach}
</ul>
</body> 
</html>
