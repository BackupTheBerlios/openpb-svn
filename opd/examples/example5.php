<?php
	define('OPD_DIR', '../lib/');
	require(OPD_DIR.'opd.class.php');

	try
	{
		$sql = opdClass::create('./config.php');
		$sql -> debugConsole = true;
		
		echo '<h2>Time caching prepared statements</h2>';
		
		// Work with the data
		echo '<p>In this example, we prepare a statement that returns one product with the specified ID. Then, we
			bind three times the ID and return the record. The difference between this example and example 3 is the fact
			the data are cached for a specified time peroid.</p>';
	
		// Set the cache names for each execution.
		// Set the name as boolean "false" so that this statement execution is not cached.
		$sql -> setCacheExpire(2, 't_prod1', OPD_CACHE_PREPARE);
		$sql -> setCacheExpire(4, 't_prod2', OPD_CACHE_PREPARE);
		$sql -> setCacheExpire(8, 't_prod3', OPD_CACHE_PREPARE);
		
		// Prepare the statement and get the records
		$stmt = $sql -> prepare('SELECT name FROM products WHERE id=:id');
		
		$stmt -> bindValue(':id', 2);
		$stmt -> execute();		
		while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
		{
			echo 'Product #1: '.$row['name'].'<br/>';
		}
		$stmt -> closeCursor();
	
		$stmt -> bindValue(':id', 3);		
		$stmt -> execute();		
		while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
		{
			echo 'Product #2: '.$row['name'].'<br/>';
		}
		$stmt -> closeCursor();
		
		$stmt -> bindValue(':id', 4);		
		$stmt -> execute();		
		while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
		{
			echo 'Product #3: '.$row['name'].'<br/>';
		}
		$stmt -> closeCursor();
		
		// We must not send next execution statements, because we have not defined the cache states
		// For them.
		
		echo '<p>Queries executed: '.$sql -> getCounter().'</p>';
	}
	catch(PDOException $exception)
	{
		opdErrorHandler($exception);
	}

?>
