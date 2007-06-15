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
 
	define('MAP_TYPE', 0);
	define('MAP_GT', 1);
	define('MAP_LT', 2);
	define('MAP_LEN_GT', 3);
	define('MAP_LEN_LT', 4);
	define('MAP_EQUAL', 5);
	define('MAP_LEN_EQUAL', 6);
	define('MAP_PASSWORD', 7);
	define('MAP_MATCHTO', 8);
	define('MAP_NOTMATCHTO', 9);
	define('MAP_PERMITTEDCHARS', 10);
	define('MAP_NOTPERMITTEDCHARS', 11);
	define('MAP_SCOPE', 12);
	define('MAP_JS', 13);
	
	define('TYPE_INTEGER', 0);
	define('TYPE_FLOAT', 1);
	define('TYPE_NUMERIC', 2);
	define('TYPE_STRING', 3);
	define('TYPE_TEXT', 4);
	define('TYPE_BOOL', 5);
	define('TYPE_BOOLEAN', 5);
	define('TYPE_CHOOSE', 6);
	define('TYPE_COMPARABLE', 7);

	define('JS_SUBMIT', 2);
	define('JS_SELECT', 4);
	define('JS_CHANGE', 8);
	define('JS_RESET', 16);
	define('JS_FOCUS', 32);
	define('JS_BLUR', 64);
	define('JS_KEYPRESS', 128);
	define('JS_KEYUP', 256);
	define('JS_KEYDOWN', 512);
	define('JS_CLICK', 1024);
	define('JS_DBLCLICK', 2048);
	
	define('OPF_MAIL_PATTERN', '/(.+)\@(.+)\.(.+)/');
	
	class opfStandardContainer implements iopfConstraintContainer
    {
			public $js;
    	protected $constraintList;
 
    	protected $valid = true;
    	protected $errors = array();
    	protected $i;
 
    	public function __construct()
    	{
    		$this -> constraintList = func_get_args();
    		$this -> i = 0;
    	} // end __construct();
 
    	public function process($name, $type, &$value)
    	{
    		foreach($this -> constraintList as $item)
    		{
    			if(!$item -> process($name, $type, $value))
    			{
    				$this -> valid = false;
    				$this -> errors[$this -> i] = $item -> error();
					$this -> i++;		
    			}
    		}
    		$this -> i = 0;
    		return $this -> valid;
    	} // process();
 
		public function valid()
		{
			return $this -> valid;
		} // end valid();
 
		public function error()
		{
			if(isset($this -> errors[$this -> i]))
			{
				return $this -> errors[$this -> i++];
			}
		} // end error();
    	
    	public function createJavaScript($name)
    	{
				$code = "form.map('".$name."', new opfStandardContainer(\r\n";
    		foreach($this -> constraintList as $key => $item)
    		{
    			$code .= $item -> createJavaScript($name);
					if(($key+1) < count($this -> constraintList))
					{
						$code.= ', ';
					}
					$code.= "\r\n";
    		}
				$code.= '));'."\r\n";

				if(is_array($this->js))
				{
					$code.= 'form.setEvent(\''.$name.'\', '.$this->js[1].');'."\r\n";
				}

				$scriptSrc = '<script type="text/javascript" src="js/forms/"></script>';

				return $code;
    	} // createJavaScript();
    }
 
	class opfArrayContainer extends opfStandardContainer
    {
    	public function process($name, $type, &$value)
    	{
    		if(!is_array($value))
    		{
    			$this -> valid = false;
    			$this -> errors[0]['id'] = 'constraint_not_array';
    			$this -> errors[0]['args'] = array();
    			return 0;
    		}
    		foreach($value as &$element)
    		{
	    		foreach($this -> constraintList as $item)
	    		{
	    			if(!$item -> process($name, $type, $element))
	    			{
	    				$this -> valid = false;
	    				$this -> errors[$this -> i] = $item -> error();
						$this -> i++;		
	    			}
	    		}
    		}
    		$this -> i = 0;
    		return $this -> valid;
    	} // process();
    	
    	public function createJavaScript($name)
    	{
    		$jsCode = '';
    		foreach($this -> constraintList as $item)
    		{
    			$jsCode .= $item -> jsProcess($name);
    		}
    		return $jsCode;
    	} // createJavaScript();
    }
 
 
    class opfConstraint implements iopfConstraint
    {  	
    	private $valid = true;
    	private $error = array();
    	
    	private $type;
    	private $params;
    	
    	public function __construct($type)
    	{
    		$params = func_get_args();
    		unset($params[0]);
    		$this -> type = $type;
    		$this -> params = $params;
		} // end __construct();
 
    	public function process($name, $type, &$value)
    	{
    		switch($this -> type)
    		{
    			case MAP_TYPE:
    				// Type testing
    				switch($this -> params[1])
    				{
    					case TYPE_INTEGER:
			                if(!ctype_digit((string)$value))
			                {
			                	$this -> setError('constraint_type', 'type_integer');
			                }
			                break;
			            case TYPE_FLOAT:
			                if(!preg_match('/([0-9]*?)[.,]([0-9]*?)/', $value))
			                {
			                	$this -> setError('constraint_type', 'type_float');
			                }
			                break;
			            case TYPE_NUMERIC:
			                if(!preg_match('/([0-9 -]*?)([\.,][0-9]*?)?/', $value))
			                {
			                	$this -> setError('constraint_type', 'type_numeric');
			                }
			                break;
			            case TYPE_STRING:
			                if(ctype_digit($value) || (strlen($value) > 256))
			                {
			                	$this -> setError('constraint_type', 'type_string');
			                }
			                break;
			            case TYPE_TEXT:
			                if(ctype_digit($value))
			                {
			                	$this -> setError('constraint_type', 'type_text');
			                }
			                break;
			            case TYPE_BOOL:
			                if(!preg_match('/(0|1)/', $value))
			                {
			                	$this -> setError('constraint_type', 'type_bool');
			                }
			                break;
			            case TYPE_CHOOSE:
			                if($value == 'on' || $value == 'off')
			                {
			                    $value = ($value == 'on' ? 1 : 0);
			                }
			                else
			                {
			                	$this -> setError('constraint_type', 'type_choose');
			                }
			                break;
			            case TYPE_COMPARABLE:
			            	if($value != $_POST[$name.'2'])
			            	{
			                	$this -> setError('constraint_type', 'type_comparable');
			                }
    				}
					break;
		        case MAP_GT:
		        	if(!($value > $this -> params[1]))
		        	{
		        		$this -> setError('constraint_gt', $this->params[1]);
		        	}
		        	break;
		        case MAP_LT:
		        	if(!($value < $this -> params[1]))
		        	{
		        		$this -> setError('constraint_lt', $this->params[1]);
		        	}
		        	break;
		        case MAP_LEN_GT:
		        	if(!(strlen($value) > $this -> params[1]))
		        	{
		        		$this -> setError('constraint_len_gt', $this->params[1]);
		        	}
		        	break;
		        case MAP_LEN_LT:
		        	if(!(strlen($value) < $this -> params[1]))
		        	{
		        		$this -> setError('constraint_len_lt', $this->params[1]);
		        	}
		        	break;
		        case MAP_EQUAL:
		        	if($value != $this -> params[1])
		        	{
		        		$this -> setError('constraint_equal', $this->params[1]);
		        	}
		        	break;
		        case MAP_LEN_EQUAL:
		        	if(strlen($value) != $this -> params[1])
		        	{
		        		$this -> setError('constraint_len_equal', $this->params[1]);
		        	}		        	
		        	break;
		        case MAP_PASSWORD:
		        	if($value != $_POST[$this->params[1]])
		        	{
		        		$this -> setError('constraint_password', $this->params[1]);
		        	}
		        	break;
		        case MAP_MATCHTO:
		        	if(!preg_match($this -> params[1], $value))
		        	{
		        		$this -> setError('constraint_matchto');
		        	}
		        	break;
		        case MAP_NOTMATCHTO:
		        	if(preg_match($this -> params[1], $value))
		        	{
		        		$this -> setError('constraint_notmatchto');
		        	}
		        	break;
		        case MAP_PERMITTEDCHARS:
		        	for($i = 0; $i < strlen($value); $i++)
		        	{
		        		if(strpos($this->params[1], $value{$i}) === FALSE)
		        		{
		        			$this -> setError('constraint_permittedchars', $this->params[1]);
		        		}		        	
		        	}
		        	break;
		        case MAP_NOTPERMITTEDCHARS:
		        	for($i = 0; $i < strlen($value); $i++)
		        	{
		        		if(strpos($this->params[1], $value{$i}) !== FALSE)
		        		{
		        			$this -> setError('constraint_notpermittedchars', $this->params[1]);
		        		}
		        	}
		        	break;
		        case MAP_SCOPE:
		        	if(!($value > $this -> params[1] && $value < $this -> params[2]))
		        	{
		        		$this -> setError('constraint_scope', $this->params[1], $this->params[2]);
		        	}
		        	break;
    		}
    		return $this -> valid();
    	} // process();
    	
		public function valid()
		{
			return $this -> valid;
		} // end valid();
 
		public function error()
		{
			return $this -> error;
		} // end error();
    	
    	public function createJavaScript($name)
    	{
				$code = "new opfConstraint(".$this->type.", ";
 
				foreach($this->params as $key => $param)
				{
					$code.= is_string($param) ? "'".$param."'" : $param;
					$code.= ($key) < count($this->params) ? ', ' : '';
				}
				$code.= ")";
 
				return $code;
 
    	} // createJavaScript();
    	
    	private function setError($id)
    	{
    		$this -> valid = false;
    		$this -> error['id'] = $id;
    		
    		$args = func_get_args();
    		if(count($args) > 1)
    		{
    			unset($args[0]);
    			$this -> error['args'] = $args;
    		}
    	} // end setError();
 
	}