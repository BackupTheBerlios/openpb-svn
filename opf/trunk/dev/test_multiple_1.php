<?php
	require('./common.php');
	require(OPT_DIR.'opt.class.php');
	require(OPF_DIR.'opf.class.php');

	$userTable = array(
		'user1' => 'pass1',
		'user2' => 'pass2',
		'user3' => 'pass3'	
	);

	class form1 extends opfVirtualForm
	{
		public function create()
		{
			$this -> map('someText', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_STRING),
				new opfConstraint(MAP_LEN_GT, 3)
			), OPF_REQUIRED);
		} // end create();
		
		public function view()
		{
			if($this -> invalid())
			{
				$this -> tpl -> assign('form1_error', 1);
			}
		} // end view();
	}
	
	class form2 extends opfVirtualForm
	{
		public function create()
		{
			$this -> map('someText', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_STRING),
				new opfConstraint(MAP_LEN_GT, 3)
			), OPF_REQUIRED);
		} // end create();
		
		public function view()
		{
			if($this -> invalid())
			{
				$this -> tpl -> assign('form2_error', 1);
			}
			// The displaying command should be located in the last processed form
			$this -> tpl -> parse('test_multiple_1_1.tpl');
		} // end view();
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
		
		$form1 = new form1($opf, 'form1');
		if($form1 -> execute())
		{
			$tpl -> assign('fill_form1', 1);
			$tpl -> assign('text', $opf -> validator -> someText);
			$tpl -> parse('test_multiple_1_2.tpl');		
		}
		else
		{
			$form2 = new form2($opf, 'form2');
			if($form2 -> execute())
			{
				$tpl -> assign('fill_form2', 1);
				$tpl -> assign('text', $opf -> validator -> someText);
				$tpl -> parse('test_multiple_1_2.tpl');
			}
		}
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
