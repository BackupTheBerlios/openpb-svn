<?php
	define('OPF_DIR', '../lib/');
	define('OPT_DIR', '../../../opt-dev/www/lib/');
	require(OPT_DIR.'opt.class.php');
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
			if(isset($userTable[$this -> validator -> username]) && $userTable[$this -> validator -> username] == $this -> validator -> password)
			{
				return true;
			}
			$this -> setError('username', 'Statically custom defined message');
			return false;
		} // end process();
		
		public function view()
		{
			if($this -> invalid())
			{
				$this -> tpl -> assign('error_msg', 1);
			}
			$this -> tpl -> parse('test_forms_5.tpl');
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

		$form = new myForm($opf, 'form1');
		if($form -> execute())
		{
			$tpl -> assign('username', $opf -> validator -> username);
			$tpl -> assign('email', $opf -> validator -> email);
			$tpl -> assign('age', $opf -> validator -> age);
			$tpl -> assign('content', $opf -> validator -> content);
			$tpl -> parse('report.tpl');		
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
