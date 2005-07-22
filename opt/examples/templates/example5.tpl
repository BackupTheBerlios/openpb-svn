<html> 
<head> 
  <title>Example 5</title> 
</head> 
<body>
<h1>Example 5</h1>
<h3>Custom i18n support</h3>
<p>When it comes to the template, the i18n code is exactly the same. The differences are, when we look at the PHP.
Custom i18n system allows you to link OPT with object-based i18n system. You have to write your own text manager,
your own apply function, and of course - a postfilter which create the object inside the template. This could be done
for example by singleton.</p>
<hr/>
{* put current date inside the global@date language block *}
{apply($global@date, $current_date)}
<p>{$global@text1}</p>
<p>{$global@text2}</p>
<p>{$global@text3}</p>
<p>{$global@date}</p>
</body> 
</html>
