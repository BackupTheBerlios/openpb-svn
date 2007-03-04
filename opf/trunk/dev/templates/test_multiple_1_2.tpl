<html>
<head>
	<title>Test multiple 1</title>
</head>
<body>
<h1>Report</h1>
{if test="$fill_form1"}
<p>Form 1 was filled.</p>
<table class="form" width="100%">
  <tr>
  <td>Some text</td>
  <td>{$text}</td> 
 </tr>
</table>
{/if}

{if test="$fill_form2"}
<p>Form 2 was filled.</p>
<table class="form" width="100%">
  <tr>
  <td>Some text</td>
  <td>{$text}</td> 
 </tr>
</table>
{/if}
</body>
</html>
