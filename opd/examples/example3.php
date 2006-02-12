<?php
	define('OPD_DIR', '../lib/');
	require(OPD_DIR.'opd.class.php');

	try
	{
		$sql = opdClass::create('./config.php');
		$sql -> debugConsole = true;
		echo '<h2>Caching prepared statements and clearing the cache</h2>';
		
		if(isset($_GET['act']))
		{
			// Clear the cache
			switch($_GET['act'])
			{
				case '1':
					$sql -> clearCache('prod1');
					echo '<p>Product 1 cache has been removed</p>';
					break;
				case '2':
					$sql -> clearCache('prod2');
					echo '<p>Product 2 cache has been removed</p>';
					break;
				case '3':
					$sql -> clearCache('prod3');
					echo '<p>Product 3 cache has been removed</p>';
					break;
				default:
					$sql -> clearCacheGroup('prod{1,2,3}');
					echo '<p>The cache has been removed</p>';
					break;		
			}
			echo '<p><a href="example3.php">Go back</a></p>';		
		}
		else
		{
			// Work with the data
			echo '<p>In this example, we prepare a statement that returns one product with the specified ID. Then, we
				bind three times the ID and return the record. Each of the statement executions is cached in separate
				cache: prod1, prod2 and prod3. Use the links below to clear any of these cache files.</p>';
		
			// Set the cache names for each execution.
			// Set the name as boolean "false" so that this statement execution is not cached.
			$sql -> setCache('prod1', OPD_CACHE_PREPARE);
			$sql -> setCache('prod2', OPD_CACHE_PREPARE);
			$sql -> setCache('prod3', OPD_CACHE_PREPARE);
			
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
			echo '<p><a href="example3.php?act=1">Clear product 1 cache</a></p>';
			echo '<p><a href="example3.php?act=2">Clear product 2 cache</a></p>';
			echo '<p><a href="example3.php?act=3">Clear product 3 cache</a></p>';
			echo '<p><a href="example3.php?act=all">Clear the cache</a></p>';
		}
	}
	catch(PDOException $exception)
	{
		opdErrorHandler($exception);
	}

?>
