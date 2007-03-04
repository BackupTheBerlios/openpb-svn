<?php
	// Additional code file for the examples
	
	function showMessage($message, $example)
	{
		global $tpl;
		
		$tpl -> assign('message', $message);
		$tpl -> assign('example', $example);
		$tpl -> parse('message.tpl');
		die();
	} // end showMessage();

?>
