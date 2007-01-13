<h3>Compiler</h3>
<form method="post" action="compiler.php?cmd=chdir">
<p>Source template directory: &nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="spl" value="{$splValue}" size="35"/></p>
<p>Compiled template directory: &nbsp;&nbsp;<input type="text" name="cpl" value="{$cplValue}" size="35"/></p>
<p>Plugin directory: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="plg" value="{$plgValue}" size="35"/></p>
<p><input type="submit" value="Change directory"/></p>
</form>

<form method="post" action="compiler.php">
<table border="0" width="100%">
<thead>
  <tr>
   <th width="30">#</th>
   <th>Template file</th>
   <th>Compile date</th>
   <th>Size (src/cpl)</th>
  </tr> 
</thead>
<tbody>
 {section=templates}
  <tr>
   <td><input type="checkbox" name="sel[]" value="{$templates.filename}" /></td>
   <td>{$templates.filename}</td>
   <td>{$templates.cdate}</td>
   <td>{$templates.srcSize} / {$templates.cplSize}</td>
  </tr> 
 {/section}
</tbody>
</table>
<p><input type="submit" name="rall" value="Remove all"/><input type="submit" name="rsel" value="Remove selected"/><input type="submit" name="csel" value="Compile selected"/></p>
</form>
