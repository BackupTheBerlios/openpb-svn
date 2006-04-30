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

	if(!defined('OPF_DIR'))
	{
		define('OPF_DIR', './');
	}
	
	define('OPF_VERSION', '1.0.0-dev');

	interface iopfConstraintContainer
	{
		public function __construct();
		public function process($name, $type, &$value);
		public function createJavaScript($name);
		public function valid();
		public function error();
	}

	interface iopfConstraint
	{
		public function __construct($type);
		public function process($name, $type, &$value);
		public function createJavaScript($name);
		public function valid();
		public function error();
	}

	interface iopfResponse
	{
		public function setOpfInstance(opfClass $context);
		public function createCookie($name, $value, $time = NULL);
		public function setCookieExpire($name, $time);
		public function removeCookie($name);
		public function addHeader($header);
		public function listHeaders();
		public function beginXMLResponse();
		public function setXMLMessage($message);
		public function setXMLValidationResult($item, $result, $message = NULL);
		public function finishXMLResponse();
	}

	interface iopfRequest
	{
		public function setOpfInstance(opfClass $context);
		public function map($name, $type, iopfConstraintContainer $container);
		public function __get($name);
		public function get($name);
	}

	if(!defined('NO_OPF_RESPONSE'))
	{
		require(OPT_DIR.'opt.class.php');
		require(OPF_DIR.'opf.response.php');
	}
	if(!defined('NO_OPF_REQUEST'))
	{
		require(OPF_DIR.'opf.request.php');
	}
	require(OPF_DIR.'opf.components.php');
	require(OPF_DIR.'opf.visit.php');
	require(OPF_DIR.'opf.form.php');
	require(OPF_DIR.'opf.error.php');
	require(OPF_DIR.'opf.router.php');

	// HTTP-Context
	class opfClass
	{
		// OPF configuration
		public $router = NULL;
		public $ajaxEnabled = false;
		public $i18nGroup = 'opf';
	
		// OPF elements
		private $response;
		private $request;
		private $visit;
		private $design;
		private $routerObj;
		
		// Dynamic forms
		private $dynamicForms = false;
		private $formData = array();

		public function __construct(iopfResponse $response, iopfRequest $request)
		{
			$this -> response = $response;
			$this -> request = $request;
			$this -> visit = new opfVisit;
			$this -> design = new opfDesign;
			$response -> setOpfInstance($this);
			$request -> setOpfInstance($this);

			$this -> visit -> ajax = false;

			// Check whether the form is sent
			if($this -> visit -> requestMethod == OPF_POST)
			{
				if($this -> request -> map('opfFormName', OPF_POST, new opfStandardContainer(
					new opfConstraint(MAP_TYPE, TYPE_STRING),
					new opfConstraint(MAP_LEN_GT, 0)	
				)))
				{
					$this -> formData = array('name' => $this -> request -> opfFormName);
					$this -> dynamicForms = true;
					if($this -> request -> map('opfStep', OPF_POST, new opfStandardContainer(
						new opfConstraint(MAP_TYPE, TYPE_INTEGER),
						new opfConstraint(MAP_GT, 0)			
					)))
					{
						$this -> formData['step'] = $this -> request -> opfStep;
					}
					else
					{
						$this -> formData['step'] = 1;
					}
				}	
			}
		} // end __construct();
		
		static public function create($config = NULL)
		{
			$context = new opfClass(new opfResponse, new opfRequest);
		
			if($config != NULL)
			{
				$context -> loadConfig($config);
			}
			return $context;
		} // end create();

		public function getResponse()
		{
			return $this -> response;
		} // end getResponse();

		public function getRequest()
		{
			return $this -> request;		
		} // end getRequest();

		public function getVisit()
		{
			return $this -> visit;
		} // end getVisit();
		
		public function getRouter()
		{
			return $this -> routerObj;
		} // end getRouter();
		
		public function getDesign()
		{
			return $this -> design;
		} // end getDesign();

		public function loadConfig($data)
		{
			$configDirectives = array(0=>
				'rowTitle', 'rowValue', 'invalidRowTitle', 'invalidRowValue', 'router'
			);

			if(is_string($data))
			{
				$data = parse_ini_file($data, true);			
			}
			elseif(!is_array($data))
			{
				throw new opfException('Invalid configuration data format. Array or file name required.');
			}
			
			foreach($configDirectives as $name)
			{
				if(isset($data[$name]))
				{
					$this -> $name = $data[$name];
				}
			}
			// Router autoconfiguration
			if($this -> router != NULL)
			{
				switch($this -> router)
				{
					case 'default':

						$this -> setRouter(new opfDefaultRouter());
						break;
					case 'nice':
						$this -> setRouter(new opfNiceRouter());
						break;
					case 'value':
						$this -> setRouter(new opfValueRouter());
				}
			}
			// Load OPT configuration
			if(isset($data['opt']) && is_array($data['opt']))
			{
				$this -> response -> loadConfig($data['opt']);
			}
		} // end loadConfig();

		public function setRouter(iopfRouter $router)
		{
			$this -> routerObj = $router;
			// Try to init AJAX connection
			// If failure, set the normal mode
			if($this -> ajaxEnabled)
			{
				$this -> response -> initAjaxConnection();
			}
		} // end setRouter();
		
		public function getDynamicFormInfo($name, $step = false)
		{
			if($this -> dynamicForms == false)
			{
				return false;
			}
			elseif($this -> formData['name'] == $name)
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
			return $this -> formData;
		} // end getDynamicFormInfo();

		public function addForm($name, opfVirtualForm $form, $id = NULL)
		{
			$form -> setOpfInstance($this, $name);
			if($id == NULL)
			{
				$this -> forms[$name] = $form;		
			}
			else
			{
				if(!isset($this -> forms[$name]))
				{
					$this -> forms[$name] = array();
				}
				$this -> forms[$name][$id] = $form;
				$form -> setStep($id);
			}
			$this -> stepCount++; 
		} // end addForm();

		public function process()
		{
			if($this -> stepCount == 1)
			{
				if(!is_object($this -> forms[0]))
				{
					throw new opfException('Open Power Forms: no form loaded for process() method');				
				}
			
				// One-step form
				if($this -> visit -> requestMethod == OPF_GET)
				{
					$this -> forms[0] -> run(OPF_STATE_RENDER);
					return false;	
				}
				else
				{
					if(!$this -> request -> map('opfFormName', OPF_POST, new opfStandardContainer(
						new opfConstraint(MAP_TYPE, TYPE_STRING),
						new opfConstraint(MAP_LEN_GT, 0)	
					)))
					{
						return -1;					
					}
				
					return $this -> forms[0] -> run(OPF_STATE_VALIDATE);
				}
			}
			elseif($this -> stepCount > 1)
			{
				// Multi-step form

				if($this -> visit -> requestMethod == OPF_POST)
				{
				
					if($this -> request -> map('opfStep', OPF_POST, new opfStandardContainer(
						new opfConstraint(MAP_TYPE, TYPE_INTEGER),
						new opfConstraint(MAP_GT, 0)			
					)))
					{
						$step = $this -> request -> opfStep;	
					}
					else
					{
						return -1;
					}
				}
				else
				{
					$step = 1;			
				}
				if(isset($this -> forms[$step-1]))
				{
					$this -> forms[$step-1] -> run(OPF_STATE_VALIDATE);
					if(isset($this -> forms[$step]))
					{
						$this -> forms[$step] -> addPreviousItems($this -> forms[$step-1] -> getItems());
					}
				}			

				// Revalidate all previous steps
				if($step > 2)
				{
					for($i = 1; $i <= $step-2; $i++)
					{
						if($this -> forms[$i] -> run(OPF_STATE_STEP_VALIDATE))
						{
							if(isset($this -> forms[$step]))
							{
								$this -> forms[$step] -> addPreviousItems($this -> forms[$i] -> getItems());
							}
						}
						else
						{
							// Someone tries to cheat
							return -1;
						}
					}
				}

				if(isset($this -> forms[$step]))
				{
					$this -> forms[$step] -> run(OPF_STATE_RENDER);
					return 0;
				}
				return 1;
			}
		} // end process();

	}

?>
