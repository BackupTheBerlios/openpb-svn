<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                          Open Power Forms                              //
  //         Copyright (c) 2005 OpenPB team, http://www.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //

	define('OPF_STATE_RENDER', 1);
	define('OPF_STATE_VALIDATE', 2);
	define('OPF_STATE_STEP_VALIDATE', 3);
	define('OPF_STATE_FIELD_VALIDATE', 4);
	
	class opfShowFormException extends Exception
	{
		private $mode;
	
		public function __construct($mode = true)
		{
			parent::__construct();
			$this -> mode = $mode;
		} // end __construct();
		
		public function invalidData()
		{
			return $this -> mode;
		} // end invalidData();	
	} // end opfShowFormException;

	abstract class opfVirtualForm
	{
		protected $context;
		protected $request;
		protected $response;
		protected $visit;
		protected $design;
		protected $state;
		public $i18n;		// If a component needs

		private $datasource;
		private $dst = 0;
		private $fields = array();
		private $nullValues = array();
		private $errorGroups = array();
		private $errorTexts = array();
		private $fieldCounter = -1;
		private $nextStep = NULL;
		
		// Data for OPT
		public $name;
		public $step = NULL;
		public $items = array();
		public $errorMessages = array();
		public $valid = true;

		final public function __construct(opfClass $context, ioptI18n $i18n, $name)
		{
			$this -> i18n = $i18n;
			$this -> name = $name;
			$this -> context = $context;
			$this -> request = $context -> getRequest();
			$this -> response = $context -> getResponse();
			$this -> visit = $context -> getVisit();
			$this -> design = $context -> getDesign();
		} // end __construct();
		
		abstract public function create();
		abstract public function view(opfShowFormException $showForm);

		public function process()
		{
			return true;
		} // end process();
		
		final protected function map($name, iopfConstraintContainer $constraints, $null, $errorMsgGroup = NULL, $errorMsgText = NULL)
		{
			$this -> fields[$name] = $constraints;
			$this -> nullValues[$name] = $null;
			
			if(!is_null($errorMsgGroup) && !is_null($errorMsgText))
			{
				$this -> errorGroups[$name] = $errorMsgGroup;
				$this -> errorTexts[$name] = $errorMsgText;
			}
		} // end map();
		
		final public function setDatasource($dataSource)
		{
			if(is_array($dataSource))
			{
				$this -> dataSource = $dataSource;
				$this -> dst = 1;
				return true;
			}
			elseif(is_object($dataSource))
			{
				$this -> dataSource = $dataSource;
				$this -> dst = 2;
				return true;
			}
			return false;
		} // end setDatasource();
		
		final public function nextStep(opfVirtualForm $form)
		{
			if(is_null($this -> nextStep))
			{
				$this -> nextStep = $form;
				return true;
			}
			return false;
		} // end nextStep();
		
		final public function getValue($name, $listData = false)
		{
			if($this -> state == OPF_STATE_VALIDATE && $listData == false)
			{		
				return htmlspecialchars($_POST[$name]);
			}
			else
			{
				switch($this -> dst)
				{
					case 0:
						if($listData)
						{
							return array();
						}
						return '';
					case 1:
						return $this -> dataSource[$name];
					case 2:
						return $this -> dataSource -> $name;
				}
			}
		} // end getValue();
		
		final protected function setError($name, $group, $id, $args = NULL)
		{
			if(!is_null($args))
			{
				$this -> errorMessages[$name][] = $this -> i18n -> putApply($group, $id, $args);
			}
			else
			{
				$this -> errorMessages[$name][] = $this -> i18n -> put($group, $id);
			}
		} // end setError();
		
		final public function getClass($name)
		{
			$class = $this -> getCssClass($name);
			if($class !== false)
			{
				return 'class="'.$class.'"';
			}
			else
			{
				return '';
			}
		} // end getClass();
		
		final public function __get($name)
		{
			if($name == 'cssClass')
			{
				$class = $this -> getCssClass(key($this -> fields));
				next($this -> fields);
				if($class !== false)
				{
					return 'class="'.$class.'"';
				}
				else
				{
					return '';
				}				
			}
			return '';
		} // end __get();
		
		final public function execute()
		{
			$this -> create();
			$info = $this -> context -> getDynamicFormInfo($this -> name, ($this -> nextStep !== null));
			
			if(!$this -> visit -> ajax)
			{
				if(!is_null($this -> nextStep))
				{
					$items = array();
					$this -> state = OPF_STATE_STEP_VALIDATE;
					return $this -> stepExecute($this, $info, 1, $items);						
				}
				try
				{	
					if($info === false)
					{
						throw new opfShowFormException(false);				
					}
					else
					{
						$this -> state = OPF_STATE_VALIDATE;
						return $this -> stateValidate();
					}				
				}
				catch(opfShowFormException $exception)
				{
					if($exception -> invalidData())
					{
						$this -> invalidData = true;
					}
					else
					{
						$this -> state = OPF_STATE_RENDER;
						$this -> invalidData = false;
					}
					$this -> stateRender($exception);
					return false;
				}		
			}		
		} // end execute();
		
		final public function stepExecute($starter, $info, $step, &$items)
		{
			$this -> step = $step;
			if($step < $info['step'])
			{
				try
				{		
					$this -> state = OPF_STATE_VALIDATE;
					$this -> create();
					$this -> stateValidate();
					$items = array_merge($items, $this -> getItems());

					if(is_null($this -> nextStep))
					{
						return true;
					}
					return $this -> nextStep -> stepExecute($this, $info, $step + 1, $items);
				}
				catch(opfShowFormException $exception)
				{
					$this -> addPreviousItems($items);
					if($exception -> invalidData())
					{
						$this -> invalidData = true;
					}
					else
					{
						$this -> state = OPF_STATE_RENDER;
						$this -> invalidData = false;
					}
					$this -> stateRender($exception);
					return false;
				}
			}
			else
			{
				$this -> state = OPF_STATE_RENDER;
				$this -> invalidData = false;
				$this -> addPreviousItems($items);
				$this -> stateRender(new opfShowFormException(false));
			}
		} // end stepExecute();
		
		/*
		 * Multi-step forms
		 */
		
		public function addPreviousItems(Array $items)
		{
			foreach($items as $name => $value)
			{
				$this -> items[$name] = $value;
			}		
		} // end addPreviousItems();
		
		public function getItems()
		{
			$result = array();
			foreach($this -> fields as $name => $void)
			{
				$result[$name] = htmlspecialchars($this -> request -> get($name));			
			}
			return $result;
		} // end getItems();
		
		/*
		 * INTERNAL OPF METHODS
		 */
		
		private function stateRender(opfShowFormException $exception)
		{
			// $this -> generateJavaScriptCode();
			$this -> response -> assign($this -> name, $this);
			reset($this -> fields);
			//next($this -> fields);
			$this -> view($exception);
			return 1;	
		} // end stateRender();

		private function stateValidate()
		{
			$ok = $this -> stateStepValidate();
			$this -> valid = $ok;
			if(!$ok)
			{
				throw new opfShowFormException();
			}
			
			return $ok;
		} // end stateValidate();
		
		private function stateStepValidate()
		{
			$ok = true;
			foreach($this -> fields as $name => $container)
			{
				$this -> request -> map($name, OPF_POST, $container);

				if(!$this -> nullValues[$name] && !$container -> valid())
				{
					$this -> errorMessages[$name] = array();
					$ok = false;
					
					if(isset($this -> errorGroups[$name]))
					{
						$this -> errorMessages[$name][] = $this -> i18n -> put($this -> errorGroups[$name], $this -> errorTexts[$name]);					
					}
					else
					{
						// If no null values allowed for this field, we have to throw an error
						while($error = $container -> error())
						{
							if(isset($error['args']))
							{
								if($error['id'] == 'constraint_type')
								{
									$error['args'][1] = $this -> i18n -> put($this -> context -> i18nGroup, $error['args'][1]); 
								}
								$this -> errorMessages[$name][] = $this -> i18n -> putApply($this -> context -> i18nGroup, $error['id'], $error['args']);
							}
							else
							{							
								$this -> errorMessages[$name][] = $this -> i18n -> put($this -> context -> i18nGroup, $error['id']);
							}
						}
					}
				}
			}
			return $ok && $this -> process();
		} // end stateStepValidate();
		
		private function getCssClass($item)
		{
			if(isset($this -> errorMessages[$item]))
			{
				return $this -> design -> getClass('row', false);
			}
			else
			{
				return $this -> design -> getClass('row', true);
			}
		} // end getCssClass();
		
		public function printVals()
		{
			foreach($this as $name => $value)
			{
				echo $name.' - '.$value.'<br/>';
			}		
		} 
	}
	
	// CSS component design manager
	// Necessary, because HTML forms were developed by...
	// Well... you see, how they were "brilliant" not allowing to distinguish between (for example) text and radio fields
	class opfDesign
	{
		private $designInfo;
		
		public function __construct()
		{
			$this -> designInfo = array('base' => array(
				'valid' => '',
				'invalid' => ''
			));
		} // end __construct();
		
		public function loadDesign($filename)
		{
			$this -> designInfo = @parse_ini_file($filename, true);
			if(!is_array($this -> designInfo))
			{
				throw new opfException('Design file '.$filename.' not found.');
			}
		} // end loadDesign();
		
		public function setDesign($component, $valid, $invalid)
		{
			$this -> designInfo[$component] = array(
				'valid' => $valid,
				'invalid' => $invalid
			);		
		} // end setDesign();
		
		public function getClass($component, $valid)
		{
			if(!isset($this -> designInfo[$component]))
			{
				$di = $this -> designInfo['base'];
			}
			else
			{
				$di = $this -> designInfo[$component];
			}
			if($valid && strlen($di['valid']) > 0)
			{
				return $di['valid'];
			}
			elseif(!$valid && strlen($di['invalid']) > 0)
			{
				return $di['invalid'];
			}
			return false;
		} // end getClass();

	} // end opfDesign;
?>
