<?php 
	require('../lib/opt.class.php');
 
	try{ 
		$tpl = new opt_template; 
		$config = array( 
			// store the templates in this directory 
			'root' => 'templates/', 
			// the directory for the opt usage 
			'compile' => 'templates_c/',
			'cache' => 'cache/',
			'gzip_compression' => 1, 
			'debug_console' => 0, 
			'trace' => 1 
		); 
		$tpl -> conf_load_array($config); 
		$tpl -> init(); 
		$tpl -> http_headers(OPT_HTML);


		$tpl -> cache_status(true, 30);
		
		if(!$tpl -> is_cached('example10.tpl'))
		{
			require('db_connect.php'); 
			$r = mysql_query('SELECT id, name, description FROM products ORDER BY id');
			$list = array();
			while($row = mysql_fetch_assoc($r)) 
			{ 
				// add the next item 
				$list[] = array( 
					'id' => $row['id'], 
					'name' => $row['name'],
					'description' => $row['description']
				);
			}
			$tpl -> assign('products', $list);
			mysql_close();
		}
		// cache this template result for 30 seconds
		$tpl -> parse('example10.tpl'); 
	}catch(opt_exception $exception){ 
		opt_error_handler($exception); 
	}
?>
