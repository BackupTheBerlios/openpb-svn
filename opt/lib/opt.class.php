<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                        Open Power Template                             //
  //         Copyright (c) 2005 OpenPB team, http://opt.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //
  //
  // $Id$

	define('OPT_FORCE_REWRITE', 1);	
	define('OPT_MAX_NESTING_LEVEL', 32);

	define('OPT_HTTP_CACHE', 1);
	define('OPT_NO_HTTP_CACHE', 2);

	define('OPT_HTML', 0);
	define('OPT_XHTML', 1);
	define('OPT_XML', 2);
	define('OPT_WML', 3);
	define('OPT_TXT', 4);

	define('OPT_ENABLED', 1);
	define('OPT_DISABLED', 0);

	define('OPT_PREFILTER', 0);
	define('OPT_POSTFILTER', 1);
	define('OPT_OUTPUTFILTER', 2);

	define('OPT_VERSION', '1.0.0');
	
	if(!defined('OPT_DIR'))
	{
		define('OPT_DIR', './');
	}
	
	include_once(OPT_DIR.'opt.error.php');
	include_once(OPT_DIR.'opt.components.php');
	
/************************
 *   O P T   C L A S S
 ************************/

	class optClass
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
		public $functions = array(
								'parse_int' => 'PredefParseInt',
								'wordwrap' => 'PredefWordwrap',
								'apply' => 'PredefApply',
								'cycle' => 'PredefCycle'			
							);
		public $phpFunctions = array(
								'upper' => 'strtoupper',
								'lower' => 'strtolower',
								'capitalize' => 'ucfirst',
								'trim' => 'trim',
								'length' => 'strlen',
								'count_words' => 'str_word_count',
								'count' => 'count',
								'date' => 'date'			
							);
		public $control = array(0 =>
								'optSection',
								'optShow',
								'optInclude',
								'optPlace',
								'optVar',
								'optIf',
								'optPhp',
								'optFor',
								'optForeach',
								'optCapture',
								'optDynamic',
								'optDefault'
							);
		public $components = array(
								'selectComponent' => 1,
								'textInputComponent' => 1,
								'textLabelComponent' => 1,
								'formActionsComponent' => 1			
							);
		public $delimiters = array(0 => 
								'\{(\/?)(.*?)(\/?)\}'
							);

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
		# DEBUG_CONSOLE
		private $debugOutput;
		private $debugConfig;
		private $debugCode;
		# /DEBUG_CONSOLE
		# OUTPUT_CACHING
		private $cacheStatus;
		private $cacheExpires;
		private $cacheId;
		private $cacheOutput;
		private $cacheTestResult;
		private $cacheResource;
		private $cacheHeader;
		# /OUTPUT_CACHING
		
		public $compileCode = '';
		private $includedFiles = array();
		private $testIncludedFiles;

		public function __destruct()
		{
			# DEBUG_CONSOLE
			if($this -> debugConsole)
			{
				$this -> data['config'] = array(0 => 
					array('name' => 'root', 'value' => $this -> root),
					array('name' => 'compile', 'value' => $this -> compile),
					array('name' => 'cache', 'value' => $this -> cache),
					array('name' => 'plugins', 'value' => $this -> plugins),
					array('name' => 'compileCacheDisabled', 'value' => $this -> compileCacheDisabled),
					# GZIP_SUPPORT
					array('name' => 'gzipCompression', 'value' => $this -> gzipCompression),
					# /GZIP_SUPPORT
					array('name' => 'charset', 'value' => $this -> charset),
					array('name' => 'safeMode', 'value' => $this -> safeMode)				
				);
				$this -> data['files'] = &$this->debugOutput;
			//	eval($this->debug_code);
				echo '<script language="JavaScript">
				opt_console = window.open("","OPT debug console","width=680,height=350,resizable,scrollbars=yes");
				opt_console.document.write("<HTML><TITLE>OPT debug console</TITLE><BODY bgcolor=#ffffff><h1>OPT DEBUG CONSOLE</h1>");
				opt_console.document.write(\'<table border="0" width="100%">\');
				'; if(count($this -> data['config']) > 0){ foreach($this -> data['config'] as $__config_id => &$__config_val){ echo '
				opt_console.document.write(\'<tr><td width="25%" bgcolor="#DDDDDD"><b>'.(string)($__config_val['name']).'</b></td>\'); 
				opt_console.document.write(\'<td width="75%" bgcolor="#EEEEEE">'.(string)($__config_val['value']).'</td></tr>\');
				'; } } echo '
				opt_console.document.write(\'</table><table border="0" width="100%"><tr><td width="25%" bgcolor="#CCCCCC"><b>Loaded file</b></td>\'); 
				opt_console.document.write(\'<td width="25%" bgcolor="#CCCCCC"><b>Problems</b></td>\');
				opt_console.document.write(\'<td width="25%" bgcolor="#CCCCCC"><b>Compile cache status</b></td>\');
				opt_console.document.write(\'<td width="25%" bgcolor="#CCCCCC"><b>Execution time</b></td></tr>\');
				'; if(count($this -> data['files']) > 0){ foreach($this -> data['files'] as $__files_id => &$__files_val){ echo '
				opt_console.document.write(\'<tr><td width="25%" bgcolor="#EEEEEE">'.(string)($__files_val['name']).'</td>\'); 
				opt_console.document.write(\'<td width="25%" bgcolor="#EEEEEE"><b>'.(string)($__files_val['problems']).'</b></td>\');
				opt_console.document.write(\'<td width="25%" bgcolor="#EEEEEE">'.(string)($__files_val['cache']).'</td>\');
				opt_console.document.write(\'<td width="25%" bgcolor="#EEEEEE">'.(string)($__files_val['exec']).' s</td></tr>\');
				'; } } echo '
				opt_console.document.write(\'</table>\');
				</script>
				';
			}
			# /DEBUG_CONSOLE
			if(count($this -> codeFilters['output']) > 0)
			{
				$output = ob_get_clean();
				foreach($this -> codeFilters['output'] as $filter)
				{
					$output = $filter($output, $this);
				}
				echo $output;
			}
			else
			{
				// potential bug
				// while(@ob_end_flush());
				@ob_end_flush();
			}
		} // end __destruct();

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

		public function httpHeaders($content, $cache = OPT_HTTP_CACHE)
		{
			if(headers_sent())
			{
				return 0;
			}

			$charset = '';
			if($this -> charset != NULL)
			{
				$charset = ';charset='.$this -> charset;
			}

			switch($content)
			{		
				case OPT_HTML:
						header('Content-type: text/html'.$charset);
						break;
				case OPT_XHTML:
						if(stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml'))
						{
							header('Content-type: application/xhtml+xml'.$charset);
						}
						else
						{
							header('Content-type: text/html'.$charset);
						}
						break;
				case OPT_XML:
						header('Content-type: application/xml'.$charset);
						break;
				case OPT_WML:
						header('Content-type: text/vnd.wap.wml'.$charset);
						break;
				case OPT_TXT:
						header('Content-type: text/plain'.$charset);
						break;
				default:
						if(is_string($content))
						{
							header('Content-type: '.$content.$charset);						
						}
						else
						{
							$this -> error(E_USER_ERROR, 'Unknown content type: '.$content, 1);
						}
			}
			if($cache == OPT_NO_HTTP_CACHE)
			{
				header('Expires: 0'); 
				header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); 
				// HTTP/1.1 
				header('Cache-Control: no-store, no-cache, must-revalidate'); 
				header('Cache-Control: post-check=0, pre-check=0', false);
				// HTTP/1.0 
				header('Pragma: no-cache');
			}
			# DEBUG_CONSOLE
			if($this->debugConsole)
			{
				$this -> debugConfig[] = array('name' => 'HTTP Headers', 'value' => implode('<br/>', headers_list()));
			}
			# /DEBUG_CONSOLE
		} // end httpHeaders();

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

		public function setCustomI18n($template, $applyClass, $postfilter = NULL)
		{
			$this -> i18nType = 1;
			$this -> lang = array(
				'template' => $template,
				'applyClass' => $applyClass	
			);

			if($postfilter != NULL)
			{
				$this -> registerFilter(OPT_POSTFILTER, $postfilter);			
			}
		} // setCustomI18n();

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

		public function parse($file)
		{
			$this -> captureTo = 'echo';
			$this -> captureDef = 'echo';
			return $this -> doParse($file, 0);
		} // end parse();

		public function parseCapture($file, $destination)
		{
			$this -> captureTo = '$this -> capture[\''.$destination.'\'] .=';
			$this -> captureDef = '$this -> capture[\''.$destination.'\'] .=';
			return $this -> doParse($file, 0);
		} // end parseCapture();

		public function fetch($file)
		{
			$this -> outputBuffer = '';
			$this -> captureTo = '$this -> outputBuffer .=';
			$this -> captureDef = '$this -> outputBuffer .=';
			$this -> doParse($file, 0);
			return $this -> outputBuffer;
		} // end fetch();

		private function doParse($file, $nestingLevel)
		{
			if($this -> init == 0)
			{
				require_once(OPT_DIR.'opt.functions.php');
				# GZIP_SUPPORT
				if($this -> gzipCompression == 1 && extension_loaded('zlib') && ini_get('zlib.output_compression') == 0)
				{
					ob_start('ob_gzhandler');
					ob_implicit_flush(0);					
				}
				# /GZIP_SUPPORT
				# PLUGIN_AUTOLOAD
				if($this -> plugins != NULL)
				{
					$this -> loadPlugins();
				}
				# /PLUGIN_AUTOLOAD
				$this -> init = 1;
			}
			$res = $this -> getResourceInfo($file, $file);
			# NESTING_LEVEL
			if($nestingLevel > OPT_MAX_NESTING_LEVEL)
			{
				$this -> error(E_USER_ERROR, 'Nesting level too deep.', 3);
			}
			# /NESTING_LEVEL
			$ok = 0;
			# OUTPUT_CACHING
			// Output caching enabled
			if($this -> cacheStatus == true)
			{
				if($this -> cacheProcess($file))
				{
					return 1;
				}
			}
			# /OUTPUT_CACHING

			// time generating
			# DEBUG_CONSOLE
			if($this -> debugConsole)
			{
				$time = microtime(true);
			}
			# /DEBUG_CONSOLE
			# DISABLED_CC
			if($this -> compileCacheDisabled == 1)
			{
				// The compile cache is disabled
				if(!is_object($this -> compiler))
				{
					require_once(OPT_DIR.'opt.compiler.php');
					$this -> compiler = new optCompiler($this);
				}
				$code = $this -> compiler -> parse($res -> loadSource($file));
				$ok = 1;
				# DEBUG_CONSOLE
				$useCache = 'no';
				# /DEBUG_CONSOLE
			}
			else
			{
			# /DISABLED_CC
				// The compile cache is enabled
				if($res -> isModified($file))
				{
					// The file is not compiled
					if(!is_object($this -> compiler))
					{
						require_once(OPT_DIR.'opt.compiler.php');
						$this -> compiler = new optCompiler($this);
					}
					$res -> lockCode($file);
					$code = $this -> compiler -> parse($res -> loadSource($file));
					$res -> saveCode($code);
					$ok = 1;
					# DEBUG_CONSOLE
					$useCache = 'generating';
					# /DEBUG_CONSOLE
				}
				else
				{
					// The file is compiled
					$code = $res -> loadCode($file);
					# DEBUG_CONSOLE
					$useCache = 'reading';
					# /DEBUG_CONSOLE
					$ok = 1;
				}
			
			# DISABLED_CC
			}
			# /DISABLED_CC

			// turn off the notices for the template execution time
			// and restore the old settings					
			$this -> oldErrorReporting = ini_get('error_reporting');
			error_reporting(E_ALL ^ E_NOTICE);
			eval($code);
			error_reporting($this -> oldErrorReporting);

			// if the programmer wants the template source...
			if($this -> showSource == 1)
			{
				$source = explode("\n", htmlspecialchars($code));
				echo '<hr/><b>Template Source:</b><br/><table style="width: 70%; border: 1px solid #000000;">';
				foreach($source as $num => $lineCode)
				{
					echo '<tr><td bgcolor="#DDDDDD" width="30">'.($num+1).'</td><td><pre>'.wordwrap($lineCode, 100, "\n").'</pre></td></tr>';						
				}
				echo '</table>';
			}
			# OUTPUT_CACHING
			if($this -> cacheStatus == true)
			{
				if(count($this -> cacheOutput) == 0)
				{
					$this -> cacheWrite($file);
				}
				else
				{
					$this -> cacheWrite($file, $code);
				}
			}
			# /OUTPUT_CACHING
			
			# DEBUG_CONSOLE
			if($this -> debugConsole)
			{
				$time = microtime(true) - $time;
				
				if(!isset($php_errormsg))
				{
					$problem = '<font color="blue">Error data unavailable</font>';
				}
				elseif(strpos($php_errormsg, 'Undefined') === 0 || $php_errormsg == '')
				{
					$problem = '<font color="green">no</font>';
				}
				else
				{
					$problem = '<font color="red">'.$php_errormsg.'</font>';
				}
				$this -> debugOutput[] = array(
					'name' => $file,
					'problems' => $problem,
					'cache' => $useCache,
					'exec' => round($time, 5)
				);
			}
			# /DEBUG_CONSOLE
			return $ok;
		} // end doParse();
		
		private function doInclude($file, $nestingLevel)
		{
			$res = $this -> getResourceInfo($file, $file);
			# NESTING_LEVEL
			if($nestingLevel > OPT_MAX_NESTING_LEVEL)
			{
				$this -> error(E_USER_ERROR, 'Nesting level too deep', 3);
			}
			# /NESTING_LEVEL
			$ok = 0;

			// time generating
			# DEBUG_CONSOLE
			if($this -> debugConsole)
			{
				$time = microtime(true);
			}
			# /DEBUG_CONSOLE

			if(!($included = in_array($file, $this->includedFiles)))
			{
				$res -> setTestStatus(1);
			}

			# DISABLED_CC
			if($this -> compileCacheDisabled == 1)
			{
				// The compile cache is disabled
				$code = $this -> compiler -> parse($res -> loadSource($file));
				$ok = 1;
				# DEBUG_CONSOLE
				$useCache = 'no';
				# /DEBUG_CONSOLE
			}
			else
			{
			# /DISABLED_CC
				// the template hasn't been processed yet
				if(!$included)
				{		
					// The compile cache is enabled
					if($res -> isModified($file))
					{
						// The file is not compiled
						if(!is_object($this -> compiler))
						{
							require_once(OPT_DIR.'opt.compiler.php');
							$this -> compiler = new optCompiler($this);
						}
						$res -> lockCode($file);
						$code = $this -> compiler -> parse($res -> loadSource($file));
						$res -> saveCode($code);
						$ok = 1;
						# DEBUG_CONSOLE
						$useCache = 'generating';
						# /DEBUG_CONSOLE
					}
					else
					{
						// The file is compiled
						$code = $res -> loadCode($file);
						# DEBUG_CONSOLE
						$useCache = 'reading';
						# /DEBUG_CONSOLE
						$ok = 1;
					}
					$this -> includedFiles[] = $file;
				}
				else
				{
					// The file is compiled
					$code = $res -> loadCode($file);
					# DEBUG_CONSOLE
					$useCache = 'reading';
					# /DEBUG_CONSOLE
					$ok = 1;
				}
			# DISABLED_CC
			}
			# /DISABLED_CC
	
			// turn off the notices for the template execution time
			// and restore the old settings					
			$this -> oldErrorReporting = ini_get('error_reporting');
			error_reporting(E_ALL ^ E_NOTICE);
			eval($code);
			error_reporting($this -> oldErrorReporting);
			$res -> setTestStatus(1);
			# DEBUG_CONSOLE
			if($this -> debugConsole)
			{
				$time = microtime(true) - $time;
				
				if(strpos($php_errormsg, 'Undefined') === 0 || $ $php_errormsg == '')
				{
					$problem = '<font color="green">no</font>';
				}
				else
				{
					$problem = '<font color="red">'.$php_errormsg.'</font>';
				}
				$this -> debugOutput[] = array(
					'name' => $file,
					'problems' => $problem,
					'cache' => $useCache,
					'exec' => round($time, 5)				
				);
			}
			# /DEBUG_CONSOLE
			
			return $ok;
		} // end doInclude();

		public function checkExistence($file)
		{
			$res = $this -> getResourceInfo($file, $file);

			if(!isset($this->testIncludedFiles[$file]))
			{
				return $this->testIncludedFiles[$file] = $res->templateExists($file);
			}
			else
			{
				return $this->testIncludedFiles[$file];
			}
		} // end checkExistence();

		public function registerFunction($name, $func = '')
		{
			if(is_array($name))
			{
				$this -> functions = $name;
				return 1;
			}
			else
			{
				if(strlen($name) > 0 && function_exists('opt'.$func))
				{
					$this -> functions[$name] = $func;
					return 1;
				}
			}
			return 0;
		} // end registerFunction();

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

		public function registerFilter($type, $callback)
		{
			if(count($this -> codeFilters['pre']) + count($this -> codeFilters['post']) + count($this -> codeFilters['output']) == 0)
			{
				require_once(OPT_DIR.'opt.filters.php');
			}
			switch($type)
			{
				case 0:
						$prefix = 'optPrefilter';
						$idx = 'pre';
						break;
				case 1:
						$prefix = 'optPostfilter';
						$idx = 'post';
						break;
				case 2:
						$prefix = 'optOutputfilter';
						$idx = 'output';
						break;
				default:
						return 0;			
			}
			if(function_exists($prefix.$callback))
			{
				$this -> codeFilters[$idx][] = $prefix.$callback;			
				return 1;
			}
			else
			{
				$this -> error(E_USER_ERROR, 'Specified '.$idx.' filter function: `'.$callback.'` does not exist!', 4);
			}
		} // end registerFilter();

		# CUSTOM_RESOURCES
		public function registerResource($name, $resourceName)
		{
			if(class_exists($resourceName))
			{
				$this -> resources[$name] = new $resourceName($this);
				return 1;
			}
			$this -> error(E_USER_ERROR, 'Specified value is not a valid resource class name.', 5);	
		} // end registerResource();
		# /CUSTOM_RESOURCES

		public function unregisterFilter($type, $callback)
		{
			switch($type)
			{
				case 0:
						if(isset($this -> codeFilters['pre']['optPrefilter'.$callback]))
						{
							unset($this -> codeFilters['pre']['optPrefilter'.$callback]);
							return 1;
						}
						break;
				case 1:
						if(isset($this -> codeFilters['post']['optPostfilter'.$callback]))
						{
							unset($this -> codeFilters['post']['optPostfilter'.$callback]);
							return 1;
						}
						break;
				case 2:
						if(isset($this -> codeFilters['output']['optOutputfilter'.$callback]))
						{
							unset($this -> codeFilters['output']['optOutputfilter'.$callback]);
							return 1;
						}
						break;
			}
			return 0;
		} // end unregisterFilter();

		public function loadConfig($data)
		{	
			$configDirectives = array(0=>
				'root', 'compile', 'cache', 'plugins',
				'gzipCompression', 'compileCacheDisabled', 'showWarnings', 'showSource', 'charset',
				'safeMode', 'debugConsole', 'trace', 'rewriteWarnings', 'xmlsyntaxMode',
				'strictSyntax', 'parseintDecPoint', 'parseintDecimals', 'parseIntThousands'
			);

			if(is_string($data))
			{
				$data = parse_ini_file($data);			
			}
			elseif(!is_array($data))
			{
				$this -> error(E_USER_ERROR, 'Invalid configuration data format. Array or file name required.');
			}

			foreach($configDirectives as $name)
			{
				if(isset($data[$name]))
				{
					$this -> $name = $data[$name];
				}			
			}
		} // end loadConfig();

		public function compileCacheReset($resource = 'file', $filename = NULL)
		{
			if(isset($this -> resources[$resource]))
			{
				$res = $this -> resources[$resource];
			}
			else
			{
				$this -> error(E_USER_ERROR, 'Specified resource type: '.$resource.' does not exist.', 6);
			}
			$res -> compileCacheReset($filename);
		} // end compileCacheReset();

		# OUTPUT_CACHING
		public function cacheStatus($status, $expires = 3600)
		{
			$this -> cacheStatus = $status;
			if($expires < 2)
			{
				$this -> cacheExpires = 2;
			}
			else
			{
				$this -> cacheExpires = $expires;
			}		
		} // end cacheStatus();
		
		public function getStatus()
		{
			return $this -> cacheStatus;
		} // end getStatus();

		public function cacheUnique($id = NULL)
		{
			$this -> cacheId = str_replace('/', '^', $id);
		} // end cacheUnique();

		public function isCached($filename, $id = NULL)
		{
			$buf = $this -> cacheId;
			if($id != NULL)
			{				
				$this -> cacheId = $id;
			}
			else
			{
				$this -> cacheId = NULL;
			}
			$c = $this->cd($filename);
			if(file_exists($c))
			{

				$ok = $this -> cacheTest($c, $filename);
				if($ok == 1)
				{
					$this -> cacheTestResult = 1;
				}
				else
				{
					fclose($this -> cacheResource);
					unlink($c);
					$this -> cacheTestResult = 1;
				}
			}
			else
			{
				$ok = 0;
			}

			$this -> cacheId = $buf;
			return $ok;			
		} // end isCached();

		public function cacheReset($filename = NULL, $id = NULL, $expire_time = NULL)
		{
			if($filename == NULL && $id == NULL)
			{
				$dir = opendir($this -> cache);
				while($f = readdir($dir))
				{
					if($expire_time != NULL)
					{
						$expire = $this -> checkExpire($file, $expire_time);
					}
					else
					{
						$expire = 1;
					}
					if(is_file($this -> cache.$f) && $expire)
					{
						unlink($this -> cache.$f);
					}
				}
				closedir($dir);
				return 1;
			}
			elseif($filename == NULL)
			{
				$id = str_replace('|', '^', $id);
				$dir = glob($this -> cache.$id.'*_*.*', GLOB_BRACE);
				foreach($dir as $file)
				{
					if($expire_time != NULL)
					{
						$expire = $this -> checkExpire($file, $expire_time);
					}
					else
					{
						$expire = 1;
					}
					if(is_file($file) && $expire)
					{
						unlink($file);
					}
				}
				return 1;
			}
			elseif($id == NULL)
			{
				$dir = glob($this -> cache.'*_'.$this->cd($filename, true));
				foreach($dir as $file)
				{
					if($expire_time != NULL)
					{
						$expire = $this -> checkExpire($file, $expire_time);
					}
					else
					{
						$expire = 1;
					}
					if(is_file($file) && $expire)
					{
						unlink($file);
					}
				}
				return 1;
			}
			else
			{
				$id = str_replace('|', '^', $id);
				$dir = glob($this -> cache.$id.'*_'.$this->cd($filename, true), GLOB_BRACE);
				foreach($dir as $file)
				{
					if($expire_time != NULL)
					{
						$expire = $this -> checkExpire($file, $expire_time);
					}
					else
					{
						$expire = 1;
					}
					if(is_file($file) && $expire)
					{
						unlink($file);
					}
				}
				return 1;
			
			}
		} // end cacheReset();

		private function cacheProcess($filename)
		{
			$c = $this->cd($filename);
			$this -> cacheOutput = array();
			if(file_exists($c))
			{
				$mod = filemtime($c);
				if($mod < (time() - $this -> cacheExpires))
				{
					// recompilation
					ob_start();
					return 0;				
				}
				else
				{
					if($this -> cacheTestResult == 0)
					{
						$result = $this -> cacheTest($c, $filename);
						if($result == 0)
						{
							// recompilation
							return 0;
						}
					}
					else
					{
						$this -> cacheTestResult = 0;
					}
					
					// ok, all tests passed, read
					// read the stuff
					$content = fread($this -> cacheResource, filesize($c));
					
					if($this -> cacheHeader['dynamic'] == true)
					{
						// there is some dynamic stuff.
						eval($content);
					}
					else
					{
						// whole file is static
						echo $content;
					}
					fclose($this -> cacheResource);
					return 1;			
				}			
			}
			ob_start();
			return 0;		
		} // end cacheProcess();

		private function cacheWrite($filename, $code = NULL)
		{
			$c = $this->cd($filename);
			$content = 'echo(\'Unknown content!\');';
			if(count($this -> cacheOutput) == 0)
			{
				// ok, the content is static!
				$dynamic = false;
				$content = ob_get_contents();
				ob_start();
			}
			else
			{
				// the content is dynamic
				$dynamic = true;

				if(preg_match_all('#\/\* \#\@\#DYNAMIC\#\@\# \*\/(.+)\/\* \#\@\#END DYNAMIC\#\@\# \*\/#si', $code, $blocks))
				{
					$content = $this -> captureDef.' \'';
					for($i = 0; $i < count($this -> cacheOutput); $i++)
					{
						$content .= addslashes($this->cacheOutput[$i]);
						$content .= '\'; ';
						$content .= $blocks[1][$i];
						$content .= ' '.$this -> captureDef.' \'';					
					}
					$content .= addslashes(ob_get_clean()).'\';';
				}
				else
				{
					$this -> error(E_USER_ERROR, 'Critical caching system error: invalid dynamic delimiters!', 7);
				}
			}
			
			// generate the file
			$header = array(
				'timestamp' => time(),
				'copy_version' => filemtime($this->root.$filename),
				'dynamic' => $dynamic			
			);
			
			file_put_contents($c, serialize($header)."\n".$content);		
		} // end cacheWrite();
		
		private function cacheTest($c, $filename)
		{
			$this -> cacheResource = fopen($c, 'r');
			$this -> cacheHeader = unserialize(fgetss($this -> cacheResource));
			
			if(!is_array($this -> cacheHeader))
			{
				// the cache is broken, recompile
				return 0;
			}
			
			// one more time we will control the timestamp
			if($this -> cacheHeader['timestamp'] < (time() - (int)$this -> cacheExpires))
			{
				// ok, the filesystem was wrong, we should recompile this...
				return 0;
			}
			
			// is the original template unchanged?
			if($this -> cacheHeader['copy_version'] != filemtime($this->root.$filename))
			{
				// there are some differences, recompile
				return 0;
			}
			return 1;
		} // end cacheTest();

		private function cd($filename, $raw = 0)
		{
			if($raw == 1)
			{
				return base64_encode(dirname($filename)).basename($filename);
			}
			// cache filename hashing
			if($this -> cacheId == NULL)
			{
				$id = '';
			}
			else
			{
				$id = $this -> cacheId;
			}
			return $this -> cache.str_replace('|','^',$id).'_'.base64_encode(dirname($filename)).basename($filename);
		} // end cd();
		
		private function checkExpire($file, $time)
		{
			if($time == 0)
			{
				return 1;
			}

			$f = fopen($file, 'r');
			$header = unserialize(fgetss($f));
			fclose($f);
			if($header['timestamp'] < (time() - $time))
			{
				return 1;
			}
			return 0;		
		} // end checkExpire();
		# /OUTPUT_CACHING
		# PLUGIN_AUTOLOAD
		private function loadPlugins()
		{
			if(file_exists($this -> plugins.'plugins.php'))
			{
				// Load precompiled plugin database
				include($this -> plugins.'plugins.php');	
			}
			else
			{
				// Compile plugin database
				if(!is_dir($this -> plugins))
				{
					return 0;
				}

				$code = '';
				$file = '';
				$dir = opendir($this -> plugins);
				while($file = readdir($dir))
				{
					if(preg_match('/(component|instruction|function|prefilter|postfilter|outputfilter|resource)\.([a-zA-Z0-9\_]+)\.php/', $file, $matches))
					{
						switch($matches[1])
						{
							case 'component':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->components['".$matches[2]."'] = 1;\n";
								break;
							case 'instruction':
								$this -> compileCode .= "\trequire(\$this -> tpl-> plugins.'".$file."');\n";
								$this -> compileCode .= "\t\$this->tpl->control[] = '".$matches[2]."';\n";
								break;
							case 'function':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->functions['".$matches[2]."'] = '".$matches[2]."';\n";
								break;
							case 'prefilter':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->codeFilters['pre'][] = 'optPrefilter".$matches[2]."';\n";
								break;
							case 'postfilter':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->codeFilters['post'][] = 'optPostfilter".$matches[2]."';\n";
								break;
							case 'outputfilter':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->codeFilters['output'][] = 'optOutputfilter".$matches[2]."';\n";
								break;
							case 'resource':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->resources[".$matches[2]."] = new \$".$matches[4]."(\$this);\n";
								break;
						}	
					}
				}
				closedir($dir);
				file_put_contents($this -> plugins.'plugins.php', "<?php\n".$code."?>");
				file_put_contents($this -> plugins.'compile.php', "<?php\n".$this -> compileCode."?>");
				eval($code);
			}
			return 1;
		} // end loadPlugins();
		# /PLUGIN_AUTOLOAD
		
		public function getResourceInfo($path, &$file)
		{
			if(!isset($this->resources['file']))
			{
				require_once(OPT_DIR.'opt.resources.php');
				$this -> resources['file'] = new optResourceFiles($this);
			}
			# CUSTOM_RESOURCES
			if(strpos($path, ':') !== FALSE)
			{
				$resource = explode(':', $path);
				$file = $resource[1];
				if(isset($this -> resources[$resource[0]]))
				{
					return $this -> resources[$resource[0]];
				}
				else
				{
					$this -> error(E_USER_ERROR, 'Specified resource type: '.$res_name.' does not exist.', 8);
				}
			}
			else
			{
			# /CUSTOM_RESOURCES
				$file = $path;
				return $this->resources['file'];
			# CUSTOM_RESOURCES
			}
			# /CUSTOM_RESOURCES
		} // end getResourceInfo();
	}
?>
