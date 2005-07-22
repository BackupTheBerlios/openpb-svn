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
    $tpl -> conf_load_array($config); 
    $tpl -> init(); 
    $tpl -> http_headers(OPT_HTML); 

    require('db_connect.php');
    
    $r = mysql_query('SELECT id, name FROM categories ORDER BY id');
    $categories = array();
    while($row = mysql_fetch_assoc($r))
    {
    	$categories[$row['id']] = array(
			'name' => $row['name']
		);    
    }    
    
    $r = mysql_query('SELECT id, name, description, category FROM products ORDER BY category, id'); 
    $products = array(); 
    while($row = mysql_fetch_assoc($r)) 
    { 
      // add the next item 
      $products[$row['category']][] = array( 
          'id' => $row['id'], 
          'name' => $row['name'],
          'description' => $row['description']
       ); 
    } 

	$tpl -> assign('categories', $categories);
    $tpl -> assign('products', $products); 
    $tpl -> parse('example3.tpl'); 
    mysql_close();
  }catch(opt_exception $exception){ 
    opt_error_handler($exception); 
  } 
?>
