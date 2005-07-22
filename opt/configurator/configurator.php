<pre>
                   __
                __|  |__
 ______   ____ |__    __|
/  __  \ |  _ \   |  |
| |  | | | | \ \  |  |
| |__| | | |_/ /  |  |_
\______/ |  __/    \___| emplate
         | |
Open     |_| ower 
			v. 0.2.0-dev
</pre><span style="font-family: Courier;"><?php

	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		if(is_readable($_POST['src']) && is_writable($_POST['dest']))
		{
			$files = array(
				'opt.class.php', 'opt.compiler.php', 'opt.instructions.php', 'opt.functions.php', 'opt.error.php',
				'opt.filters.php', 'opt.api.php'			
			);
			
			$key = array(
				'AUTOLOAD_MODULES' => 0,
				'CUSTOM_RESOURCES' => 1,
				'DEBUG_CONSOLE' => 2,
				'DISABLED_CC' => 3,
				'ER_PROTECTION' => 4,
				'GZIP_SUPPORT' => 5,
				'NESTING_LEVEL' => 6,
				'OUTPUT_CACHING' => 7			
			);

			foreach($files as $file)
			{
				$src = file($_POST['src'].$file);
				$cutting = 0;
				$nesting = 0;
				foreach($src as $i => $line)
				{
					if(preg_match('/# (\/?)([A-Z_]+)/', trim($line), $found))
					{
						if(isset($key[$found[2]]) && $_POST['f'][$key[$found[2]]] == 'on')
						{
							if($found[1] == '/')
							{
								if($nesting == 1)
								{
									$cutting = 0;
									$nesting--;
								}
								else
								{
									$nesting--;
								}
							}
							else
							{
								$cutting = 1;
								$nesting++;
							}
							unset($src[$i]);
						}
					}
					if($cutting == 1)
					{
						unset($src[$i]);
					}
				}
				file_put_contents($_POST['dest'].$file, implode('', $src));
				echo $file.' has been successfully rebuild...<br/>';
			}
			echo '---------------<br/>Operation completed. Thank you for using OPT!<br/><br/>
			&nbsp;&nbsp;&nbsp;Open Power Template team (Always on time!)</span>';
		}
		else
		{
?><span style="font-family: Courier;">
One of directories you have specified has invalid access rights. Make sure the
"<?=$_POST['src']?>" is readable and the "<?=$_POST['dest']?>" - writable.
</span><?php		
		}	
	}
	else
	{
		if(isset($_GET['help']))
		{
?><span style="font-family: Courier;">
HELP<br/><br/>
OPT configurator is a simple script, which allows you to build your own versions of Open Power Template
parser. This gives you lotf of benefits: you may remove from the source code the features you are not using
or are unncessary. When you upload your application into the web, you will probably also remove all
the debug options. OPT will work faster then.<br/>
You must specify three things: 1/ the directory, where the original OPT code is placed; 2/ the output
directory, where will be placed "new" OPT; 3/ the features you want to keep. Here are their descriptions:

<ul>
 <li><b>Autoloading modules support</b> - Open Power Template may automatically load and install new functions,
 instructions and filters placed in the "/plugins" directory while running the engine. If you are not loading new
 features in this way, you may disable this option.</li>
 <li><b>Custom resources support</b> - resources allow you to load templates from different sources, such as databases.
  If you are using the default resource: "file" (templates in files), you may disable this option.</li>
 <li><b>Debug console</b> - when in debug mode, the console shows much useful information about parsed
  templates, configuration etc. However, if you have already developed the script and you want to upload 
  it into a webserver, you may remove the console from the code, because now it will be unnecessary.</li>
  <li><b>Disabled CC support</b> - OPT allows you not to cache the compiled templates on your HDD ("compile_cache_disabled = 1"), although
   it could be useful only while developing an application. If all your compiled templates are cached, you may
   disable this option.</li>
  <li><b>Error reporting protection</b> - if your server error reporting is set to E_ALL & ~E_NOTICE, you may
  disable this option.</li>
  <li><b>GZip compression support</b> - if you are not using GZip compression feature (for example because
  your PHP is not compiled with the Zlib module), you may disable this option.</li>
  <li><b>Nesting level check</b> - it is strongly recommended NOT TO disable this feature! It checks, whether
  all the instructions were enclosed etc.</li>
  <li><b>Output caching support</b> - if you are not using the output caching feature, you may disable this
  option.</li>
</ul>
</span>
<?php
		}
		else
		{
?><span style="font-family: Courier;">

<form method="post" action="configurator.php">
Welcome to the Open Power Template configuration tool. Here you may configure, which
options and features should be available in your OPT version. Please fill in this
form:<br/><br/>
OPT source directory: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="src"/><br/>
OPT destination directory: <input type="text" name="dest"/><br/><br/>
Features [ <a href="configurator.php?help" target="_blank">Help</a> ]<br/>
<input type="checkbox" name="f[0]" checked="checked"/> Autoloading modules support<br/>
<input type="checkbox" name="f[1]" checked="checked"/> Custom resources support<br/>
<input type="checkbox" name="f[2]" checked="checked"/> Debug console<br/>
<input type="checkbox" name="f[3]" checked="checked"/> Disabled CC support<br/>
<input type="checkbox" name="f[4]" checked="checked"/> Error reporting protection<br/>
<input type="checkbox" name="f[5]" checked="checked"/> GZip compression support<br/>
<input type="checkbox" name="f[6]" checked="checked"/> Nesting level check<br/>	
<input type="checkbox" name="f[7]" checked="checked"/> Output caching support<br/><br/>
<input type="submit" value="Build OPT"/></form>	

<?php	
		}
	}

?></span>
