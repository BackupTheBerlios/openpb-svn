<?php 
  require('../lib/opt.class.php');
  
  $lang = array(
  	'global' => 
  		array(
  			'text1' => 'This is text one',
  			'text2' => 'This is text two',
  			'text3' => 'This is text three',
			'date' => 'Today is %s, good day for fishing'		
  		)
  );
 
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
    $tpl -> conf_load_array($config); 
    $tpl -> init(); 
    $tpl -> http_headers(OPT_HTML);
    
    // init default i18n system
    $tpl -> set_default_i18n($lang);

    $tpl -> assign('current_date', date('d.m.Y')); 
    $tpl -> parse('example4.tpl'); 
  }catch(opt_exception $exception){ 
    opt_error_handler($exception); 
  } 
?>
