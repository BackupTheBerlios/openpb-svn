<?php
	require('./common.php');
	require(OPT_DIR.'opt.class.php');
	require(OPF_DIR.'opf.class.php');

	class myRouter implements iopfRouter
	{
		public function createURL($vars)
		{
			return var_export($vars, true);
		} // end createURL();
	}

	try
	{		
		require('./include.php');
		
		$tpl = new optClass;
		$tpl -> loadConfig('./config.php');
		$tpl -> setMasterPage('master.tpl');

		$validator = new opfValidator();
		$opf = new opfClass($tpl, $validator->defaultParams());
		$opf -> createI18n('./');
		$opf -> setRouter(new myRouter());

		$tpl -> assign('vars', array('somevar' => 'somevalue'));
		$tpl -> parse('test_router_1.tpl');		
	}
	catch(opfException $exception)
	{
		opfErrorHandler($exception);
	}
	catch(optException $exception)
	{
		optErrorHandler($exception);
	}
	
?>
