<?php
	define('OPD_DIR', '../lib/');
	require('../lib/opd.class.php');

	try
	{

		$pdo = new opdClass('mysql:host=localhost;dbname=test', 'root', 'root');
		$pdo -> debugConsole = true;
		$pdo -> cacheDirectory = './cache/';

		$guard = new opdGuardian($pdo);
		$guard -> lock('people READ');

		$guard -> setCache('people');
		$stmt = $guard -> query('SELECT * FROM people ORDER BY id');
		echo '<ol>';
		while($row = $stmt -> fetch())
		{
			echo '<li>'.$row['name'].' '.$row['surname'].'</li>';
		}
		echo '</ol>';
		$stmt -> closeCursor();

		$guard -> unlock();

	}catch(PDOException $exception)
	{
		die('Exception: '.$exception->getMessage());
	}
?>
