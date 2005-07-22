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
      'debug_console' => 1, 
      'trace' => 1,
      // Enable XML Syntax mode
      'xmlsyntax_mode' => 1,
      // Strict XML Syntax for IF clauses
      'strict_syntax' => 1
    ); 
    $tpl -> conf_load_array($config); 
    $tpl -> init(); 
    $tpl -> http_headers(OPT_HTML); 

    require('db_connect.php'); 
    $r = mysql_query('SELECT id, name, description FROM products ORDER BY id'); 
    $list = array(); 
    while($row = mysql_fetch_row($r)) 
    { 
      // add the next item 
      $list[] = array( 
          'id' => $row[0], 
          'name' => $row[1],
          'description' => $row[2]
       ); 
    } 

    $tpl -> assign('products', $list);
    if(rand(0, 10) == 7)
    {
    	$tpl -> assign('border' , 'border="1"');
    }
    $tpl -> parse('example11.tpl'); 
    mysql_close();
  }catch(opt_exception $exception){ 
    opt_error_handler($exception); 
  } 
?>
