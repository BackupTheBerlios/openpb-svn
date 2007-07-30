<?php
	require('./common.php');
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
			), OPF_REQUIRED);
			$this -> map('password', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_STRING),
				new opfConstraint(MAP_LEN_GT, 3),
				new opfConstraint(MAP_TYPE, TYPE_COMPARABLE),
				new opfConstraint(MAP_PERMITTEDCHARS, 'abcdefghijklmnopqrstuvwxyz0123456789')
			), OPF_REQUIRED);
			$this -> map('password2', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_STRING),
				new opfConstraint(MAP_LEN_GT, 3),
				new opfConstraint(MAP_PERMITTEDCHARS, 'abcdefghijklmnopqrstuvwxyz0123456789')
			), OPF_REQUIRED);
			$this -> map('email', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_STRING),
				new opfConstraint(MAP_MATCHTO, OPF_MAIL_PATTERN)
			), OPF_REQUIRED);
			$this -> map('age', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_INTEGER),
				new opfConstraint(MAP_SCOPE, 12, 99)
			), OPF_REQUIRED);
			$this -> map('content', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_TEXT),
				new opfConstraint(MAP_LEN_GT, 10)
			), OPF_REQUIRED);
			
			$this -> setJavascriptEvent('username', JS_BLUR);
			$this -> setJavascriptEvent('password', JS_BLUR);
			$this -> setJavascriptEvent('password2', JS_BLUR);
			$this -> setJavascriptEvent('email', JS_BLUR);
			$this -> setJavascriptEvent('age', JS_BLUR);
			$this -> setJavascriptEvent('content', JS_BLUR);
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
			$this->tpl -> parse('test_javascript_3.tpl');
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
		$opf -> jsDir = '../js/';
		$opf -> jsUrl = '../js/';
		$opf -> createI18n('./');
		$opf -> handleAjax();

		$form = new myForm($opf, 'form1');

		if($form -> execute())
		{
			if(!$opf->visit->ajax)
			{
				$tpl -> assign('username', $opf -> validator -> username);
				$tpl -> assign('email', $opf -> validator -> email);
				$tpl -> assign('age', $opf -> validator -> age);
				$tpl -> assign('content', $opf -> validator -> content);
				//$tpl -> parse('report.tpl');
			}
			else
			{
				
				
			}
		}

		/*$form2 = new myForm($opf, 'drugi');

		if($form2 -> execute())
		{
			if(!$opf->visit->ajax)
			{
				$tpl -> assign('username', $opf -> validator -> username);
				$tpl -> assign('email', $opf -> validator -> email);
				$tpl -> assign('age', $opf -> validator -> age);
				$tpl -> assign('content', $opf -> validator -> content);
				//$tpl -> parse('report.tpl');
			}
		}*/
		//$tpl -> parse('test_javascript_3.tpl');
		//$form->tpl->parse('test_javascript_3.tpl');
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
