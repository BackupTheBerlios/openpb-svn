<?php
	define('OPF_DIR', '../lib/');
	define('OPT_DIR', '../../opt/lib/');
	require(OPF_DIR.'opf.class.php');

	$userTable = array(
		'user1' => 'pass1',
		'user2' => 'pass2',
		'user3' => 'pass3'	
	);

	class myForm extends opfVirtualForm
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
			$this -> map('email', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_STRING),
				new opfConstraint(MAP_MATCHTO, OPF_MAIL_PATTERN)
			), false);
			$this -> map('age', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_INTEGER),
				new opfConstraint(MAP_SCOPE, 12, 99)
			), false);
			$this -> map('content', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_TEXT),
				new opfConstraint(MAP_LEN_GT, 10)
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
			$this -> response -> parse('example4.tpl');
		} // end view();
	}

	try
	{

		
		require('./include.php');
		
		$i18n = new i18n;
		$i18n -> loadGroup('opf');
		$context = opfClass::create();
		$context -> loadConfig('config.php');
		
		$form = new myForm($context, $i18n, 'form1');
		if($form -> execute())
		{
			$tpl = $context -> getResponse();
			$request = $context -> getRequest();
			$tpl -> assign('username', $request -> username);
			$tpl -> assign('email', $request -> email);
			$tpl -> assign('age', $request -> age);
			$tpl -> assign('content', $request -> content);
			$tpl -> parse('report.tpl');		
		}
	}
	catch(opfException $exception)
	{
		opfErrorHandler($exception);
	}
	
?>
