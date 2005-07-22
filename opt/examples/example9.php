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

		$tpl -> assign('current_date', date('d.m.Y, H:i:s')); 
		
		// cache this template result for 30 seconds
		$tpl -> cache_status(true, 30);
		$tpl -> parse('example9.tpl'); 
	}catch(opt_exception $exception){ 
		opt_error_handler($exception); 
	}
?>
