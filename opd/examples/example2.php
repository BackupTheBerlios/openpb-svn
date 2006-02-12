<?php
	define('OPD_DIR', '../lib/');
	require(OPD_DIR.'opd.class.php');

	try
	{
		$sql = opdClass::create('./config.php');
		$sql -> debugConsole = true;
		
		// Get the categories
		$sql -> setCache('categories');
		$stmt = $sql -> query('SELECT id, name FROM categories ORDER BY name');
		$categories = array();
		while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
		{
			$categories[$row['id']]['name'] = $row['name'];
		}
		$stmt -> closeCursor();
		
		// Get the products
		$sql -> setCache('products');
		$stmt = $sql -> query('SELECT p.id, p.name, p.description, p.category FROM products p, categories c WHERE c.id=p.category ORDER BY c.name, p.id');
		$currentCategory = 0;
		
		while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
		{
			$categories[$row['category']]['products'][] = $row;		
		}
		$stmt -> closeCursor();
		// OPD ended the work
		// Now let's show the result
		echo '<h2>Product list with OPD caching</h2>';
		echo '<ul>';
		foreach($categories as $cat)
		{
			echo '<li>'.$cat['name'];
			if(isset($cat['products']))
			{
				echo '<ul>';
				foreach($cat['products'] as $product)
				{
					echo '<li><b>'.$product['name'].'</b>: '.$product['description'].'</li>';				
				}
				echo '</ul>';
			}
			echo '</li>';		
		}
		echo '</ul>';
		
		echo '<p>Queries executed: '.$sql -> getCounter().'</p>';
	}
	catch(PDOException $exception)
	{
		opdErrorHandler($exception);	
	}
?>
