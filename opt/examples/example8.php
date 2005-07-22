<?php 
	require('../lib/opt.class.php');

	try{ 
		$tpl = new opt_template; 
		$config = array( 
			// store the templates in this directory 
			'root' => 'templates/', 
			// the directory for the opt usage 
			'compile' => 'templates_c/', 
			'gzip_compression' => 1, 
			'debug_console' => 0, 
			'trace' => 1 
		);

		require('db_connect.php'); 

		$tpl -> conf_load_array($config); 
		$tpl -> init(); 
		$tpl -> http_headers(OPT_HTML);

		$selector = new selectComponent;
		$selector -> set('name', 'selected');

		$selector -> push(0, '---SELECT---');

		$r = mysql_query('SELECT id, name FROM categories ORDER BY id'); 
		while($row = mysql_fetch_assoc($r)) 
		{ 
			// add the next item
			$selector -> push($row['id'], $row['name']);
		}		

		if(isset($_GET['selected']))
		{
			$selector -> set('selected', $_GET['selected']);
			if($_GET['selected'] == 0)
			{
				$selector -> set('message', 'Invalid choice!');
			}
		}
		else
		{
			$selector -> set('selected', 0);
		}

		$tpl -> assign('selector', $selector);

		$tpl -> parse('example8.tpl');
		mysql_close(); 
	}catch(opt_exception $exception){ 
		opt_error_handler($exception); 
	}
?>
