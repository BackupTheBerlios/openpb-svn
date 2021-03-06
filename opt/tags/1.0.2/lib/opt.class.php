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
  // $Id: opt.class.php 59 2006-08-02 11:29:55Z zyxist $

	define('OPT_HTTP_CACHE', 1);
	define('OPT_NO_HTTP_CACHE', 2);

	define('OPT_HTML', 0);
	define('OPT_XHTML', 1);
	define('OPT_XML', 2);
	define('OPT_WML', 3);
	define('OPT_TXT', 4);
	define('OPT_FORCED_XHTML', 5);

	define('OPT_PREFILTER', 0);
	define('OPT_POSTFILTER', 1);
	define('OPT_OUTPUTFILTER', 2);
	
	define('OPT_SECTION_MULTI', 0);
	define('OPT_SECTION_SINGLE', 1);
	define('OPT_PRIORITY_NORMAL', 0);
	define('OPT_PRIORITY_HIGH', 1);

	define('OPT_VERSION', '1.0.2');
	
	if(!defined('OPT_DIR'))
	{
		define('OPT_DIR', './');
	}
	
	// Additional interfaces
	
	include_once(OPT_DIR.'opt.error.php');
	# COMPONENTS
	
	interface ioptComponent
	{
		public function __construct($name = '');
		public function setOptInstance(optClass $tpl);
		public function set($name, $value);
		public function push($name, $value, $selected = false);
		public function setDatasource(&$source);
		public function begin();
		public function end();
	}

	# PREDEFINED_COMPONENTS
	include_once(OPT_DIR.'opt.components.php');
	# /PREDEFINED_COMPONENTS
	# /COMPONENTS
	
	# OBJECT_I18N
	interface ioptI18n
	{
		public function setOptInstance(optClass $tpl);
		public function put($group, $id);
		public function putApply($group, $id);
		public function apply($group, $id);
	}
	# /OBJECT_I18N
	
	function optPostfilterStripWhitespaces(optClass $tpl, $code)
	{
		return preg_replace('/(\r|\n){1,2}[ \t\f]*\<\?(.+)\?\>[ \t\f]*(\r|\n){1,2}/s', '<'.'?$2?'.'>$3', $code);	
	} // end optPostfilterStripWhitespaces();
	
	// OPT Parser class

	class optClass
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
		protected $contentType = '';
		private $filenames = array();
		public $compiler;
		public $data = array();
		public $vars = array();
		public $capture = array();
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
								'count' => 'sizeof',
								'date' => 'date',
								'array' => 'array'		
							);
		public $control = array(0 =>
								'optSection',
								'optInclude',
								'optPlace',
								'optVar',
								'optIf',
								'optFor',
								'optForeach',
								'optCapture',
								'optDynamic',
								'optDefault',
								'optBind',
								'optInsert',
								'optBindEvent'		
							);
		# COMPONENTS
		public $components = array(
							# PREDEFINED_COMPONENTS
								'selectComponent' => 1,
								'textInputComponent' => 1,
								'textLabelComponent' => 1,
								'formActionsComponent' => 1
							# /PREDEFINED_COMPONENTS		
							);
		# /COMPONENTS
		public $delimiters = array(0 => 
								'\{(\/?)(.*?)(\/?)\}'
							);
		public $filters = array(
								'pre' => array(),
								'post' => array(),
								'output' => array()
							);
		public $instructionFiles = array();
		
		// I18n
		public $i18n = NULL;
		public $i18nType = 0;
		
		# DEBUG_CONSOLE
		private $debugOutput = array();
		private $totalTime = 0;
		private $realPath = '';
		# /DEBUG_CONSOLE
		
		// Output cache
		private $cacheStatus = false;
		# OUTPUT_CACHING
		private $cacheId = NULL;
		private $cacheExpire = 0;
		private $cacheDynamic = false;
		private $cacheData = array();
		# /OUTPUT_CACHING
		private $outputBuffer = array();
		
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

		# HTTP_HEADERS
		public function httpHeaders($content, $cache = OPT_HTTP_CACHE)
		{
			$charset = '';
			if($this -> charset != NULL)
			{
				$charset = ';charset='.$this -> charset;
			}

			switch($content)
			{		
				case OPT_HTML:
						$this -> contentType = 'text/html';
						break;
				case OPT_XHTML:
						if(preg_match('/application\/xhtml\+xml(?![+a-z])(;q=(0\.\d{1,3}|[01]))?/i', $_SERVER['HTTP_ACCEPT'], $matches))
						{
							$xhtmlQ = isset($matches[2]) ? ($matches[2]+0.2) : 1;
							if(preg_match('/text\/html(;q=(0\.\d{1,3}|[01]))s?/i', $_SERVER['HTTP_ACCEPT'], $matches))
							{
								$htmlQ = isset($matches[2]) ? $matches[2] : 1;
								if($xhtmlQ >= $htmlQ)
								{
									$this -> contentType = 'application/xhtml+xml';
									break;
								}
							}
							else
							{
								$this -> contentType = 'application/xhtml+xml';
								break;
							}
						}
						$this -> contentType = 'text/html';
						break;
				case OPT_FORCED_XHTML:
						if(stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml'))
						{
							$this -> contentType = 'application/xhtml+xml';
						}
						else
						{
							$this -> contentType = 'text/html';
						}
						break;
				case OPT_XML:
						$this -> contentType = 'application/xml';
						break;
				case OPT_WML:
						$this -> contentType = 'text/vnd.wap.wml';
						break;
				case OPT_TXT:
						$this -> contentType = 'text/plain';
						break;
				default:
						if(is_string($content))
						{
							$this -> contentType = $content;
						}
						else
						{
							$this -> error(E_USER_ERROR, 'Unknown content type: '.$content, OPT_E_CONTENT_TYPE);
						}
			}
			if($this -> contentType == 'application/xhtml+xml' && $this -> debugConsole)
			{
				$this -> contentType .= ' (text/html used for debug purposes)';
				$this -> header('Content-type: text/html'.$charset);
			}
			else
			{ 
				$this -> header('Content-type: '.$this -> contentType.$charset);
			}
			if($cache == OPT_NO_HTTP_CACHE)
			{
				$this -> header('Expires: 0'); 
				$this -> header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
				// HTTP/1.1
				$this -> header('Cache-Control: no-store, no-cache, must-revalidate'); 
				$this -> header('Cache-Control: post-check=0, pre-check=0', false);
				// HTTP/1.0 
				$this -> header('Pragma: no-cache');
			}
		} // end httpHeaders();
		# /HTTP_HEADERS

		public function loadConfig($data)
		{
			$configDirectives = array(0=>
				'root', 'compile', 'cache', 'plugins',
				'gzipCompression', 'charset', 'showWarnings', 'debugConsole', 'alwaysRebuild',
				'performance', 'xmlsyntaxMode', 'strictSyntax', 'entities', 'sectionStructure',
				'statePriority', 'parseintDecPoint', 'parseintDecimals', 'parseintThousands'
			);
			
			if(!is_array($data))
			{
				$data = parse_ini_file($data);
			}

			foreach($configDirectives as $name)
			{
				if(isset($data[$name]))
				{
					$this -> $name = $data[$name];
				}
			}
		} // end loadConfig();

		public function setDefaultI18n(&$lang)
		{
			$this -> i18nType = 0;
			if(is_array($lang))
			{
				$this -> i18n = &$lang;
			}
			else
			{
				$this -> error(E_USER_ERROR, 'First parameter must be an array.', OPT_E_ARRAY_REQUIRED);
			}
		} // end setDefaultI18n();

		# OBJECT_I18N
		public function setObjectI18n(ioptI18n $i18n)
		{
			$this -> i18nType = 1;
			$this -> i18n = $i18n;
		} // end setObjectI18n();
		# /OBJECT_I18N
		# REGISTER_FAMILY
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

		public function registerFunction($name, $callback = NULL)
		{
			if(is_array($name))
			{
				$this -> functions = array_merge($this -> functions, $name);
				return true;
			}
			else
			{
				if(strlen($name) > 0)
				{
					$this -> functions[$name] = $callback;
					return true;
				}
			}
			return false;
		} // end registerFunction();
		
		public function registerPhpFunction($name, $callback = NULL)
		{
			if(is_array($name))
			{
				$this -> phpFunctions = array_merge($this -> phpFunctions, $name);
				return true;
			}
			else
			{
				if(strlen($name) > 0)
				{
					$this -> phpFunctions[$name] = $callback;
					return true;
				}
			}
			return false;
		} // end registerPhpFunction();

		public function registerResource($name, $callback)
		{
			if(function_exists('optResource'.$callback))
			{
				$this -> resources[$name] = 'optResource'.$callback;
				return true;
			}
			$this -> error(E_USER_ERROR, 'Specified value: "optResource'.$callback.'" is not a valid resource function name.', OPT_E_RESOURCE);
		} // end registerResource();

		public function registerComponent($name)
		{
			if(is_array($name))
			{
				foreach($name as $componentName)
				{
					$this -> components[$componentName] = 1;
				}		
			}
			else
			{
				$this -> components[$name] = 1;
			}
		} // end registerComponent();

		public function registerFilter($type, $callback)
		{
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
						return false;
			}
			if(function_exists($prefix.$callback))
			{
				$this -> filters[$idx][] = $prefix.$callback;
				return true;
			}
			else
			{
				$this -> error(E_USER_ERROR, 'Specified value: "'.$prefix.$callback.'" is not a valid OPT filter function name.', OPT_E_FILTER);
			}
		} // end registerFilter();

		public function unregisterFilter($type, $callback)
		{
			switch($type)
			{
				case 0:
						if(($id = in_array('optPrefilter'.$callback, $this -> filters['pre'])) !== FALSE)
						{
							unset($this -> filters['post'][$id]);
							return true;
						}
						break;
				case 1:
						if(($id = in_array('optPostfilter'.$callback, $this -> filters['post'])) !== FALSE)
						{
							unset($this -> filters['post'][$id]);
							return true;
						}
						break;
				case 2:
						if(($id = in_array('optOutputfilter'.$callback, $this -> filters['output'])) !== FALSE)
						{
							unset($this -> filters['post'][$id]);
							return true;
						}
						break;
			}
			return false;
		} // end unregisterFilter();
		# /REGISTER_FAMILY
		public function registerInstructionFile($file)
		{
			$this -> instructionFiles[] = $file;
		} // end registerInstructionFile();
		
		public function parse($filename)
		{
			$this -> fetch($filename, true);
		} // end parse();

		public function parseCapture($filename, $destination)
		{
			$this -> capture[$destination] = $this -> fetch($filename);
		} // end parseCapture();

		public function fetch($filename, $display = false)
		{
			static $init;
			if(is_null($init))
			{
				$init = 1;
				require_once(OPT_DIR.'opt.functions.php');
				# GZIP_SUPPORT
				if($this -> gzipCompression == true && extension_loaded('zlib') && ini_get('zlib.output_compression') == 0 && !$this -> outputBufferEnabled)
				{
					ob_start('ob_gzhandler');
					ob_implicit_flush(0);
					$this -> outputBufferEnabled = true;
				}
				# /GZIP_SUPPORT
				# PLUGIN_AUTOLOAD
				if($this -> plugins != NULL)
				{
					$this -> loadPlugins();
				}
				# /PLUGIN_AUTOLOAD
				# DEBUG_CONSOLE
				if($this -> debugConsole)
				{
					$this -> realPath = realpath(OPT_DIR.'opt.core.php');
				}
				# /DEBUG_CONSOLE
			}
			
			array_push($this -> filenames, $filename);
			
			if(!$display || sizeof($this -> filters['output']) > 0)
			{
				ob_start();
			}
			$cached = false;
			$dynamic = false;
			if($this -> performance)
			{
				# OUTPUT_CACHING
				if($this -> cacheStatus == true)
				{
					if(!$this -> cacheProcess($filename))
					{
						$filename = optCompileFilenameFull($filename);
						$oldErrorReporting = error_reporting(E_ALL ^ E_NOTICE);
						include($this -> compile.$filename);
						error_reporting($oldErrorReporting);
						$this -> cacheWrite($filename, $dynamic);
						$cached = true;
					}
				}
				else
				{
				# /OUTPUT_CACHING
					$oldErrorReporting = error_reporting(E_ALL ^ E_NOTICE);
					include($this -> compile.optCompileFilenameFull($filename));
					error_reporting($oldErrorReporting);
				# OUTPUT_CACHING
				}
				# /OUTPUT_CACHING
			}
			else
			{
				# DEBUG_CONSOLE
				if($this -> debugConsole)
				{
					$time = microtime(true);
				}
				# /DEBUG_CONSOLE
				# OUTPUT_CACHING
				if($this -> cacheStatus == true)
				{
					if(!$this -> cacheProcess($filename))
					{
						$compiled = $this -> needCompile($filename);
						$oldErrorReporting = error_reporting(E_ALL ^ E_NOTICE);
						include($this -> compile.$compiled);
						error_reporting($oldErrorReporting);
						$this -> cacheWrite($compiled, $dynamic);
						$cached = true;
					}
				}
				else
				{
				# /OUTPUT_CACHING
					$compiled = $this -> needCompile($filename);
					$oldErrorReporting = error_reporting(E_ALL ^ E_NOTICE);
					include($this -> compile.$compiled);
					error_reporting($oldErrorReporting);
				# OUTPUT_CACHING
				}
				# /OUTPUT_CACHING
				# DEBUG_CONSOLE
				if($this -> debugConsole)
				{
					$this -> totalTime += $time = microtime(true) - $time;
					$this -> debugOutput[] = array(
						'template' => $filename,
						'cached' => $cached,
						'problems' => (!isset($php_errormsg) || strpos($php_errormsg, 'Undefined') === 0 || $php_errormsg == '') ? '&nbsp;' : $php_errormsg,
						'cache' => ($this -> cacheStatus ? 'Yes' : 'No'),
						'exec' => round($time, 5)				
					);
				}
				# /DEBUG_CONSOLE
			}
			
			array_pop($this -> filenames);
			
			// Parse output filters
			if(sizeof($this -> filters['output']) > 0)
			{
				$content = ob_get_clean();
				foreach($this -> filters['output'] as $filter)
				{
					$content = $filter($this, $content);
				}
				if(!$display)
				{
					return $content;
				}
				echo $content;
			}
			// Return by default
			if(!$display)
			{
				$text = ob_get_clean();
				return $text;
			}
		} // end fetch();
		
		private function doInclude($filename, $default = false)
		{
			array_push($this -> filenames, $filename);
			if($this -> performance)
			{
				if($default == true)
				{
					if(!file_exists($filename = $this -> compile.optCompileFilenameFull($filename)))
					{
						return false;					
					}
					$oldErrorReporting = error_reporting(E_ALL ^ E_NOTICE);
					include($this -> compile.optCompileFilenameFull($filename));
					error_reporting($oldErrorReporting);
				}
				else
				{
					$oldErrorReporting = error_reporting(E_ALL ^ E_NOTICE);
					include($this -> compile.optCompileFilenameFull($filename));
					error_reporting($oldErrorReporting);
				}
			}
			else
			{
				$compiled = $this -> needCompile($filename, true);
				if($compiled == NULL)
				{
					return false;
				}
				$oldErrorReporting = error_reporting(E_ALL ^ E_NOTICE);
				include($this -> compile.$compiled);
				error_reporting($oldErrorReporting);
			}
			array_pop($this -> filenames);
			return true;
		} // end doInclude();

		public function compileCacheReset($filename = NULL)
		{
			require_once(OPT_DIR.'opt.core.php');
			optCompileCacheReset($filename, $this -> compile);
		} // end compileCacheReset();
		# OUTPUT_CACHING
		public function cacheReset($filename = NULL, $id = NULL, $expireTime = NULL)
		{
			require_once(OPT_DIR.'opt.core.php');
			optCacheReset($filename, $id, $expireTime, $this -> cache, $this -> root);
		} // end cacheReset();

		public function cacheStatus($status, $expire = 2)
		{
			$this -> cacheStatus = $status;
			$this -> cacheExpire = $expire;
		} // end cacheReset();

		public function getStatus()
		{
			return $this -> cacheStatus;
		} // end cacheReset();

		public function cacheUnique($id = NULL)
		{
			$this -> cacheId = $id;
		} // end cacheReset();

		public function isCached($filename, $id = NULL)
		{
			$hash = base64_encode($filename.$id);
			$this -> cacheFilename = optCacheFilename($filename, $id);
			if(!isset($this -> cacheData[$hash]))
			{
				// Need to check, hasn't been done yet
				$header = @unserialize(file_get_contents($this -> cache.$this -> cacheFilename.'.def'));
				
				if(!is_array($header))
				{
					$this -> cacheBuffer[$hash]['ok'] = false;
					return false;
				}
				
				$this -> cacheDynamic = $header['dynamic'];
				if($header['timestamp'] < (time() - (int)$header['expire']))
				{
					$this -> cacheBuffer[$hash]['ok'] = false;
					return false;
				}
				$this -> cacheBuffer[$hash]['ok'] = true;
				return true;
			}
			$this -> cacheDynamic = $this -> cacheData[$hash]['dynamic'];
			return $this -> cacheBuffer[$hash]['ok'];
		} // end cacheReset();
		# /OUTPUT_CACHING
		
		public function __destruct()
		{
			# GZIP_SUPPORT
			if($this -> outputBufferEnabled)
			{
				ob_end_flush();
			}
			# /GZIP_SUPPORT
			# DEBUG_CONSOLE
			if($this -> debugConsole)
			{
				// Including opt.core.php
				// This solution is used because of PHP bug #36454
				if($this -> realPath != '')
				{
					require_once($this -> realPath);
					
					optShowDebugConsole(array(
						'Root directory' => $this -> root,
						'Compile directory' => $this -> compile,
						'Plugin directory' => (!is_null($this -> plugins) ? $this -> plugins : '&nbsp;'),
						'Cache directory' => (!is_null($this -> cache) ? $this -> cache : '&nbsp;'),
						'GZip compression' => $this -> gzipCompression,
						'Always rebuild' => ($this->alwaysRebuild==true ? '<font color="red">Yes</font> (Please turn off this option to improve performance)' : 'No'),
						'Performance tuning' => ($this->performance==true ? '<font color="green">Yes</font>' : 'No'),
						'Charset' => (!is_null($this -> charset) ? $this -> charset : '&nbsp;'),
						'Content-type' => $this -> contentType,
						'Total template time' => round($this -> totalTime, 6).' s'				
					),$this -> debugOutput);
				}
			}
			# /DEBUG_CONSOLE
		} // end __destruct();
		
		public function error($type, $message, $code)
		{
			require_once(OPT_DIR.'opt.core.php');
			optErrorMessage($this, $type, $message, $code, end($this->filenames));
		} // end error();
		
		private function needCompile($filename, $noException = false)
		{
			$compiled = optCompileFilename($filename);
			$resource = 'file';
			if(strpos($filename, ':') !== FALSE)
			{
				$data = explode(':', $filename);
				$filename = $data[1];
				$resource = $data[0];
				
				if(!isset($this -> resources[$resource]))
				{
					if($noException)
					{
						return NULL;
					}
					$this -> error(E_USER_ERROR, 'Specified resource type: "'.$resource.'" does not exist.', OPT_E_RESOURCE_NOT_FOUND);
				}
				$callback = $this -> resources[$resource];
			}
			
			$compiledTime = @filemtime($this -> compile.$compiled);
			$result = false;
			if($resource == 'file')
			{
				$rootTime = @filemtime($this -> root.$filename);
				if($rootTime === false)
				{
					if($noException)
					{
						return NULL;
					}
					$this -> error(E_USER_ERROR, '"'.$filename.'" not found in '.$this->root.' directory.', OPT_E_FILE_NOT_FOUND);
				}
				if($compiledTime === false || $compiledTime < $rootTime || $this -> alwaysRebuild)
				{
					$result = file_get_contents($this -> root.$filename);
				}
			}
			else
			{
				$result = $callback($this, $filename, $compiledTime);
			}
			
			if($result === false)
			{
				return $compiled;
			}
			
			if(!is_object($this -> compiler))
			{
				require_once(OPT_DIR.'opt.compiler.php');
				$this -> compiler = new optCompiler($this);
			}
			$this -> compiler -> parse($this -> compile.$compiled, $result);
			return $compiled;
		} // end needCompile();
		
		public function getTemplate($filename)
		{
			$resource = 'file';
			if(strpos($filename, ':') !== FALSE)
			{
				$data = explode(':', $filename);
				$filename = $data[1];
				$resource = $data[0];
				
				if(!isset($this -> resources[$resource]))
				{
					$this -> error(E_USER_ERROR, 'Specified resource type: '.$resource.' does not exist.', OPT_E_RESOURCE_NOT_FOUND);
				}
				$callback = $this -> resources[$resource];
			}
			$compiler = new optCompiler($this -> compiler);
			if($resource == 'file')
			{
				$result = file_get_contents($this -> root.$filename);
			}
			else
			{
				$result = $callback($this, $filename);
			}
			return $compiler -> parse(NULL, $result);
		} // end getFilename();
		
		# OUTPUT_CACHING
		private function cacheProcess($filename)
		{
			if($this -> isCached($filename, $this -> cacheId))
			{
				if($this -> cacheDynamic)
				{
					$oldErrorReporting = error_reporting(E_ALL ^ E_NOTICE);
					include($this -> cache.$this->cacheFilename);
					error_reporting($oldErrorReporting);			
				}
				else
				{
					echo file_get_contents($this -> cache.$this->cacheFilename);
				}
				return true;
			}
			else
			{
				ob_start();
				return false;
			}
		} // end cacheProcess();
		
		private function cacheWrite($compiled, $dynamic)
		{
			// generate the file
			$header = array(
				'timestamp' => time(),
				'dynamic' => $dynamic,
				'expire' => $this -> cacheExpire
			);
			file_put_contents($this->cache.$this -> cacheFilename.'.def', serialize($header));
			
			if(!$dynamic)
			{
				file_put_contents($this->cache.$this -> cacheFilename, ob_get_contents());
			}
			else
			{
				// Build the dynamic source
				$dynamicCodes = unserialize(file_get_contents($this -> compile.$compiled.'.dyn'));
				$content = '';
				foreach($this -> outputBuffer as $id => &$buffer)
				{
					$content .= $buffer;
					if(isset($dynamicCodes[$id]))
					{
						$content .= $dynamicCodes[$id];
					}
				}
				file_put_contents($this->cache.$this -> cacheFilename, $content.ob_get_contents());			
			}
		} // end cacheWrite();
		# /OUTPUT_CACHING
		
		# HTTP_HEADERS
		protected function header($header)
		{
			header($header);
		} // end header();
		
		# /HTTP_HEADERS
		
		private function loadPlugins()
		{	
			$this -> instructionFiles[] = $this -> plugins.'compile.php';
			if(file_exists($this -> plugins.'plugins.php'))
			{
				// Load precompiled plugin database
				include($this -> plugins.'plugins.php');
			}
			else
			{
				// Compile plugin database
				if(!is_writeable($this -> plugins))
				{
					$this -> error(E_USER_ERROR, $this->plugins.' is not a writeable directory.', OPT_E_WRITEABLE);
				}

				$code = '';
				$compileCode = '';
				$file = '';
				$dir = opendir($this -> plugins);
				while($file = readdir($dir))
				{
					if(preg_match('/(component|instruction|function|prefilter|postfilter|outputfilter|resource)\.([a-zA-Z0-9\_]+)\.php/', $file, $matches))
					{
						switch($matches[1])
						{
							# COMPONENTS
							case 'component':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->components['".$matches[2]."'] = 1;\n";
								break;
							# /COMPONENTS
							case 'instruction':
								$compileCode .= "\trequire(\$this -> tpl-> plugins.'".$file."');\n";
								$compileCode .= "\t\$this->tpl->control[] = '".$matches[2]."';\n";
								break;
							case 'function':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->functions['".$matches[2]."'] = '".$matches[2]."';\n";
								break;
							case 'prefilter':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->filters['pre'][] = 'optPrefilter".$matches[2]."';\n";
								break;
							case 'postfilter':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->filters['post'][] = 'optPostfilter".$matches[2]."';\n";
								break;
							case 'outputfilter':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->filters['output'][] = 'optOutputfilter".$matches[2]."';\n";
								break;
							case 'resource':
								$code .= "\trequire(\$this -> plugins.'".$file."');\n";
								$code .= "\t\$this->resources[".$matches[2]."] = \$".$matches[4].";\n";
								break;
						}	
					}
				}
				closedir($dir);
				file_put_contents($this -> plugins.'plugins.php', '<'."?php\n".$code.'?'.'>');
				file_put_contents($this -> plugins.'compile.php', '<'."?php\n".$compileCode.'?'.'>');
				eval($code);
			}
			return 1;
		} // end loadPlugins();
	}
	
	// Functions
	
	function optCompileFilenameFull($filename)
	{
		if(strpos($filename, ':') !== FALSE)
		{
			$resource = explode(':', $filename);
			$filename = $resource[1];
		}
		return '%%'.str_replace(array('/', '\\'), '_', $filename);
	} // end optCompileFilenameFull();
	
	function optCompileFilename($filename)
	{
		return '%%'.str_replace(array('/', ':', '\\'), '_', $filename);
	} // end optCompileFilename();
	
	function optCacheFilename($filename, $id = '')
	{
		return str_replace(array('|', '/', '\\'),'^',$id).'_'.base64_encode(dirname($filename)).basename($filename);
	} // end cd();
?>
