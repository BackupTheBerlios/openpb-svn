<?php require('./directives.php'); ?><html>
<head>
<title>OPT Configurator</title>
</head>
<body>
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
			v. <?php echo OPT_VERSION; ?>
</pre><span style="font-family: Courier;"><?php

	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		if($_POST['src'] == $_POST['dest'])
		{
?><span style="font-family: Courier;">
Please specify DIFFERENT source and destination directory names!
</span><?php
		}
		elseif(is_readable($_POST['src']) && is_writable($_POST['dest']))
		{
			foreach($projectFiles as $file)
			{
				$src = file($_POST['src'].$file);
				if($file == 'opt.compiler.php')
				{
					echo $src[0].'<br/>';
				}
				$cutting = 0;
				$nesting = 0;
				foreach($src as $i => $line)
				{
					if(preg_match('/# (\/?)([A-Z_]+)/', trim($line), $found))
					{
						if(isset($availableDirectives[$found[2]]) && !isset($_POST['f'][$found[2]]))
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
			&nbsp;&nbsp;&nbsp;Open Power Template team</span>';
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
<?php
	foreach($availableDirectives as $directive)
	{
		echo '<li><b>'.$directive['title'].'</b> - '.$directive['description'].'</li>';
	}
?>
</ul>
<a href="configurator.php">Back</a>
</span>
<?php
		}
		else
		{
?><span style="font-family: Courier;">

<form method="post" action="configurator.php">
Welcome to the Open Power Template configuration tool. Here you may configure, which
options and features should be available in your OPT version. Please fill in the
form:<br/><br/>
OPT source directory: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="src"/><br/>
OPT destination directory: <input type="text" name="dest"/><br/><br/>
Features [ <a href="configurator.php?help" target="_blank">Help</a> ]<br/>
Uncheck to remove specified feature.<br/>
<?php
	foreach($availableDirectives as $id => $directive)
	{
		echo '<input type="checkbox" name="f['.$id.']" checked="checked"/> '.$directive['title'].'<br/>';
	}
?>
<br/>
<input type="submit" value="Build OPT"/></form>	
<?php
		}
	}

?></span>
</body>
</html>
