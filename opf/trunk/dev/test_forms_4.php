<?php
	require('./common.php');
	require(OPT_DIR.'opt.class.php');
	require(OPF_DIR.'opf.class.php');
	
	/* Some additional code */

	class typeList
	{
		private $types = array(1 =>
			'String',
			'Item');
			
		public function getTypes()
		{
			return $this -> types;
		} // end getTypes();
		
		public function getType($id)
		{
			return $this -> types[$id];
		} // end getType();
	}
	
	class itemList
	{
		private $items = array(1 =>
			'Item 1',
			'Item 2',
			'Item 3',
			'Item 4');

		public function getItems()
		{
			return $this -> items;
		} // end getTypes();
		
		public function getItem($id)
		{
			return $this -> items[$id];
		} // end getType();
	}	
	
	/*
	 * OPEN POWER FORMS
	 */	 

	class myForm1 extends opfVirtualForm
	{
		public function create()
		{
			$this -> map('type', new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_INTEGER),
				new opfConstraint(MAP_GT, 0)
			), OPF_REQUIRED);
		} // end create();
		
		public function view()
		{
			global $typeList;
			if($this -> invalid())
			{
				$this -> tpl -> assign('error_msg', 1);
			}
			// Provide the data source for the type list
			$this -> setDatasource(array('typeValues' => $typeList -> getTypes()));
			
			$this -> tpl -> parse('test_forms_4_1.tpl');
		} // end view();
	}
	
	class myForm2 extends opfVirtualForm
	{
		public function create()
		{
			switch($this -> validator -> type)
			{
				case 1:
					$this -> map('value', new opfStandardContainer(
						new opfConstraint(MAP_TYPE, TYPE_STRING),
						new opfConstraint(MAP_LEN_GT, 0)
					), OPF_REQUIRED);
					break;
				case 2:
					$this -> map('value', new opfStandardContainer(
						new opfConstraint(MAP_TYPE, TYPE_INTEGER),
						new opfConstraint(MAP_GT, 0)
					), OPF_REQUIRED);		
			}
		} // end create();
		
		public function view()
		{
			global $itemList;
			if($this -> invalid())
			{
				$this -> tpl -> assign('error_msg', 1);
			}
			
			// Adding a dynamic component
			// Depending on the type selected in the previous form
			switch($this -> validator -> type)
			{
				case 1:
					$component = new opfInput('value');
					break;
				case 2:
					$component = new opfSelect('value');
					// Provide the data source for the selection list
					$this -> setDatasource(array('valueValues' => $itemList -> getItems()));
			}
			$this -> tpl -> assign('value', $component);

			$this -> tpl -> parse('test_forms_4_2.tpl');
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
		
		$itemList = new itemList;
		$typeList = new typeList;
		
		$form1 = new myForm1($opf, 'form');
		$form2 = new myForm2($opf, 'form');	
		$form1 -> nextStep($form2);

		if($form1 -> execute())
		{
			$tpl -> assign('type', $typeList -> getType($opf -> validator -> type));
			switch($opf->validator->type)
			{
				case 1:
					$tpl -> assign('value', $opf -> validator -> value);
					break;
				case 2:
					$tpl -> assign('value', $itemList -> getItem($opf -> validator -> value));
			}
			$tpl -> parse('test_forms_4_3.tpl');
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
