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

	define('OPT_SECTION_MULTI', 0);
	define('OPT_SECTION_SINGLE', 1);
	define('OPT_PRIORITY_NORMAL', 0);
	define('OPT_PRIORITY_HIGH', 1);
	define('OPT_VERSION', '1.0.0');

	if(!defined('OPT_DIR'))
	{
		define('OPT_DIR', './');
	}

	require(OPT_DIR.'opt.error.php');

	abstract class optApi
	{
		// Configuration
		public $root = NULL;
		public $compile = NULL;
		public $cache = NULL;
		public $plugins = NULL;

		public $gzipCompression = false;
		public $charset = NULL;

		public $alwaysRebuild = false;
		public $showWarnings = true;
		public $debugConsole = false;
		public $performance = false;

		public $xmlsyntaxMode = false;
		public $strictSyntax = false;
		public $entities = false;
		public $sectionStructure = OPT_SECTION_MULTI;
		public $statePriority = OPT_PRIORITY_NORMAL;

		public $parseintDecPoint = '.';
		public $parseintDecimals = 3;
		public $parseintThousands = ',';

		// Parser and compiler data
		protected $init = false;
		protected $outputBufferEnabled = false;
		public $compiler;
		public $data = array();
		public $vars = array();
		public $capture = array();
		public $functions = array();
		public $phpFunctions = array();
		public $control = array();
		# COMPONENTS
		public $components = array();
		# /COMPONENTS
		public $delimiters = array(0 => 
								'\{(\/?)(.*?)(\/?)\}'
							);
		public $filters = array(
								'pre' => NULL,
								'post' => NULL,
								'output' => NULL
							);
		public $instructionFiles = array();
		
		// I18n
		public $i18n = NULL;
		public $i18nType = 0;

		
		public function __construct()
		{
			$this -> compileCode = '';		
		} // end __construct();

		public function error($type, $message, $code)
		{
			require_once(OPT_DIR.'opt.core.php');
			optErrorMessage($this, $type, $message, $code);
		} // end error();

		// Methods		
		public function assign($name, $value)
		{
			$this -> data[$name] = $value;		
		} // end assign();

		public function assignGroup($values)
		{
			if(!is_array($values))
			{
				return false;
			}
		
			foreach($values as $name => &$value)
			{
				$this -> data[$name] = $value;
			}	
		} // end assignGroup();

		public function assignRef($name, &$value)
		{
			$this -> data[$name] = $value;		
		} // end assignRef();

		public function setDefaultI18n(&$lang)
		{
			$this -> i18nType = 0;
			if(is_array($lang))
			{
				$this -> i18n = &$lang;
			}
			else
			{
				$this -> error(E_USER_ERROR, 'First parameter must be an array.', 2);
			}
		} // end setDefaultI18n();
		
		public function registerInstruction($class)
		{
			if(is_object($this -> compiler))
			{
				// The compiler is already initialized, we have to translate this call like the compiler does.
				if(!is_array($class))
				{
					$class = array(0 => $class);
				}
				$this -> compiler -> translate($class);
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

		} // end doParse();

		abstract protected function doInclude($file, $nestingLevel);
	}
?>
