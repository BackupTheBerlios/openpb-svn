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
		$r = mysql_query('SELECT id, name FROM categories ORDER BY id'); 
		$list = array(0 => array(
			'value' => 0,
			'desc' => '---SELECT---',
			'selected' => false		
		)); 
		while($row = mysql_fetch_assoc($r)) 
		{ 
			// add the next item 
			$list[] = array( 
				'value' => $row['id'], 
				'desc' => $row['name'],
				'selected' => false
			);
		}

		$tpl -> conf_load_array($config); 
		$tpl -> init(); 
		$tpl -> http_headers(OPT_HTML);

		if(isset($_GET['selected']))
		{
			$tpl -> assign('selected', $_GET['selected']);
			if($_GET['selected'] == 0)
			{
				$tpl -> assign('message', 'Invalid choice!');
			}
		}
		else
		{
			$tpl -> assign('selected', 0);
		}

		$tpl -> assign('list', $list);

		$tpl -> parse('example7.tpl');
		mysql_close();
	}catch(opt_exception $exception){ 
		opt_error_handler($exception); 
	}
?>
