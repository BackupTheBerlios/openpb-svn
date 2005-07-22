<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                        Open Power Template                             //
  //         Copyright (c) 2004 OpenPB team, http://www.opbp.info/          //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //
	define('OPT_MAX_NESTING_LEVEL', 32);
	define('OPT_NOT_FOUND_RETURN', 0);
	define('OPT_NOT_FOUND_ERROR', 1);
	define('OPT_VERSION', '0.2.0-dev');
	define('OPT_ENABLED', 1);
	define('OPT_DISABLED', 0);

	require('opt.error.php');

	abstract class opt_api{
		public $data;
		public $vars;
		public $lang;
		public $conf;
		public $res;
		public $compiler;
		public $functions;
		public $php_functions;
		public $control;
		public $capture;
		public $capture_to = 'echo';
		public $capture_def = 'echo';
		
		private $old_error_reporting;
		private $output_buffer;

		public function error($type, $msg, $code)
		{
			/* get callback information */
			$trace = debug_backtrace();
			for($i = count($trace) - 1; $i >= 0; $i--)
			{
				if($trace[$i]['class'] == 'opt_template' || $trace[$i]['class'] == 'opt_compiler')
				{
					$d_file = $trace[$i]['file'];
					$d_line = $trace[$i]['line'];
					$d_function = $trace[$i]['function'];
					break;				
				}			
			}
			if($code > 0 && $code < 100)
			{
				$n_type = 'Open Power Template';
			}
			else
			{
				$n_type = 'Open Power Template Compiler';
			}
			if($type == E_USER_WARNING && $this -> conf['show_warnings'] == 1)
			{
				$this -> output_started = 1;
				echo '<br/><b>'.$n_type.' warning #'.$code.':</b> '.$msg.' <i>Generated by OPT method `'.$d_function.'` called in '.$d_file.' on line '.$d_line.'</i><br/>';
			}
			elseif($type == E_USER_ERROR)
			{
				throw new opt_exception($msg, $code, $n_type, $d_file, $d_line, $d_function);
			}		
		} // end error();

		public function assign($name, $value, $force_rewrite = 0)
		{
			if($force_rewrite)
			{
				$this -> data[$name] = $value;
				return 1;
			}
			else
			{
				if(!isset($this -> data[$name]))
				{
					$this -> data[$name] = $value;
					return 1;
				}
			}
			return 0;		
		} // end assign();

		public function assign_by_ref($name, &$value, $force_rewrite = 0)
		{
			if($force_rewrite)
			{
				$this -> data[$name] = &$value;
				return 1;
			}
			else
			{
				if(!isset($this -> data[$name]))
				{
					$this -> data[$name] = &$value;
					return 1;
				}
			}
			return 0;		
		} // end assign_by_ref();

		public function register_language(&$lang)
		{
			if(is_array($lang))
			{
				$this -> lang = &$lang;
			}
			else
			{
				$this -> error(E_USER_ERROR, 'First parameter must be an array.', 9);
			}
		} // end register_language();

		public function simple_parse($file)
		{
			$this -> capture_to = 'echo';
			$this -> capture_def = 'echo';
			if(!is_object($this -> compiler))
			{
				require('opt.compiler.php');
				$this -> compiler = new opt_compiler($this);
			}

			$this -> compiler -> load($this -> conf['root'].$file);
			if($this -> compiler -> parse())
			{
				$this -> old_error_reporting = ini_get('error_reporting');
				error_reporting(E_ALL ^ E_NOTICE);
				eval($this -> compiler -> get_code());
				error_reporting($this -> old_error_reporting);
			}
		} // end simple_parse();
	}
?>