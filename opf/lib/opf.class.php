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
	require(OPF_DIR.'opf.visit.php');
	require(OPF_DIR.'opf.error.php');
	require(OPF_DIR.'opf.router.php');
	
	// HTTP-Context
	class opfClass
	{
		// OPF configuration
		public $rowTitle = '';
		public $rowValue = '';
		public $invalidRowTitle = '';
		public $invalidRowValue = '';
		public $router = NULL;		
	
		// OPF elements
		private $response;
		private $request;
		private $visit;
		private $routerObj;

		public function __construct(iopfResponse $response, iopfRequest $request)
		{
			$this -> response = $response;
			$this -> request = $request;
			$this -> visit = new opfVisit;
			$response -> setOpfInstance($this);
			$request -> setOpfInstance($this);
			
			$this -> visit -> ajax = false;	
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
			$this -> response -> initAjaxConnection();
		} // end setRouter();
/*
		public function addForm(opfVirtualForm $form, $id = NULL)
		{
		
		} // end addForm();

		public function process()
		{
		
		} // end process();
*/
	}

?>
