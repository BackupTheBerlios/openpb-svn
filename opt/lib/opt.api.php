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
  //
  // $Id$

	define('OPT_MAX_NESTING_LEVEL', 32);
	define('OPT_FORCE_REWRITE', 1);
	define('OPT_VERSION', '1.0.0');
	define('OPT_ENABLED', 1);
	define('OPT_DISABLED', 0);

	if(!defined('OPT_DIR'))
	{
		define('OPT_DIR', './');
	}

	require(OPT_DIR.'opt.error.php');

	abstract class optApi
	{
		// configuration area
		public $root = './templates/';
		public $compile = './templates_c/';
		# OUTPUT_CACHING
		public $cache = NULL;
		# /OUTPUT_CACHING
		# PLUGIN_AUTOLOAD
		public $plugins = NULL;
		# /PLUGIN_AUTOLOAD
		# GZIP_SUPPORT
		public $gzipCompression = 1;
		# /GZIP_SUPPORT
		public $compileCacheDisabled = 0;
		public $showWarnings = 0;
		public $showSource = 0;
		public $charset = 'iso-8859-1';
		public $safeMode = 0;
		# DEBUG_CONSOLE
		public $debugConsole = 0;
		# /DEBUG_CONSOLE
		public $trace = 0;
		public $rewriteWarnings = 0;
		public $includeOptimization = 0;
		public $xmlsyntaxMode = 0;
		public $strictSyntax = 0;
		public $parseintDecPoint = '.';
		public $parseintDecimals = 3;
		public $parseintThousands = ',';
		
		// parser data area	
		public $data = array();
		public $vars = array();
		public $res;
		public $compiler;
		public $functions = array();
		public $phpFunctions = array();
		public $control = array();
		public $components = array();
		public $delimiters = array(0 => '\{(\/?)(.*?)(\/?)\}');

		public $lang;
		public $i18nType;

		protected $init = 0;

		public $codeFilters = array(
								'pre' => NULL,
								'post' => NULL,
								'output' => NULL
							);
		public $capture;
		public $captureTo = 'echo';
		public $captureDef = 'echo';
		# ER_PROTECTION
		private $oldErrorReporting;
		# /ER_PROTECTION
		
		private $outputBuffer;
	
		public $compileCode = '';
		private $includedFiles = array();
		private $testIncludedFiles;
		
		public function __construct()
		{
			$this -> compileCode = '';		
		} // end __construct();

		public function error($type, $msg, $code)
		{
			// get callback information
			$dFile = 'Unknown';
			$dLine = '0';
			$dFunction = 'main';
			$trace = debug_backtrace();
			for($i = count($trace) - 1; $i >= 0; $i--)
			{
				if(isset($trace[$i]['class']))
				{
					if($trace[$i]['class'] == 'optClass' || $trace[$i]['class'] == 'optCompiler')
					{
						$dFile = $trace[$i]['file'];
						$dLine = $trace[$i]['line'];
						$dFunction = $trace[$i]['function'];
						break;				
					}
				}	
			}
			// Code processing
			if($code > 0 && $code < 100)
			{
				$n_type = 'Open Power Template';
			}
			else
			{
				$n_type = 'Open Power Template Compiler';
			}
			if($type == E_USER_WARNING && $this -> showWarnings == 1)
			{
				echo '<br/><b>'.$n_type.' warning #'.$code.':</b> '.$msg.' <i>Generated by OPT method `'.$dFunction.'` called in '.$dFile.' on line '.$dLine.'</i><br/>';
			}
			elseif($type == E_USER_ERROR)
			{
				// Send the exception
				$exception = new optException($msg, $code, $n_type, $dFile, $dLine, $dFunction, $this -> trace);
				
				if($this -> trace)
				{
					$exception -> directories = array(
						'root' => $this->root,
						'compile' => $this->compile,
						'cache' => $this->cache,
						'plugins' => $this->plugins
					);			
				}
				throw $exception;
			}	
		} // end error();

		public function setDefaultI18n(&$lang)
		{
			$this -> i18nType = 0;
			if(is_array($lang))
			{
				$this -> lang = &$lang;
			}
			else
			{
				$this -> error(E_USER_ERROR, 'First parameter must be an array.', 2);
			}
		} // end setDefaultI18n();

		public function assign($name, $value, $forceRewrite = 0)
		{
			if($forceRewrite)
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
				elseif($this -> rewriteWarnings == 1)
				{
					$this -> error(E_USER_WARNING, 'Trying to rewrite \''.$name.'\' block (value: `'.$this -> data[$name].'`) with `'.$value.'` value', 51);
				}
			}
			return 0;
		} // end assign();

		public function assignGroup($values, $forceRewrite = 0)
		{
			if(!is_array($values))
			{
				return 0;
			}
			if($forceRewrite)
			{
				$this -> data = array_merge($this->data, $values);
				return 1;
			}
			else
			{
				foreach($values as $name => &$value)
				{
					if(!isset($this -> data[$name]))
					{
						$this -> data[$name] = $value;
					}
					elseif($this -> rewriteWarnings == 1)
					{
						$this -> error(E_USER_WARNING, 'Trying to rewrite \''.$name.'\' block (value: `'.$this -> data[$name].'`) with `'.$value.'` value', 51);
					}
				}
			}
			return 1;
		} // end assignGroup();

		public function assignRef($name, &$value, $forceRewrite = 0)
		{
			if($forceRewrite)
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
		} // end assignRef();

		public function assignSect($name, $values)
		{
			if(is_array($values))
			{
				$this -> data[$name][] = $values;
			}
			else
			{
				return 0;
			}
		} // end assignSect();
		
		public function registerInstruction($class)
		{
			if(is_object($this -> compiler))
			{
				// The compiler is already initialized, we have to translate this call like the compiler does.
				if(is_array($class))
				{
					foreach($class as $c)
					{
						eval('$data = '.$c.'::configure();');
	
						foreach($data as $name => $type)
						{
							$this -> compiler -> translator[$name] = array(0 => $c, 1 => $type);				
						}
					}
				}
				else
				{
					eval('$data = '.$class.'::configure();');

					foreach($data as $name => $type)
					{
						$this -> translator[$name] = array(0 => $class, 1 => $type);
					}
				}
			}
			else
			{
				// OK, the compiler is not used. Just register. If the compiler is needed, it will translate
				// the call on its own.
				if(is_array($class))
				{
					$this -> control = array_merge($this->control, $class);				
				}
				else
				{
					$this -> control[] = $class;
				}
			}
		} // end registerInstruction();

		public function doParse($file)
		{
			$this -> captureTo = 'echo';
			$this -> captureDef = 'echo';
			if(!is_object($this -> compiler))
			{
				require(OPT_DIR.'opt.compiler.php');
				$this -> compiler = new optCompiler($this);
			}

			$this -> oldErrorReporting = ini_get('error_reporting');
			error_reporting(E_ALL ^ E_NOTICE);
			eval($this -> compiler -> parse(file_get_contents($this -> root.$file)));
			error_reporting($this -> oldErrorReporting);
		} // end doParse();

		abstract protected function doInclude($file, $nestingLevel);
	}
?>
