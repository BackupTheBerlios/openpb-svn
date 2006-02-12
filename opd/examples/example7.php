<?php
	define('OPD_DIR', '../lib/');
	require(OPD_DIR.'opd.class.php');

	try
	{
		$sql = opdClass::create('./config.php');
		$sql -> debugConsole = true;
		$sql -> exec('INSERT INTO categories (name) VALUES(\'Post\')');
		echo '<h1>Example 7</h1>';
		echo '<p>See the debug console to get to know about the query executed in this example. Note that it is a pop-up
		window, so your browser must not block it in order to see it.</p>';
	}
	catch(PDOException $exception)
	{
		opdErrorHandler($exception);	
	}
?>
