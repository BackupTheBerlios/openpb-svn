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

	define('OPF_POST', 0);
	define('OPF_GET', 1);
	define('OPF_COOKIE', 2);
	define('OPF_FILE', 3);
	
	require(OPF_DIR.'opf.container.php');
	require(OPF_DIR.'opf.constraint.php');

	class opfRequest implements iopfRequest
	{
		private $context;
		private $__mappedData;

		public function setOpfInstance(opfClass $context)
		{
			$this -> context = $context;
		} // end setOpfInstance();

		public function map($name, $type, iopfConstraintContainer $container)
		{
			// decide, what to map
			switch($type)
			{
				case OPF_POST:
					if(!isset($_POST[$name]))
					{
						return 0;
					}
					if($container -> process($name, $type, $_POST[$name]))
					{
						$this -> __mappedData[$name] = &$_POST[$name];
						return 1;
					}
					return 0;
				case OPF_GET:
					$data = $this -> context -> getRouter() -> handleData($name);
					if($data == NULL)
					{
						return 0;
					}
					if($container -> process($name, $type, $_GET[$name]))
					{
						$this -> __mappedData[$name] = $data;
						return 1;
					}
					return 0;
				case OPF_COOKIE:
					if(!$this -> context -> getVisit() -> cookiesEnabled)
					{
						// Cookie-emulation data always go through GET.
						if(!isset($_GET[$name]))
						{
							return 0;
						}
						if($container -> process($name, $type, $_GET[$name]))
						{
							$this -> __mappedData[$name] = &$_GET[$name];
							return 1;
						}
						return 0;
					}
					else
					{
						if(!isset($_COOKIE[$name]))
						{
							return 0;
						}
						if($container -> process($name, $type, $_COOKIE[$name]))
						{
							$this -> __mappedData[$name] = &$_COOKIE[$name];
							return 1;
						}
						return 0;
					}
				case OPF_FILE:
					if(!isset($_FILES[$name]))
					{
						return 0;
					}
					if($container -> process($name, $type, $_FILES[$name]))
					{
						$this -> __mappedData[$name] = &$_FILES[$name]['name'];
						return 1;
					}
					return 0;
			}
		} // end map();

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
	}
?>
