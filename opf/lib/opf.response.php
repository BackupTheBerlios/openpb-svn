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

	class opfResponse extends optClass implements iopfResponse
	{
		public $context;
		private $headerList;
		private $content = false;
		private $headersSent = false;
		
		private $lock = true;
		
		public function __construct()
		{
			$this -> control[] = 'opfUrl';
			$this -> control[] = 'opfForm';
			$this -> components['opfInput'] = 1;
			$this -> components['opfPassword'] = 1;
			$this -> components['opfTextarea'] = 1;
			$this -> components['opfQuestion'] = 1;
			$this -> components['opfLabel'] = 1;
			$this -> components['opfSelect'] = 1;
			$this -> components['opfRadio'] = 1;
			$this -> instructionFiles[] = OPF_DIR.'opf.template.php';
		} // end __construct();
	
		public function setOpfInstance(opfClass $context)
		{
			$this -> context = $context;
			$this -> assign('opfDesign', $context -> getDesign());
		} // end setOpfInstance();

		public function createCookie($name, $value, $time = NULL)
		{
			if(!headers_sent())
			{
				if($time == NULL)
				{
					setcookie($name, $value);
				}
				else
				{
					setcookie($name, $value, $time);
				}
				if(!$this -> context -> getVisit() -> cookiesEnabled)
				{
					output_add_rewrite_var($name, $value);
				}
				$_COOKIE[$name] = $value;
				return true;
			}
			return false;
		} // end createCookie();

		public function setCookieExpire($name, $time)
		{
			if(!headers_sent())
			{
				if(isset($_COOKIE[$name]))
				{
					setcookie($name, $_COOKIE[$name], $time);
				}
				if(!$this -> context -> getVisit() -> cookiesEnabled)
				{
					output_add_rewrite_var($name, $value);
				}
				return true;
			}
			return false;
		} // end setCookieExpire();

		public function removeCookie($name)
		{
			if(!headers_sent() && isset($_COOKIE[$name]))
			{
				setcookie($name, '', 0);
				unset($_COOKIE[$name]);
				return true;
			}
			return false;
		} // end removeCookie();

		public function addHeader($header)
		{			
			if(!headers_sent())
			{
				header($header);

				$this -> headerList[] = $header;
				
				if(strpos('Location: ', $header) !== FALSE)
				{					
					die();
				}
				return true;
			}
			return false;
		} // end addHeader();
		
		public function listHeaders()
		{
			return $this -> headerList;
		} // end listHeaders();
		
		public function httpHeaders($content, $cache = OPT_HTTP_CACHE)
		{			
			if(!headers_sent())
			{
				$this -> headersSent = true;
				$charset = '';
				if($this -> charset != NULL)
				{
					$charset = ';charset='.$this -> charset;
				}
				switch($content)
				{		
					case OPT_HTML:
							$this -> addHeader('Content-type: text/html'.$charset);
							break;
					case OPT_XHTML:
							if(stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml'))
							{
								$this -> addHeader('Content-type: application/xhtml+xml'.$charset);
							}
							else
							{
								$this -> addHeader('Content-type: text/html'.$charset);
							}
							break;
					case OPT_XML:
							$this -> addHeader('Content-type: application/xml'.$charset);
							break;
					case OPT_WML:
							$this -> addHeader('Content-type: text/vnd.wap.wml'.$charset);
							break;
					case OPT_TXT:
							$this -> addHeader('Content-type: text/plain'.$charset);
							break;
					default:
							if(is_string($content))
							{
								$this -> addHeader('Content-type: '.$content.$charset);						
							}
							else
							{
								$this -> error(E_USER_ERROR, 'Unknown content type: '.$content, 1);
							}
				}
				if($cache == OPT_NO_HTTP_CACHE)
				{
					$this -> addHeader('Expires: 0'); 
					$this -> addHeader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); 
					// HTTP/1.1 
					$this -> addHeader('Cache-Control: no-store, no-cache, must-revalidate'); 
					$this -> addHeader('Cache-Control: post-check=0, pre-check=0', false);
					// HTTP/1.0 
					$this -> addHeader('Pragma: no-cache');
				}
				if($this->debugConsole)
				{
					$this -> debugConfig[] = array('name' => 'HTTP Headers', 'value' => implode('<br/>', $this -> listHeaders()));
				}
			}
		} // end httpHeaders();
		
		public function headersSent()
		{		
			return $this -> headersSent;		
		} // end headersSent();

		public function beginXMLResponse()
		{
			if(!$this -> lock)
			{
				$this -> content = true;
				
				echo '<'.'?xml version="1.0" encoding="'.$this -> charset.'"?'.">\r\n";
				echo "<opfResponse>\r\n";
			}
		} // end beginXMLResponse();

		public function setXMLMessage($message)
		{
			if(!$this -> lock)
			{
				echo '<opfMessage>'.$message."</opfMessage>\r\n";
			}
		} // end setXMLMessage();

		public function setXMLValidationResult($item, $result, $message = NULL)
		{
			if(!$this -> lock)
			{
				echo '<opfValidationResult item="'.$item."\">\r\n";
				echo '<opfResult>'.$result."</opfResult>\r\n";
				
				if($message != NULL)
				{
					$this -> setXMLMessage($message);
				}
				echo "</opfValidationResult>\r\n";
			}
		} // end setXMLValidationResult();

		public function finishXMLResponse()
		{
			if(!$this -> lock)
			{
				echo "</opfResponse>\r\n";
			}
		} // end finishXMLResponse();
		
		private function enableOpfXML()
		{
			$this -> lock = false;
		} // end enableOpfXML();

		
		public function initAjaxConnection()
		{
			$request = $this -> context -> getRequest();
			if($request -> map('opfAjax', OPF_GET, new opfStandardContainer(
				new opfConstraint(MAP_TYPE, TYPE_INTEGER),
				new opfConstraint(MAP_SCOPE, -1, 3)
			)))
			{
				if($request -> opfAjax > 0)
				{
					$this -> context -> getVisit() -> ajax = true;
					$this -> context ->  getVisit() -> ajaxMode = $request -> opfAjax;
					
					// Be sure nobody is so stupid to send any templates in AJAX connection...
					$this -> httpHeaders(OPT_XML, OPT_NO_HTTP_CACHE);
					$this -> enableOpfXML();
					
					if($this -> context -> getVisit() -> ajaxMode == OPF_SELECTIVE_AJAX)
					{
						if($request -> map('opfAjaxControl', OPF_GET, new opfStandardContainer(
							new opfConstraint(MAP_TYPE, TYPE_STRING),
							new opfConstraint(MAP_LEN_GT, 0)
						)))
						{
							$this -> context -> getVisit() -> ajaxControl = $request -> opfAjaxControl;						
						}
						else
						{
							throw new opfException('No opfAjaxControl parameter defined.');
						}
					}
				}			
			}
		} // end initAjaxConnection();
		
		public function getContext()
		{
			// For OPT components
			return $this -> context;
		} // end getContext();
	}
?>
