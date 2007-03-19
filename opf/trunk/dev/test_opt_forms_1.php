<?php
	define('OPF_DIR', '../lib/');
	define('OPT_DIR', '../../../opt-dev/www/lib/');
	require(OPT_DIR.'opt.class.php');
	require(OPF_DIR.'opf.class.php');

	class calendarForm extends opfVirtualForm
	{
		public function create()
		{
			$this -> setRequestMethod(OPF_REQUEST);
		
			$this -> map('day', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_INTEGER),
				new opfConstraint(MAP_GT, 0)
			), true);
			$this -> map('month', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_INTEGER),
				new opfConstraint(MAP_GT, 0)
			), true);
			$this -> map('year', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_INTEGER),
				new opfConstraint(MAP_GT, 0)
			), true);
		} // end create();
		
		public function view()
		{
			$datasource = array(
				'day' => $this->validator->day, 'month' => $this->validator->month, 'year' => $this->validator->year, 'user' => $this->validator->user			
			);
			$datasource['dayValues'] = array(0 => '---');
			for($i = 1; $i <= 31; $i++)
			{
				$datasource['dayValues'][] = $i;
			}
			
			$datasource['monthValues'] = array(0 => '---Choose---',
				'January',
				'February',
				'March',
				'April',
				'May',
				'June',
				'July',
				'August',
				'September',
				'October',
				'November',
				'December'			
			);
			
			$datasource['yearValues'] = array(0 => '---');
			for($i = 2006; $i <= 2012; $i++)
			{
				$datasource['yearValues'][$i] = $i;
			}
			
			$this -> setDatasource($datasource);
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
		
		$form = new calendarForm($opf, 'calendarForm');
		$form -> display();
		if(checkdate($opf->validator->month, $opf->validator->day, $opf->validator->year))
		{
			$tpl -> assign('date', 1);
			$tpl -> assign('day', $opf->validator->day);
			$tpl -> assign('month', $opf->validator->month);
			$tpl -> assign('year', $opf->validator->year);
		}
		
		$tpl -> parse('test_opt_forms_1.tpl');		
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
