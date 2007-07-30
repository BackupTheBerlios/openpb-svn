<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                          Open Power Forms                              //
  //         Copyright (c) 2007 OpenPB team, http://www.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //

	if(!defined('OPF_DIR'))
	{
		define('OPF_DIR', './');
	}
	
	define('OPF_VERSION', '1.0.0-dev');
	// Directives, etc.
	define('OPF_AJAX_EXCEPTION', 2);
	define('OPF_AJAX_ERROR404', 1);
	define('OPF_AJAX_IGNORE', 0);
	
	define('OPF_POST', 0);
	define('OPF_GET', 1);
	define('OPF_REQUEST', 2);
	define('OPF_COOKIE', 3);
	define('OPF_FILE', 4);
	
	define('OPF_STATE_RENDER', 1);
	define('OPF_STATE_VALIDATE', 2);
	define('OPF_STATE_STEP_VALIDATE', 3);
	define('OPF_STATE_FIELD_VALIDATE', 4);
	// Error messages
	define('OPF_E_INVALID_CONFIG', 1);
	define('OPF_E_INVALID_AJAX', 2);
	define('OPF_E_INVALID_REDIRECT', 3);
	define('OPF_E_INVALID_CONTENT', 4);
	define('OPF_E_INVALID_DATATYPE', 5);
	define('OPF_E_NO_FORM_TAG', 6);
	define('OPF_E_NOT_IN_FORM', 7);
	define('OPF_E_INVALID_ARGS', 8);
	define('OPF_E_NOT_WRITEABLE', 9);
	define('OPF_E_CONTAINER_NOT_DEFINED', 10);
	
	define('OPF_REQUIRED', 0);
	define('OPF_LAZY_OPTIONAL', 1);
	define('OPF_OPTIONAL', 2);

	interface iopfConstraintContainer
	{
		public function __construct();
		public function setOpfInstance(opfClass $opf);
		public function process($name, $type, &$value);
		public function createJavaScript($name);
		public function valid();
		public function error();
	}

	interface iopfConstraint
	{
		public function __construct($type);
		public function setOpfInstance(opfClass $opf);
		public function process($name, $type, &$value);
		public function createJavaScript($name);
		public function valid();
		public function error();
	}

	interface iopfValidator
	{
		public function setOpfInstance(opfClass $opf);
		public function map($name, $type, iopfConstraintContainer $container);
		public function __get($name);
		public function get($name);
	}
	
    interface iopfRouter
    {
        public function createURL($variables);	
    }

	require(OPF_DIR.'opf.components.php');
	require(OPF_DIR.'opf.visit.php');
	require(OPF_DIR.'opf.constraints.php');
	
	if(!defined('NO_OPF_LANGUAGE_SYSTEM'))
	{
		require(OPF_DIR.'opf.language.php');		
	}

	// The main class
	class opfClass
	{
		// OPF configuration
		public $i18nGroup = 'opf';
		public $magicQuotes = false;
		public $invalidAjax = OPF_AJAX_IGNORE;
		public $prefix = 'opf';
		public $jsDir = NULL;
		public $jsUrl = NULL;
	
		// OPF elements
		public $tpl;
		public $router;
		public $validator;
		public $visit;
		public $design;
		public $i18n;

		// Dynamic forms
		private $dynamicForms = false;
		private $formData = array();
		
		// Other
		private $xmlLock = true;
		
		public function __construct(optClass $tpl, iopfValidator $validator)
		{
			$this -> router = NULL;
			$this -> design = new opfDesign;
			$this -> visit = new opfVisit;
			$this -> tpl = $tpl;
			$this -> validator = $validator;
			
			// Object configuration
			$tpl -> opf = $this;
			$tpl -> control[] = 'opfCall';
			$tpl -> control[] = 'opfUrl';
			$tpl -> control[] = 'opfForm';
			$tpl -> control[] = 'opfJavascript';			
			$tpl -> components['opfInput'] = 1;
			$tpl -> components['opfPassword'] = 1;
			$tpl -> components['opfTextarea'] = 1;
			$tpl -> components['opfQuestion'] = 1;
			$tpl -> components['opfLabel'] = 1;
			$tpl -> components['opfSelect'] = 1;
			$tpl -> components['opfRadio'] = 1;
			$tpl -> components['opfFile'] = 1;
			$tpl -> components['opfRetypePassword'] = 1;
			$tpl -> components['opfCheckQuestion'] = 1;
			$tpl -> instructionFiles[] = OPF_DIR.'opf.template.php';

			$tpl -> registerNamespace('opf');
			$tpl -> assign('opfDesign', $this -> design);
			$tpl -> opf = $this;
			
			$validator -> setOpfInstance($this);
		} // end __construct();
		
		public function setRouter(iopfRouter $router)
		{
			$this -> router = $router;
		} // end setRouter();
		
		public function setI18n(ioptI18n $i18n)
		{
			$this -> i18n = $i18n;
		} // end setI18n();
		
		public function createI18n($path)
		{
			$this -> i18n = new opfI18n($path);
			$this -> i18n -> loadGroup($this -> i18nGroup);
		} // end createI18n();
		
		
		public function getDynamicFormInfo($name, $step = false)
		{
			if($this -> dynamicForms == false)
			{
				// A form request check required
				if($this -> visit -> requestMethod == OPF_POST)
				{
					if($this -> validator -> map($this->prefix.'FormName', OPF_POST, new opfStandardContainer(
						new opfConstraint(MAP_TYPE, TYPE_STRING),
						new opfConstraint(MAP_LEN_GT, 0)	
					)))
					{
						$this -> formData = array('name' => $this -> validator -> opfFormName);
						$this -> dynamicForms = true;
						if($this -> validator -> map($this->prefix.'Step', OPF_POST, new opfStandardContainer(
							new opfConstraint(MAP_TYPE, TYPE_INTEGER),
							new opfConstraint(MAP_GT, 0)			
						)))
						{
							$this -> formData['step'] = $this -> validator -> opfStep;
						}
						else
						{
							$this -> formData['step'] = 1;
						}
					}
				}
				else
				{
					return false;
				}
			}
			if($this -> formData['name'] == $name)
			{
				if($step == false)
				{
					return true;
				}
				if(isset($this -> formData['step']))
				{
					return $this -> formData['step'];
				}
				return false;
			}
			return false;
		} // end getDynamicFormInfo();
		
		public function handleAjax()
		{
			if($this -> validator -> map('opfAjax', OPF_REQUEST, new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_INTEGER),
				new opfConstraint(MAP_SCOPE, -1, 3)
			)))
			{
				if($this -> validator -> opfAjax > 0)
				{
					$this -> visit -> ajax = true;
					$this -> visit -> ajaxMode = $this -> validator -> opfAjax;
					
					// Be sure nobody is so stupid to send any templates in AJAX connection...
					$this -> tpl -> httpHeaders(OPT_XML, OPT_NO_HTTP_CACHE);
					$this -> enableOpfXML();
					
					if($this -> visit -> ajaxMode == OPF_SELECTIVE_AJAX)
					{
						if($this -> validator -> map('opfAjaxControl', OPF_REQUEST, new opfStandardContainer(
							new opfConstraint(MAP_TYPE, TYPE_STRING),
							new opfConstraint(MAP_LEN_GT, 0)
						)))
						{
							$this -> visit -> ajaxControl = $this -> validator -> opfAjaxControl;						
						}
						else
						{
							$this -> error(OPF_E_INVALID_AJAX, 'No opfAjaxControl parameter defined.');
						}
					}
				}			
			}
		} // end handleAjax();
		
		public function beginXMLResponse()
		{
			if(!$this -> xmlLock)
			{
				header('Content-type: application/xml;charset='.$this -> tpl -> charset);
				echo '<'.'?xml version="1.0" encoding="'.$this -> tpl -> charset.'"?'.">\r\n";
				echo "<opfResponse>\r\n";
			}
		} // end beginXMLResponse();

		public function setXMLMessage($message)
		{
			if(!$this -> xmlLock)
			{
				echo '<opfMessage>'.$message."</opfMessage>\r\n";
			}
		} // end setXMLMessage();

		public function setXMLResult($result)
		{
			if(!$this -> xmlLock)
			{
				echo '<opfResult>'.$result."</opfResult>\r\n";
			}
		} // end setXMLMessage();

		public function beginValidationResult($item, $result)
		{
			if(!$this -> xmlLock)
			{
				echo '<opfValidationResult item="'.$item."\">\r\n";
				$this -> setXMLResult($result);				
			}
		} // end beginValidationResult();

		public function finishValidationResult()
		{
			if(!$this -> xmlLock)
			{	
				echo "</opfValidationResult>\r\n";
			}
		} // end finishValidationResult();

		public function finishXMLResponse()
		{
			if(!$this -> xmlLock)
			{
				echo "</opfResponse>\r\n";
			}
		} // end finishXMLResponse();

		public function enableOpfXML()
		{
			$this -> xmlLock = false;
		} // end enableOpfXML();
		
		public function error($code, $message)
		{
			require_once(OPF_DIR.'opf.error.php');
			throw new opfException($code, $message, $this);
		} // end error();

	} // end opfClass;
	
	class opfValidator implements iopfValidator
	{
		private $__source = array();
		private $opf;
		private $quotes;
		private $quoteState = false;
		private $__mappedData = array();

		public function setOpfInstance(opfClass $opf)
		{
			$this -> opf = $opf;
			if(version_compare(phpversion(), '6.0.0-dev', '<'))
			{
				$this -> quotes = $this -> opf -> magicQuotes;
				$this -> quoteState = get_magic_quotes_gpc();
			}
			else
			{
				// PHP 6 has no magic quotes
				$this -> quotes = false;
				$this -> quoteState = false;
			}
		} // end setOpfInstance();
		
		public function setParams($type, &$data, $named = true)
		{
			// add the "named" support
			// when set to true, the registered parameter source supports named parameters
			// otherwise, a simple counter is used to access the elements.
			if(!isset($this -> __source[$type]))
			{
				$this -> __source[$type] = $data;
				return true;
			}
			return false;
		} // end setParams();
		
		public function defaultParams()
		{
			$this -> __source = array(
				OPF_POST => &$_POST,
				OPF_GET => &$_GET,
				OPF_REQUEST => &$_REQUEST,
				OPF_COOKIE => &$_COOKIE,
				OPF_FILE => &$_FILE,
			);
			return $this;
		} // end defaultParams();
		
		public function formSent($filename = '')
		{
        	if($filename == '')
        	{
        		if($_SERVER['REQUEST_METHOD'] == 'POST')
        		{
        			return true;
        		} 
        	}
        	elseif($_SERVER['REQUEST_METHOD'] == 'POST' && strpos($_SERVER['HTTP_REFERER'], $filename) !== FALSE)
			{
				return true;
			}
			return false;
		} // end formSent();

		public function map($name, $type, iopfConstraintContainer $container)
		{
			// decide, what to map
			if(isset($this->__source[$type]))
			{
				if(!isset($this->__source[$type][$name]))
				{
					return false;
				}
				$this -> rmquot($this->__source[$type][$name]);
				$container -> setOpfInstance($this -> opf);
				if($container -> process($name, $type, $this->__source[$type][$name]))
				{
					$this -> __mappedData[$name] = &$this->__source[$type][$name];
					return true;
				}
				return false;				
			}
			else
			{
				$this -> opf -> error(OPF_E_INVALID_DATATYPE, 'The datatype "'.$type.'" is not registered in the validator.');
			}
		} // end map();
		
		public function exists($name, $type)
		{
			if(isset($this->__source[$type]))
			{
				if(isset($this->__source[$type][$name]))
				{
					if(trim($this->__source[$type][$name]) != '')
					{
						return true;
					}
				}
				return false;
			}
			else
			{
				$this -> opf -> error(OPF_E_INVALID_DATATYPE, 'The datatype "'.$type.'" is not registered in the validator.');
			}
		} // end exists();
		
		public function compare($name1, $name2, $type)
		{
			if(isset($this->__source[$type]))
			{
				if(isset($this->__source[$type][$name1]) && isset($this->__source[$type][$name2]))
				{
					if($this->__source[$type][$name1] == $this->__source[$type][$name2])
					{
						return true;
					}					
				}
				return false;
			}
			else
			{
				$this -> opf -> error(OPF_E_INVALID_DATATYPE, 'The datatype "'.$type.'" is not registered in the validator.');
			}
		} // end compare();

		public function __get($name)
		{
			if(isset($this -> __mappedData[$name]))
			{
				return $this -> __mappedData[$name];
			}
			return NULL;
		} // end __get();

		public function get($name)
		{
			if(isset($this -> __mappedData[$name]))
			{
				return $this -> __mappedData[$name];
			}
			return NULL;
		} // end get();
		
		private function rmquot(&$text)
		{
			if($this -> quotes == false && $this -> quoteState == true)
			{
				if(!is_array($text))
				{
					$text = stripslashes($text);
				}
				else
				{
					foreach($text as &$item)
					{
						$item = stripslashes($item);
					}
				}
			}
		} // end rmquot();
	} // end opfValidator;
	
	class opfShowFormException extends Exception
	{
		private $mode;
	
		public function __construct($mode = true)
		{
			parent::__construct();
			$this -> mode = $mode;
		} // end __construct();
		
		public function invalid()
		{
			return $this -> mode;
		} // end invalidData();	
	} // end opfShowFormException;

	abstract class opfVirtualForm
	{
		protected $opf;
		protected $tpl;
		protected $validator;

		private $datasource;
		private $dst = 0;
		private $fields = array();
		private $nullValues = array();
		private $errorGroups = array();
		private $errorTexts = array();
		private $fieldCounter = -1;
		private $nextStep = NULL;
		private $requestMethod = OPF_POST;
		private $ignored = false;
		private $invalid = false;
		
		// Data for OPT
		public $name;
		public $step = NULL;
		public $items = array();
		public $errorMessages = array();
		public $valid = true;

		final public function __construct(opfClass $opf, $name)
		{
			$this -> i18n = $opf -> i18n;
			$this -> name = $name;
			$this -> opf = $opf;
			$this -> tpl = $opf->tpl;
			$this -> validator = $opf -> validator;
			$this -> visit = $opf -> visit;
			$this -> design = $opf -> design;
		} // end __construct();
		
		abstract public function create();
		public function view()
		{
			return false;
		} // end view();

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
		
		final protected function setJavascriptEvent($name, $js)
		{
			if(is_object($this -> fields[$name]))
			{
				$this -> fields[$name]->js = $js;				
				return;
			}
			$this -> opf -> error(OPF_E_CONTAINER_NOT_DEFINED, 'A container for field "'.$name.'" is not defined.');			
		} // end setJavaScriptEvent();
		
		final public function ignore()
		{
			$this -> ignored = true;
		} // end ignore();
		
		final public function invalid()
		{
			return $this->invalid;
		} // end invalid();
		
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

		final public function setRequestMethod($requestMethod)
		{
			$this -> requestMethod = $requestMethod;
		} // end setRequestMethod();

		final public function nextStep(opfVirtualForm $form)
		{
			if(is_null($this -> nextStep))
			{
				$this -> nextStep = $form;
				return true;
			}
			return false;
		} // end nextStep();

		final public function assign($name, $value = NULL)
		{
			if(is_array($name))
			{
				foreach($name as $idx => $value)
				{
					$this -> items[$idx] = $value;
				}
			}
			elseif(!is_null($value))
			{
				$this -> items[$name] = $value;
			}
			else
			{
				$this -> opf -> error(OPF_E_INVALID_ARGS, 'Invalid arguments for method opfVirtualForm::assign(): A single key-value pair or an array of pairs required.');
			}
		} // end assign();

		final public function getValue($name, $listData = false)
		{
			if($this -> valid == false && $listData == false)
			{
				if(strpos($name, '[') !== false)
				{
					preg_match('/(.+)\[(.+)\]/', $name, $found);
					return $_POST[$found[1]][$found[2]];				
				}
				return $_POST[$name];
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
		
		final protected function setError($name, $group, $id = NULL, $args = NULL)
		{
			if(func_num_args() == 2)
			{
				$this -> errorMessages[$name][] = $group;
			}
			else
			{
				if(!is_null($args))
				{
					$this -> errorMessages[$name][] = $this -> i18n -> putApply($group, $id, $args);
				}
				else
				{
					$this -> errorMessages[$name][] = $this -> i18n -> put($group, $id);
				}
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
		
		final public function getStep()
		{
			return $this -> step;
		} // end getStep();
		
		final public function display()
		{
			$this -> create();
		
			$this -> validate();
			$this -> render(new opfShowFormException(false));		
		} // end display();
		
		final public function execute()
		{
			$this -> create();
			$info = $this -> opf -> getDynamicFormInfo($this -> name, ($this -> nextStep !== null));

			if(!$this -> visit -> ajax)
			{
				try
				{
					if(!is_null($this -> nextStep))
					{
						$items = array();
						return $this -> stepExecute($this, $info, 1, $items);						
					}
					if($info === false)
					{
						throw new opfShowFormException(false);				
					}
					return $this -> validate();			
				}
				catch(opfShowFormException $exception)
				{
					if($exception -> invalid())
					{
						$this -> invalid = true;
					}
					else
					{
						$this -> invalid = false;
					}
					$this -> render();
					return $this -> ignored;
				}		
			}
			else
			{
				// AJAX Request
				try
				{
					$result = $this -> validate();
					$this -> createAjaxResponse();
					return $result;
				}
				catch(opfShowFormException $exception)
				{
					$this -> createAjaxResponse();
					return $this -> ignored;
				}
			}		
		} // end execute();
		
		final public function stepExecute($starter, $info, $step, &$items)
		{
			$this -> step = $step;
			$this -> create();
			if($step < $info)
			{
				try
				{
					$this -> validate();
					$items = array_merge($items, $this -> getItems());
					if(is_null($this -> nextStep))
					{
						return true;
					}
					return $this -> nextStep -> stepExecute($this, $info, $step + 1, $items);
				}
				catch(opfShowFormException $exception)
				{
					$this -> assign($items);
					if($exception -> invalid())
					{
						$this -> invalid = true;
					}
					else
					{
						$this -> invalid = false;
					}
					$this -> render();
					return $this -> ignored;
				}
			}
			else
			{
				$this -> invalid = false;
				$this -> assign($items);
				$this -> render();
				return $this -> ignored;
			}
		} // end stepExecute();
		
		/*
		 * Multi-step forms
		 */
		
		public function getItems()
		{
			$result = array();
			foreach($this -> fields as $name => $void)
			{
				$result[$name] = $this -> validator -> get($name);			
			}
			return $result;
		} // end getItems();
		
		/*
		 * INTERNAL OPF METHODS
		 */
		
		private function render()
		{
			// $this -> generateJavaScriptCode();
			$this -> tpl -> assign($this -> name, $this);
			reset($this -> fields);
			$this -> view();
			return true;	
		} // end render();

		private function validate()
		{
			$ok = $this -> stepValidate();
			$this -> valid = $ok;
			if(!$ok)
			{
				throw new opfShowFormException();
			}
			
			return $ok;
		} // end validate();
		
		private function stepValidate()
		{
			$ok = true;
			foreach($this -> fields as $name => $container)
			{
				switch($this -> nullValues[$name])
				{
					case OPF_REQUIRED:
						$this -> validator -> map($name, $this -> requestMethod, $container);
						if(!$container -> valid())
						{
							$ok = false;
							$this -> setInvalid($name, $container);
						}
						break;
					case OPF_LAZY_OPTIONAL:
						$this -> validator -> map($name, $this -> requestMethod, $container);
						break;
					case OPF_OPTIONAL:
						if($this -> validator -> exists($name, $this -> requestMethod))
						{
							$this -> validator -> map($name, $this -> requestMethod, $container);
							if(!$container -> valid())
							{
								$ok = false;
								$this -> setInvalid($name, $container);
							}
						}
						break;					
				}
			}
			return $ok && $this -> process();
		} // end stepValidate();
		
		private function setInvalid($name, $container)
		{
			$this -> errorMessages[$name] = array();			
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
							$error['args'][1] = $this -> i18n -> put($this -> opf -> i18nGroup, $error['args'][1]); 
						}
						$this -> errorMessages[$name][] = $this -> i18n -> putApply($this -> opf -> i18nGroup, $error['id'], $error['args']);
					}
					else
					{							
						$this -> errorMessages[$name][] = $this -> i18n -> put($this -> opf -> i18nGroup, $error['id']);
					}
				}
			}
		} // end setInvalid();
			
		private function getCssClass($item)
		{
			if(isset($this -> errorMessages[$item]))
			{
				return $this -> design -> getClass('row', $this->name, false);
			}
			else
			{
				return $this -> design -> getClass('row', $this->name, true);
			}
		} // end getCssClass();
		
		public function printVals()
		{
			foreach($this as $name => $value)
			{
				echo $name.' - '.$value.'<br/>';
			}
		} // end printVals();

		public function generateJavascript()
		{
			$code = '';
			foreach($this -> fields as $name => $object)
			{
				$code .= $object -> createJavaScript($name);			
			}
			return $code;
		} // end generateJavascript();
		
		// AJAX
		
		private function createAjaxResponse()
		{
			$resp = $this -> opf;	
			$resp -> beginXMLResponse();

			$resp -> setXMLResult((int)$this -> valid);
			
			if(!$this -> valid)
			{
				// Put the information about each field validation
				foreach($this -> fields as $name => $void)
				{
					if(isset($this -> errorMessages[$name]))
					{
						$resp -> beginValidationResult($name, 0);
						foreach($this -> errorMessages[$name] as $message)
						{
							$resp -> setXMLMessage($message);
						}
					}
					else
					{
						$resp -> beginValidationResult($name, 1);
					}
					$resp -> finishValidationResult();			
				}
			}
			$resp -> finishXMLResponse();	
		} // end createAjaxResponse();
	}
	
	// CSS component design manager
	// Necessary, because HTML forms were developed by...
	// Well... you see, how they were "brilliant" not allowing to distinguish between (for example) text and radio fields
	class opfDesign
	{
		private $designInfo;
		private $fieldDesignInfo;
		
		public function __construct()
		{
			$this -> designInfo = array('base' => array(
				'valid' => '',
				'invalid' => ''
			));
			$this -> fieldDesignInfo = array();
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
				'valid' => (strlen($valid) > 0 ? $valid : NULL),
				'invalid' => (strlen($invalid) > 0 ? $invalid : NULL)
			);		
		} // end setDesign();
		
		public function setFieldDesign($component, $name, $valid = NULL, $invalid = NULL)
		{
			$this -> fieldDesignInfo[$component.'_'.$name] = array(
				'valid' => (strlen($valid) > 0 ? $valid : NULL),
				'invalid' => (strlen($invalid) > 0 ? $invalid : NULL)
			);		
		} // end setFieldDesign();
		
		public function getClass($component, $name, $valid)
		{
			$src = array();
			if(isset($this->fieldDesignInfo[$component.'_'.$name]))
			{
				$src[] = $this -> fieldDesignInfo[$component.'_'.$name];
			}
			if(isset($this -> designInfo[$component]))
			{
				$src[] = $this -> designInfo[$component];
			}
			$src[] = $this -> designInfo['base'];
			
			foreach($src as $item)
			{
				if($valid && !is_null($item['valid']))
				{
					return $item['valid'];
				}
				elseif(!$valid && !is_null($item['invalid']))
				{
					return $item['invalid'];
				}
			}
			return false;
		} // end getClass();

	} // end opfDesign;
?>
