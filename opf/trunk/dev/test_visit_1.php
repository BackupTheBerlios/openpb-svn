<?php

	require('./common.php');
	require(OPT_DIR.'opt.class.php');
	require(OPF_DIR.'opf.class.php');

	try
	{
		$tpl = new optClass;
		$tpl -> loadConfig('./config.php');

		$validator = new opfValidator();
		$opf = new opfClass($tpl, $validator->defaultParams());

		$tpl -> httpHeaders(OPT_HTML);

		$tpl -> assign('ip', $opf -> visit -> ip);
		$tpl -> assign('address', $opf -> visit -> currentAddress);
		$tpl -> assign('ssl', $opf -> visit -> secure ? 'Yes' : 'No');
		$tpl -> assign('browser', $opf -> visit -> friendly('browser'));
		$tpl -> assign('os', $opf -> visit -> friendly('os'));
		$tpl -> assign('languages', $opf->visit->languages);
		$tpl -> assign('settings', $opf->visit->settings);
		$tpl -> parse('test_visit_1.tpl');
	
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
