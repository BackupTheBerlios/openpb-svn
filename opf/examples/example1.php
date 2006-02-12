<?php

	define('OPF_DIR', '../lib/');
	define('OPT_DIR', '../../opt/lib/');
	require(OPF_DIR.'opf.class.php');
	
	try
	{
		$context = opfClass::create('config.php');
		$tpl = $context -> getResponse();
		$tpl -> httpHeaders(OPT_HTML);
		
		$headers = array();
		foreach($tpl -> listHeaders() as $header)
		{
			$headers[] = array('body' => $header);	
		}
		$tpl -> assign('header', $headers);
		$tpl -> assign('ip', $context -> getVisit() -> ip);
		$tpl -> assign('address', $context -> getVisit() -> currentAddress);
		$tpl -> assign('ssl', $context -> getVisit() -> secure ? 'Yes' : 'No');
		$tpl -> assign('browser', $context -> getVisit() -> friendly('browser'));
		$tpl -> assign('os', $context -> getVisit() -> friendly('os'));
		$tpl -> parse('example1.tpl');
	
	}
	catch(optException $exception)
	{
		optErrorHandler($exception);
	}
	catch(opfException $exception)
	{
		opfErrorHandler($exception);
	}
?>
