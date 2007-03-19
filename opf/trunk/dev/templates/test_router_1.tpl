<html> 
<head> 
  <title>Example 6</title>
  <link rel="stylesheet" href="style.css" type="text/css" /> 
</head> 
<body>
<h1>Example 6</h1>
<h3>Router test</h3>
<hr/>

<p>Standard router call:</p>
<opf:url var1="`foo`" var2="`bar`"/>

<p>Router call with "_load":</p>
<opf:url var1="`foo`" var2="`bar`" _load="$vars"/>

<p>Router call with "_capture":</p>
<opf:url var1="`foo`" var2="`bar`" _capture="address1"/>

<p>Router call with "_capture" and "_load":</p>
<opf:url var1="`foo`" var2="`bar`" _capture="address2" _load="$vars"/>

<p>Captured addresses:</p>
<ul>
	<li>{$opt.capture.address1}</li>
	<li>{$opt.capture.address2}</li>
</ul>
</body>
</html>
