<?php

	mysql_connect('localhost:3305', 'root', 'root') or die('The MySQL database is not configured and I can\'t run this example. 
		Configure the connection in the db_connect.php file, and create a database using samples.sql file.');
	mysql_select_db('opt') or die('Unknown database.');
?>
