<?php
	define('OPD_DIR', '../lib/');
	require(OPD_DIR.'opd.class.php');

	try
	{
		$sql = opdClass::create('./config.php');
		
		echo '<h2>Iterator interface</h2>';
		
		// Get the categories
		$stmt = $sql -> query('SELECT id, name FROM categories ORDER BY name');
		$stmt -> setFetchMode(PDO::FETCH_ASSOC);
		echo '<ul>';
		foreach($stmt as $row)
		{
			echo '<li>'.$row['id'].': '.$row['name'].'</li>';
		}
		echo '</ul>';
		$stmt -> closeCursor();
		
		echo '<p>Queries executed: '.$sql -> getCounter().'</p>';
	}
	catch(PDOException $exception)
	{
		opdErrorHandler($exception);	
	}
?>
