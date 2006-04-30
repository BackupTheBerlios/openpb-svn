<?php
	define('OPF_DIR', '../lib/');
	define('OPT_DIR', '../../opt/lib/');
	require(OPF_DIR.'opf.class.php');

	$userTable = array(
		'user1' => 'pass1',
		'user2' => 'pass2',
		'user3' => 'pass3'	
	);

	class myForm1 extends opfVirtualForm
	{
		public function create()
		{
			$this -> map('username', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_STRING),
				new opfConstraint(MAP_LEN_GT, 3)
			), false);
			$this -> map('password', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_STRING),
				new opfConstraint(MAP_LEN_GT, 3),
				new opfConstraint(MAP_PERMITTEDCHARS, 'abcdefghijklmnopqrstuvwxyz0123456789')
			), false);
		} // end create();
		
		public function process()
		{
			global $userTable;
			
			if(isset($userTable[$this -> request -> username]) && $userTable[$this -> request -> username] == $this -> request -> password)
			{
				return true;
			}
			$this -> setError('username', 'opf', 'invaliduser');
			return false;
		} // end process();
		
		public function view(opfShowFormException $showForm)
		{
			if($showForm -> invalidData())
			{
				$this -> response -> assign('error_msg', 1);
			}
			$this -> response -> parse('example3_1.tpl');
		} // end view();
	}
	
	class myForm2 extends opfVirtualForm
	{
		public function create()
		{
			$this -> map('email', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_STRING),
				new opfConstraint(MAP_MATCHTO, OPF_MAIL_PATTERN)
			), false);
			$this -> map('age', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_INTEGER),
				new opfConstraint(MAP_SCOPE, 12, 99)
			), false);
		} // end create();
		
		public function view(opfShowFormException $showForm)
		{
			if($showForm -> invalidData())
			{
				$this -> response -> assign('error_msg', 1);
			}
			$this -> response -> parse('example3_2.tpl');
		} // end view();
	}
	
	class myForm3 extends opfVirtualForm
	{
		public function create()
		{
			$this -> map('content', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_TEXT),
				new opfConstraint(MAP_LEN_GT, 10)
			), false);
		} // end create();
		
		public function view(opfShowFormException $showForm)
		{
			if($showForm -> invalidData())
			{
				$this -> response -> assign('error_msg', 1);
			}
			$this -> response -> parse('example3_3.tpl');
		} // end view();
	}

	try
	{		
		require('./include.php');
		
		$i18n = new i18n;
		$i18n -> loadGroup('opf');
		$context = opfClass::create();
		$context -> loadConfig('config.php');
		$tpl -> alwaysRebuild = true;
		$tpl = $context -> getResponse();

		$form1 = new myForm1($context, $i18n, 'form1');
		$form2 = new myForm2($context, $i18n, 'form2');
		$form3 = new myForm3($context, $i18n, 'form3');
		
		$form1 -> nextStep($form2);
		$form2 -> nextStep($form3);
		if($form1 -> execute())
		{
			$tpl -> assign('username', $context ->getRequest() -> username);
			$tpl -> assign('email', $context ->getRequest() -> email);
			$tpl -> assign('age', $context ->getRequest() -> age);
			$tpl -> assign('content', $context ->getRequest() -> content);
			$tpl -> parse('report.tpl');	
		}	
	}
	catch(opfException $exception)
	{
		opfErrorHandler($exception);
	}
	
?>
