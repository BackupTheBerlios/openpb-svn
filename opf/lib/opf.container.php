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

	class opfStandardContainer implements iopfConstraintContainer
    {
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
    		$jsCode = '';
    		foreach($this -> constraintList as $item)
    		{
    			$jsCode .= $item -> jsProcess($name);
    		}
    		return $jsCode;
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

?>
