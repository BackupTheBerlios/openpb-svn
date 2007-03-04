<?php
	define('OPD_DIR', '../lib/');
	require('../lib/opd.class.php');

	try
	{

	$pdo = new opdClass('mysql:host=localhost;dbname=test', 'root', 'root');
	$pdo -> debugConsole = true;

	if($_GET['connect'] == 1)
	{
		$pdo -> exec('UPDATE `table` SET `field` = 1');
		echo '<p>Connected</p>';
	}
	else
	{
		echo '<p>Not connected</p>';
	}

	}catch(PDOException $exception)
	{
		die('Exception: '.$exception->getMessage());
	}
?>
