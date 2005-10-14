<?php 
	define('OPT_DIR', '../lib/');
	require('../lib/opt.api.php');
	
	class optParser extends optApi
	{
		protected function doInclude($name, $nestingLevel)
		{
			// actually do nothing at the moment
		} // end doInclude();
	}

	try{ 
		$tpl = new optParser; 
		$tpl -> root = './templates/';
		$tpl -> assign('current_date', date('d.m.Y'));
		$tpl -> doParse('example14.tpl');
	}
	catch(optException $exception)
	{ 
		optErrorHandler($exception); 
	}
?>
