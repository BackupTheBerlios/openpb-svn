<?php
	define('OPF_DIR', '../lib/');
	define('OPT_DIR', '../../opt/lib/');
	require(OPF_DIR.'opf.class.php');
	
	/*
	 * Some additional code
	 */	

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
			), false);
		} // end create();
		
		public function view(opfShowFormException $showForm)
		{
			global $typeList;
			if($showForm -> invalidData())
			{
				$this -> response -> assign('error_msg', 1);
			}
			// Provide the data source for the type list
			$this -> setDatasource(array('typeValues' => $typeList -> getTypes()));
			
			$this -> response -> parse('example5_1.tpl');
		} // end view();
	}
	
	class myForm2 extends opfVirtualForm
	{
		public function create()
		{
			switch($this -> request -> type)
			{
				case 1:
					$this -> map('value', new opfStandardContainer(
						new opfConstraint(MAP_TYPE, TYPE_STRING),
						new opfConstraint(MAP_LEN_GT, 0)
					), false);
					break;
				case 2:
					$this -> map('value', new opfStandardContainer(
						new opfConstraint(MAP_TYPE, TYPE_INTEGER),
						new opfConstraint(MAP_GT, 0)
					), false);		
			}
		} // end create();
		
		public function view(opfShowFormException $showForm)
		{
			global $itemList;
			if($showForm -> invalidData())
			{
				$this -> response -> assign('error_msg', 1);
			}
			
			// Adding a dynamic component
			// Depending on the type selected in the previous form
			switch($this -> request -> type)
			{
				case 1:
					$component = new opfInput('value');
					break;
				case 2:
					$component = new opfSelect('value');
					// Provide the data source for the selection list
					$this -> setDatasource(array('valueValues' => $itemList -> getItems()));
			}
			$this -> response -> assign('value', $component);

			$this -> response -> parse('example5_2.tpl');
		} // end view();
	}

	try
	{	
		require('./include.php');
		
		$i18n = new i18n;
		$i18n -> loadGroup('opf');
		$context = opfClass::create();
		$context -> loadConfig('./config.php');
		
		$itemList = new itemList;
		$typeList = new typeList;
		
		$form1 = new myForm1($context, $i18n, 'form1');
		$form2 = new myForm2($context, $i18n, 'form2');	
		$form1 -> nextStep($form2);

		if($form1 -> execute())
		{
			$tpl = $context -> getResponse();
			$request = $context -> getRequest();
			$tpl -> assign('type', $typeList -> getType($request->type));
			switch($request->type)
			{
				case 1:
					$tpl -> assign('value', $request -> value);
					break;
				case 2:
					$tpl -> assign('value', $itemList -> getItem($request -> value));
			
			}
			$tpl -> parse('example5_3.tpl');		
		}
	}
	catch(opfException $exception)
	{
		opfErrorHandler($exception);
	}
	
?>
