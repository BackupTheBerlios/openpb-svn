<script language="JavaScript">
opt_console = window.open("","OPT debug console","width=680,height=350,resizable,scrollbars=yes");
opt_console.document.write("<HTML><TITLE>OPT debug console</TITLE><BODY bgcolor=#ffffff><h1>OPT DEBUG CONSOLE</h1>");
opt_console.document.write('<table border="0" width="100%">');
{section=config}
opt_console.document.write('<tr><td width="25%" bgcolor="#DDDDDD"><b>{$config.name}</b></td>'); 
opt_console.document.write('<td width="75%" bgcolor="#EEEEEE">{$config.value}</td></tr>');
{/section}
opt_console.document.write('</table><table border="0" width="100%"><tr><td width="25%" bgcolor="#CCCCCC"><b>Loaded file</b></td>'); 
opt_console.document.write('<td width="25%" bgcolor="#CCCCCC"><b>Problems</b></td>');
opt_console.document.write('<td width="25%" bgcolor="#CCCCCC"><b>Compile cache status</b></td>');
opt_console.document.write('<td width="25%" bgcolor="#CCCCCC"><b>Execution time</b></td></tr>');
{section=files}
opt_console.document.write('<tr><td width="25%" bgcolor="#EEEEEE">{$files.name}</td>'); 
opt_console.document.write('<td width="25%" bgcolor="#EEEEEE"><b>{$files.problems}</b></td>');
opt_console.document.write('<td width="25%" bgcolor="#EEEEEE">{$files.cache}</td>');
opt_console.document.write('<td width="25%" bgcolor="#EEEEEE">{$files.exec} s</td></tr>');
{/section}
opt_console.document.write('</table>');
</script>
