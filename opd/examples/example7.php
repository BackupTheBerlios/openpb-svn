<?php
	define('OPD_DIR', '../lib/');
	require(OPD_DIR.'opd.class.php');

	try
	{
		$sql = opdClass::create('./config.php');
		$sql -> debugConsole = true;
		$sql -> exec('INSERT INTO categories (name) VALUES(\'Post\')');
	}
	catch(PDOException $exception)
	{
		opdErrorHandler($exception);	
	}
?>
