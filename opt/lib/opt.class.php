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

	require('opt.error.php');
	require('opt.functions.php');
	require('opt.components.php');
	require('opt.filters.php');
	require('opt.resources.php');

	class opt_template
	{
		public $data;
		public $vars;
		public $conf;
		public $res;
		public $compiler;
		public $functions;
		public $php_functions;
		public $control;
		public $components;
		public $delimiters;

		public $lang;
		public $i18n_type;

		protected $init;
		protected $gzip_compression;

		public $code_filters;
		public $capture;
		public $capture_to = 'echo';
		public $capture_def = 'echo';
		# ER_PROTECTION
		private $old_error_reporting;
		# /ER_PROTECTION
		
		private $output_buffer;
		# DEBUG_CONSOLE
		private $debug_output;
		private $debug_config;
		private $debug_code;
		# /DEBUG_CONSOLE
		# OUTPUT_CACHING
		private $cache_status;
		private $cache_expires;
		private $cache_id;
		private $cache_output;
		private $cache_test_result;
		private $cache_resource;
		private $cache_header;
		# /OUTPUT_CACHING
		
		public $compile_code;
		private $included_files;
		private $test_included_files;
		
		public function __construct()
		{
			$this -> data = array();
			$this -> conf = array();
			$this -> vars = array();
			$this -> capture = array();
			$this -> included_files = array();
			$this -> compiler = NULL;
			$this -> res = NULL;
			$this -> cache_test = 0;
			$this -> compile_code = '';

			// registering predefined elements
			$this -> functions['parse_int'] = 'predef_parse_int';
			$this -> functions['wordwrap'] = 'predef_wordwrap';
			$this -> functions['apply'] = 'predef_apply';
			$this -> functions['cycle'] = 'predef_cycle';

			$this -> php_functions['upper'] = 'strtoupper';
			$this -> php_functions['lower'] = 'strtolower';
			$this -> php_functions['capitalize'] = 'ucfirst';
			$this -> php_functions['trim'] = 'trim';
			$this -> php_functions['length'] = 'strlen';
			$this -> php_functions['count_words'] = 'str_word_count';
			$this -> php_functions['count'] = 'count';
			$this -> php_functions['date'] = 'date';
			
			$this -> components['selectComponent'] = 1;
			$this -> components['textInputComponent'] = 1;
			$this -> components['textLabelComponent'] = 1;
			$this -> components['formActionsComponent'] = 1;

			$this -> control = array(
				'opt_section',
				'opt_include',
				'opt_var',
				'opt_if',
				'opt_php',
				'opt_for',
				'opt_foreach',
				'opt_capture',
				'opt_dynamic',
				'opt_default'
			);

			$this -> delimiters = array(0 => '\{(\/?)(.*?)\}');

			$this -> code_filters = array(
				'pre' => array('trim'),
				'post' => NULL,
				'output' => NULL
			);
		} // end __construct();

		public function __destruct()
		{
			# DEBUG_CONSOLE
			if($this -> conf['debug_console'])
			{
				$this -> data['config'] = &$this->debug_config;
				$this -> data['files'] = &$this->debug_output;
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
			if(count($this -> code_filters['output']) > 0)
			{
				$output = ob_get_clean();
				foreach($this -> code_filters['output'] as $filter)
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
			$d_file = 'Unknown';
			$d_line = '0';
			$d_function = 'main';
			$trace = debug_backtrace();
			for($i = count($trace) - 1; $i >= 0; $i--)
			{
				if(isset($trace[$i]['class']))
				{
					if($trace[$i]['class'] == 'opt_template' || $trace[$i]['class'] == 'opt_compiler')
					{
						$d_file = $trace[$i]['file'];
						$d_line = $trace[$i]['line'];
						$d_function = $trace[$i]['function'];
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
			if($type == E_USER_WARNING && $this -> conf['show_warnings'] == 1)
			{
				echo '<br/><b>'.$n_type.' warning #'.$code.':</b> '.$msg.' <i>Generated by OPT method `'.$d_function.'` called in '.$d_file.' on line '.$d_line.'</i><br/>';
			}
			elseif($type == E_USER_ERROR)
			{
				// Send the exception
				$exception = new opt_exception($msg, $code, $n_type, $d_file, $d_line, $d_function, $this -> conf['trace']);
				
				if($this -> conf['trace'])
				{
					$exception -> directories = array('root' => $this->conf['root'],
						'compile' => $this->conf['compile'],
						'cache' => $this->conf['cache'],
						'plugins' => $this->conf['plugins']
					);			
				}
				throw $exception;
			}	
		} // end error();
		
		public function init()
		{
			if(count($this -> conf) > 0)
			{
				$predefined_options = array(
					'root' => NULL,
					'compile' => NULL,
					'plugins' => NULL,
					'cache' => NULL,
					# GZIP_SUPPORT
					'gzip_compression' => 1,
					# /GZIP_SUPPORT
					# DISABLED_CC
					'compile_cache_disabled' => 0,
					'show_source' => 0,
					# /DISABLED_CC
					'show_warnings' => 0,		
					'charset' => 'iso-8859-1',
					'safe_mode' => 0,
					'cut_whitechars' => 0,
					'ldelim' => '{',
					'rdelim' => '}',
					'debug_console' => 0,
					'trace' => 0,
					'rewrite_warnings' => 0,
					'include_optimization' => 0,
					'xmlsyntax_mode' => 0,
					'strict_syntax' => 0
				);
				// set the default values to prevent from E_NOTICE's
				foreach($predefined_options as $id => $val)
				{
					if(!isset($this->conf[$id]))
					{
						$this->conf[$id] = $val;
					}
				}
				// make OPT alive
				if($this -> conf['cut_whitechars'] == 1)
				{
					$this -> code_filters['pre'][] = 'opt_prefilter_cw';
				}

				$this -> load_plugins();
				
				if($this -> conf['xmlsyntax_mode'] == 1)
				{
					$this -> delimiters[] = '\<(\/?)opt\:(.*?)\>';
					$this -> delimiters[] = '\<opt\:(.*?)\/\>';
					$this -> delimiters[] = 'opt\:put\=\"(.*?[^\\\\])\"';
				}			
				# GZIP_SUPPORT
				if($this -> conf['gzip_compression'] == 1 && extension_loaded('zlib') && ini_get('zlib.output_compression') == 0)
				{
					$this -> gzip_compression = 1;
					ob_start('ob_gzhandler');
					ob_implicit_flush(0);
				}
				else
				{
				# /GZIP_SUPPORT
					ob_start();
					ob_implicit_flush(0);
				# GZIP_SUPPORT
				}
				# /GZIP_SUPPORT

				// Validating paths
				$this -> conf['root'] = $this -> validate_dir($this -> conf['root'], 'root');
				$this -> conf['compile'] = $this -> validate_dir($this -> conf['compile'], 'compile cache');
				if(strlen($this -> conf['cache']) > 0)
				{
					$this -> conf['cache'] = $this -> validate_dir($this -> conf['cache'], 'output cache');
				}
		
				# DISABLED_CC
				if($this -> conf['compile_cache_disabled'] == 1)
				{
					require_once('opt.compiler.php');
					$this -> compiler = new opt_compiler($this);
					$this -> error(E_USER_WARNING, 'The compile cache is disabled. It is recommended to enable this option due to much better performance.', 4);
				}
				# /DISABLED_CC
				
				$this -> resources['file'] = new opt_resource_files($this);
				$this -> init = 1;
				# DEBUG_CONSOLE
				if($this->conf['debug_console'])
				{
					// debug console enabled, initialize it
					foreach($this -> conf as $name => $value)
					{
						$this -> debug_config[] = array('name' => $name, 'value' => $value);
					}
					if(ini_get('zlib.output_compression') == 1)
					{
						$this -> debug_config[] = array('name' => 'GZIP Compression status', 'value' => 'ZLib');
					}
					else
					{
						$this -> debug_config[] = array('name' => 'GZIP Compression status', 'value' => ($this -> gzip_compression == 1 ? 'OPT' : 'Not supported'));
					}
					
					//require_once('opt.compiler.php');
					//$where = strpos(__FILE__, 'opt.class.php');

					//$this -> compiler = new opt_compiler($this);
					//$this -> compiler -> parse(file_get_contents(substr(__FILE__, 0, $where).'debug.tpl'));
				}
				# /DEBUG_CONSOLE

				return 1;
			}
			return 0;
		} // end init();

		public function http_headers($content, $cache = OPT_HTTP_CACHE)
		{
			if(headers_sent())
			{
				return 0;
			}
			
			if(isset($this -> conf['charset']))
			{
				$charset = ';charset='.$this -> conf['charset'];
			}
			else
			{
				$charset = '';
			}

			switch($content)
			{		
				case OPT_HTML:
						header('Content-type: text/html'.$charset);
						break;
				case OPT_XHTML:
						if(stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml'))
						{
							header('content-type: application/xhtml+xml'.$charset);
						}
						else
						{
							header('content-type: text/html'.$charset);
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
							$this -> error(E_USER_ERROR, 'Unknown content type: '.$content, 5);
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
			if($this->conf['debug_console'])
			{
				$this -> debug_config[] = array('name' => 'HTTP Headers', 'value' => implode('<br/>', headers_list()));
			}
			# /DEBUG_CONSOLE
		} // end http_headers();

		public function set_default_i18n(&$lang)
		{
			$this -> i18n_type = 0;
			if(is_array($lang))
			{
				$this -> lang = &$lang;
			}
			else
			{
				$this -> error(E_USER_ERROR, 'First parameter must be an array.', 9);
			}
		} // end set_default_i18n();

		public function set_custom_i18n($template, $apply_class, $postfilter = NULL)
		{
			$this -> i18n_type = 1;
			$this -> lang = array(
				'template' => $template,
				'apply_class' => $apply_class	
			);

			if($postfilter != NULL)
			{
				$this -> register_filter(OPT_POSTFILTER, $postfilter);			
			}
		} // set_custom_i18n();

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
				elseif($this -> conf['rewrite_warnings'] == 1)
				{
					$this -> error(E_USER_WARNING, 'Trying to rewrite \''.$name.'\' block (value: `'.$this -> data[$name].'`) with
						`'.$value.'` value', 1);
				}
			}
			return 0;
		} // end assign();

		public function assign_group($values, $force_rewrite = 0)
		{
			if(!is_array($values))
			{
				return 0;
			}
			if($force_rewrite)
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
					elseif($this -> conf['rewrite_warnings'] == 1)
					{
						$this -> error(E_USER_WARNING, 'Trying to rewrite \''.$name.'\' block (value: `'.$this -> data[$name].'`) with
							`'.$value.'` value', 1);
					}
				}
			}
			return 1;
		} // end assign_group();

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

		public function assign_sect($name, $values)
		{
			if(is_array($values))
			{
				$this -> data[$name][] = $values;
			}
			else
			{
				return 0;
			}
		} // end assign_sect();

		public function parse($file)
		{
			$this -> capture_to = 'echo';
			$this -> capture_def = 'echo';
			return $this -> do_parse($file, 0);
		} // end parse();

		public function parse_capture($file, $destination)
		{
			$this -> capture_to = '$this -> capture[\''.$destination.'\'] .=';
			$this -> capture_def = '$this -> capture[\''.$destination.'\'] .=';
			return $this -> do_parse($file, 0);
		} // end parse();

		public function fetch($file)
		{
			$this -> output_buffer = '';
			$this -> capture_to = '$this -> output_buffer .=';
			$this -> capture_def = '$this -> output_buffer .=';
			$this -> do_parse($file, 0);
			return $this -> output_buffer;
		} // end fetch();

		private function do_parse($file, $nesting_level)
		{
			$res = $this -> get_resource_info($file, $file);
			# NESTING_LEVEL
			if($nesting_level > OPT_MAX_NESTING_LEVEL)
			{
				$this -> error(E_USER_ERROR, 'Nesting level too deep', 6);
			}
			# /NESTING_LEVEL
			$ok = 0;
			# OUTPUT_CACHING
			// Output caching enabled
			if($this -> cache_status == true)
			{
				if($this -> cache_process($file))
				{
					return 1;
				}
			}
			# /OUTPUT_CACHING

			// time generating
			# DEBUG_CONSOLE
			if($this -> conf['debug_console'])
			{
				$data = explode(' ', microtime());
				$time = $data[0] + $data[1];
			}
			# /DEBUG_CONSOLE
			# DISABLED_CC
			if($this -> conf['compile_cache_disabled'] == 1)
			{
				// The compile cache is disabled
				$code = $this -> compiler -> parse($res -> load_source($file));
				$ok = 1;
				# DEBUG_CONSOLE
				$use_cache = 'no';
				# /DEBUG_CONSOLE
			}
			else
			{
			# /DISABLED_CC
				// The compile cache is enabled
				if($res -> is_modified($file))
				{
					// The file is not compiled
					if(!is_object($this -> compiler))
					{
						require_once('opt.compiler.php');
						$this -> compiler = new opt_compiler($this);
					}
					$code = $this -> compiler -> parse($res -> load_source($file));
					$res -> save_code($file, $code);
					$ok = 1;
					# DEBUG_CONSOLE
					$use_cache = 'generating';
					# /DEBUG_CONSOLE
				}
				else
				{
					// The file is compiled
					$code = $res -> load_code($file);
					# DEBUG_CONSOLE
					$use_cache = 'reading';
					# /DEBUG_CONSOLE
					$ok = 1;
				}
			
			# DISABLED_CC
			}
			# /DISABLED_CC

			# ER_PROTECTION
			// turn off the notices for the template execution time
			// and restore the old settings					
			$this -> old_error_reporting = ini_get('error_reporting');
			error_reporting(E_ALL ^ E_NOTICE);
			# /ER_PROTECTION
			eval($code);
			# ER_PROTECTION
			error_reporting($this -> old_error_reporting);
			# /ER_PROTECTION

			// if the programmer wants the template source...
			if($this -> conf['show_source'] == 1)
			{
				$source = explode("\n", htmlspecialchars($code));
				echo '<hr/><b>Template Source:</b><br/><table border="0" width="100%">';
				foreach($source as $num => $linecode)
				{
					echo '<tr><td bgcolor="#DDDDDD" width="30">'.($num+1).'</td><td><pre>'.wordwrap($linecode, 100, "\n").'</pre></td></tr>';						
				}
				echo '</table>';
			}
			# OUTPUT_CACHING
			if($this -> cache_status == true)
			{
				if(count($this -> cache_output) == 0)
				{
					$this -> cache_write($file);
				}
				else
				{
					$this -> cache_write($file, $code);
				}
			}
			# /OUTPUT_CACHING
			
			# DEBUG_CONSOLE
			if($this -> conf['debug_console'])
			{
				$data = explode(' ', microtime());
				$time = $data[0] + $data[1] - $time;
				
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
				$this -> debug_output[] = array(
					'name' => $file,
					'problems' => $problem,
					'cache' => $use_cache,
					'exec' => round($time, 5)				
				);
			}
			# /DEBUG_CONSOLE
			if($ok == 1)
			{
				return 1;
			}
			return 0;
		} // end do_parse();
		
		private function do_include($file, $nesting_level)
		{
			$res = $this -> get_resource_info($file, $file);
			# NESTING_LEVEL
			if($nesting_level > OPT_MAX_NESTING_LEVEL)
			{
				$this -> error(E_USER_ERROR, 'Nesting level too deep', 6);
			}
			# /NESTING_LEVEL
			$ok = 0;

			// time generating
			# DEBUG_CONSOLE
			if($this -> conf['debug_console'])
			{
				$data = explode(' ', microtime());
				$time = $data[0] + $data[1];
			}
			# /DEBUG_CONSOLE

			if(!($included = in_array($file, $this->included_files)))
			{
				$res -> set_tests_status(1);
			}

			# DISABLED_CC
			if($this -> conf['compile_cache_disabled'] == 1)
			{
				// The compile cache is disabled
				$code = $this -> compiler -> parse($res -> load_source($file));
				$ok = 1;
				# DEBUG_CONSOLE
				$use_cache = 'no';
				# /DEBUG_CONSOLE
			}
			else
			{
			# /DISABLED_CC
				// the template hasn't been processed yet
				if(!$included)
				{		
					// The compile cache is enabled
					if($res -> is_modified($file))
					{
						// The file is not compiled
						if(!is_object($this -> compiler))
						{
							require_once('opt.compiler.php');
							$this -> compiler = new opt_compiler($this);
						}
						$code = $this -> compiler -> parse($res -> load_source($file));
						$res -> save_code($file, $code);
						$ok = 1;
						# DEBUG_CONSOLE
						$use_cache = 'generating';
						# /DEBUG_CONSOLE
					}
					else
					{
						// The file is compiled
						$code = $res -> load_code($file);
						# DEBUG_CONSOLE
						$use_cache = 'reading';
						# /DEBUG_CONSOLE
						$ok = 1;
					}
					$this -> included_files[] = $file;
				}
				else
				{
					// The file is compiled
					$code = $res -> load_code($file);
					# DEBUG_CONSOLE
					$use_cache = 'reading';
					# /DEBUG_CONSOLE
					$ok = 1;
				}
			# DISABLED_CC
			}
			# /DISABLED_CC
	
			# ER_PROTECTION
			// turn off the notices for the template execution time
			// and restore the old settings					
			$this -> old_error_reporting = ini_get('error_reporting');
			error_reporting(E_ALL ^ E_NOTICE);
			# /ER_PROTECTION
			eval($code);
			# ER_PROTECTION
			error_reporting($this -> old_error_reporting);
			# /ER_PROTECTION
			$res -> set_tests_status(1);
			# DEBUG_CONSOLE
			if($this -> conf['debug_console'])
			{
				$data = explode(' ', microtime());
				$time = $data[0] + $data[1] - $time;
				
				if(strpos($php_errormsg, 'Undefined') === 0 || $ $php_errormsg == '')
				{
					$problem = '<font color="green">no</font>';
				}
				else
				{
					$problem = '<font color="red">'.$php_errormsg.'</font>';
				}
				$this -> debug_output[] = array(
					'name' => $file,
					'problems' => $problem,
					'cache' => $use_cache,
					'exec' => round($time, 5)				
				);
			}
			# /DEBUG_CONSOLE
			
			if($ok == 1)
			{
				return 1;
			}
			return 0;
		} // end do_include();

		public function check_existence($file)
		{
			$res = $this -> get_resource_info($file, $file);

			if(!isset($this->test_included_files[$file]))
			{
				return $this->test_included_files[$file] = $res->template_exists($file);
			}
			else
			{
				return $this->test_included_files[$file];
			}
		} // end check_existence();

		public function register_function($name, $func = '')
		{
			if(is_array($name))
			{
				$this -> functions = $name;
				return 1;
			}
			else
			{
				if(strlen($name) > 0 && function_exists('opt_'.$func))
				{
					$this -> functions[$name] = $func;
					return 1;
				}
			}
			return 0;
		} // end register_function();

		public function register_instruction($class)
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
		} // end register_instruction();

		public function register_filter($type, $callback)
		{
			switch($type)
			{
				case 0:
						$prefix = 'opt_prefilter_';
						$idx = 'pre';
						break;
				case 1:
						$prefix = 'opt_postfilter_';
						$idx = 'post';
						break;
				case 2:
						$prefix = 'opt_outputfilter_';
						$idx = 'output';
						break;
				default:
						return 0;			
			}
			if(function_exists($prefix.$callback))
			{
				$this -> code_filters[$idx][] = $prefix.$callback;			
				return 1;
			}
			else
			{
				$this -> error(E_USER_ERROR, 'Specified '.$idx.'filter function: `'.$callback.'` does not exist!', 10);
			}
		} // end register_filter();

		# CUSTOM_RESOURCES
		public function register_resource($name, $resource_name)
		{
			if(class_exists($resource_name))
			{
				$this -> resources[$name] = new $resource_name($this);
				return 1;		
			}
			$this -> error(E_USER_ERROR, 'Specified value is not a valid resource object.', 11);	
		} // end register_resource();
		# /CUSTOM_RESOURCES

		public function unregister_filter($type, $callback)
		{
			switch($type)
			{
				case 0:
						if(isset($this -> code_filters['pre']['opt_prefilter_'.$callback]))
						{
							unset($this -> code_filters['pre']['opt_prefilter_'.$callback]);
							return 1;
						}
						break;
				case 1:
						if(isset($this -> code_filters['post']['opt_postfilter_'.$callback]))
						{
							unset($this -> code_filters['post']['opt_postfilter_'.$callback]);
							return 1;
						}
						break;
				case 2:
						if(isset($this -> code_filters['output']['opt_outputfilter_'.$callback]))
						{
							unset($this -> code_filters['output']['opt_outputfilter_'.$callback]);
							return 1;
						}
						break;		
			}
			return 0;
		} // end unregister_filter();

		public function conf_load_file($file)
		{
			if(file_exists($file) && $this -> init == 0)
			{
				$this -> conf = parse_ini_file($file);
				return 1;
			}
			return 0;
		} // end conf_load_file();

		public function conf_load_array(&$array)
		{
			if(is_array($array) && $this -> init == 0)
			{
				$this -> conf = $array;
				return 1;
			}
			return 0;
		} // end conf_load_array();

		public function conf_set($name, $value)
		{
			if($this -> init == 1)
			{
				if($name == 'gzip_compression' || $name == 'safe_mode' || $name == 'debug_console')
				{
					return 0;
				}
				if($name == 'root' || $name == 'compile' || $name == 'cache')
				{
					$this -> conf[$name] = $this -> validate_dir($this->conf[$name], $name);
				}
			}
			$this -> conf[$name] = $value;
			return 1;
		} // end conf_set();
		
		public function compile_cache_reset($resource = 'file', $filename = NULL)
		{
			if(isset($this -> resources[$resource]))
			{
				$res = $this -> resources[$resource];
			}
			else
			{
				$this -> error(E_USER_ERROR, 'Specified resource type: '.$resource.' does not exist.', 12);
			}
			$res -> compile_cache_reset($filename);
		} // end compile_cache_reset();

		# OUTPUT_CACHING
		public function cache_status($status, $expires = 3600)
		{
			$this -> cache_status = $status;
			if($expires < 2)
			{
				$this -> cache_expires = 2;
			}
			else
			{
				$this -> cache_expires = $expires;
			}		
		} // end cache_status();
		
		public function get_status()
		{
			return $this -> cache_status;
		} // end get_status();

		public function cache_unique($id = NULL)
		{
			$this -> cache_id = $id;
		} // end cache_unique();

		public function is_cached($filename, $id = NULL)
		{
			$buf = $this -> cache_id;
			if($id != NULL)
			{				
				$this -> cache_id = $id;
			}
			else
			{
				$this -> cache_id = NULL;
			}
			$c = $this->cd($filename);
			if(file_exists($c))
			{

				$ok = $this -> cache_test($c, $filename);
				if($ok == 1)
				{
					$this -> cache_test_result = 1;
				}
				else
				{
					fclose($this -> cache_resource);
					unlink($c);
					$this -> cache_test_result = 1;
				}
			}
			else
			{
				$ok = 0;
			}

			$this -> cache_id = $buf;
			return $ok;			
		} // end is_cached();

		public function cache_reset($filename = NULL, $id = NULL, $expire_time = NULL)
		{
			if($filename == NULL && $id == NULL)
			{
				$dir = opendir($this -> tpl -> conf['cache']);
				while($f = readdir($dir))
				{
					if(is_file($this -> tpl -> conf['cache'].$f) && $this -> check_expire($f, $expire_time))
					{
						unlink($this -> tpl -> conf['cache'].$f);
					}
				}
				closedir($dir);
				return 1;
			}
			elseif($filename == NULL)
			{
				$id = str_replace('|', '^', $id);
				$dir = glob($this -> tpl -> conf['cache'].$id.'*_*.*');
				foreach($dir as $file)
				{
					if(is_file($this -> tpl -> conf['cache'].$file) && $this -> check_expire($file, $expire_time))
					{
						unlink($this -> tpl -> conf['cache'].$file);
					}
				}
				return 1;
			}
			elseif($id == NULL)
			{
				$dir = glob($this -> tpl -> conf['cache'].'*_'.$this->cd($filename, true));
				foreach($dir as $file)
				{
					if(is_file($this -> tpl -> conf['cache'].$file) && $this -> check_expire($file, $expire_time))
					{
						unlink($this -> tpl -> conf['cache'].$file);
					}
				}
				return 1;
			}
			else
			{
				$id = str_replace('|', '^', $id);
				$dir = glob($this -> tpl -> conf['cache'].$id.'*_'.$this->cd($filename, true));
				foreach($dir as $file)
				{
					if(is_file($this -> tpl -> conf['cache'].$file) && $this -> check_expire($file, $expire_time))
					{
						unlink($this -> tpl -> conf['cache'].$file);
					}
				}
				return 1;
			
			}
		} // end cache_reset();

		private function cache_process($filename)
		{
			$c = $this->cd($filename);
			$this -> cache_output = array();
			if(file_exists($c))
			{
				$mod = filemtime($c);
				
				if($mod < (time() - $this -> cache_expires))
				{
					// recompilation
					ob_start();
					return 0;				
				}
				else
				{
					if($this -> cache_test_result == 0)
					{
						$result = $this -> cache_test($c, $filename);
						if($result == 0)
						{
							// recompilation
							return 0;
						}
					}
					else
					{
						$this -> cache_test_result = 0;
					}
					
					// ok, all tests passed, read
					// read the stuff
					$content = fread($this -> cache_resource, filesize($c));
					
					if($this -> cache_header['dynamic'] == true)
					{
						// there is some dynamic stuff.
						eval($content);
					}
					else
					{
						// whole file is static
						echo $content;
					}
					fclose($this -> cache_resource);
					return 1;			
				}			
			}
			return 0;		
		} // end cache_process();

		private function cache_write($filename, $code = NULL)
		{
			$c = $this->cd($filename);
			if(count($this -> cache_output) == 0)
			{
				// ok, the content is static!
				$dynamic = false;
				
				$content = ob_get_contents();
			}
			else
			{
				// the content is dynamic
				$dynamic = true;
				
				if(preg_match_all('#\/\* \#\@\#DYNAMIC\#\@\# \*\/(.+?)\/\* \#\@\#END DYNAMIC\#\@\# \*\/#i', $code, $blocks))
				{
					$content = $this -> capture_def.' \'';
					for($i = 0; $i < count($this -> cache_output); $i++)
					{
						$content .= addslashes($this->cache_output[$i]);
						$content .= '\'; ';
						$content .= $blocks[1][$i];
						$content .= ' '.$this -> capture_def.' \'';					
					}
					$content .= addslashes(ob_get_contents()).'\';';
				}
			}
			
			// generate the file
			$header = array(
				'timestamp' => time(),
				'copy_version' => filemtime($this->conf['root'].$filename),
				'dynamic' => $dynamic			
			);
			
			file_put_contents($c, serialize($header)."\n".$content);		
		} // end cache_write();
		
		private function cache_test($c, $filename)
		{
			$this -> cache_resource = fopen($c, 'r');
			$this -> cache_header = unserialize(fgetss($this -> cache_resource));
			
			if(!is_array($this -> cache_header))
			{
				// the cache is broken, recompile
				return 0;
			}
			
			// one more time we will control the timestamp
			if($this -> cache_header['timestamp'] < (time() - (int)$this -> cache_expires))
			{
				// ok, the filesystem was wrong, we should recompile this...
				return 0;
			}
			
			// is the original template unchanged?
			if($this -> cache_header['copy_version'] != filemtime($this->conf['root'].$filename))
			{
				// there are some differences, recompile
				return 0;
			}
			return 1;
		} // end cache_test();

		private function cd($filename, $raw = 0)
		{
			if($raw == 1)
			{
				return base64_encode(dirname($filename)).basename($filename);
			}
			// cache filename hashing
			if($this -> cache_id == NULL)
			{
				$id = '';
			}
			else
			{
				$id = $this -> cache_id;
			}
			return $this -> conf['cache'].str_replace('|','^',$id).'_'.base64_encode(dirname($filename)).basename($filename);
		} // end cd();
		
		private function check_expire($file, $time)
		{
			if($time == 0)
			{
				return 1;
			}

			$f = fopen($this -> conf['cache'].$file, 'r');
			$header = unserialize(fgetss($f));
			fclose($f);
			if($header['timestamp'] < (time() - $time))
			{
				return 1;
			}
			return 0;		
		} // end check_expire();
		# /OUTPUT_CACHING
		
		private function validate_dir($dir, $type)
		{
			if(!is_dir($dir))
			{
				$this -> error(E_USER_ERROR, 'the `'.$type.'` directory `'.$dir.'` does not exist.', 2);
			}
			if($dir{strlen($dir)-1} != '/')
			{
				$dir .= '/';
			}
			return $dir;		
		} // end validate_dir();
		
		private function load_plugins()
		{
			if($this -> conf['plugins'] == NULL)
			{
				return 0;
			}

			$this -> conf['plugins'] = $this -> validate_dir($this -> conf['plugins'], 'plugin');

			if(file_exists($this -> conf['plugins'].'plugins.php'))
			{
				// Load precompiled plugin database
				require($this -> conf['plugins'].'plugins.php');	
			}
			else
			{
				// Compile plugin database
				if(!is_dir($this -> conf['plugins']))
				{
					return 0;
				}

				$code = '';
				$file = '';
				$dir = opendir($this -> conf['plugins']);
				while($file = readdir($dir))
				{
					if(preg_match('/(component|instruction|function|prefilter|postfilter|outputfilter|resource)\.([a-zA-Z0-9\_]+)\.php/', $file, $matches))
					{
						switch($matches[1])
						{
							case 'component':
								$code .= "\trequire(\$this -> conf['plugins'].'".$file."');\n";
								$code .= "\t\$this->components['".$matches[2]."'] = 1;\n";
								break;
							case 'instruction':
								$this -> compile_code .= "\trequire(\$this -> tpl-> conf['plugins'].'".$file."');\n";
								$this -> compile_code .= "\t\$this->tpl->control[] = '".$matches[2]."';\n";
								break;
							case 'function':
								$code .= "\trequire(\$this -> conf['plugins'].'".$file."');\n";
								$code .= "\t\$this->functions['".$matches[2]."'] = '".$matches[2]."';\n";
								break;
							case 'prefilter':
								$code .= "\trequire(\$this -> conf['plugins'].'".$file."');\n";
								$code .= "\t\$this->code_filters['pre'][] = 'opt_prefilter_".$matches[2]."';\n";
								break;
							case 'postfilter':
								$code .= "\trequire(\$this -> conf['plugins'].'".$file."');\n";
								$code .= "\t\$this->code_filters['post'][] = 'opt_postfilter_".$matches[2]."';\n";
								break;
							case 'outputfilter':
								$code .= "\trequire(\$this -> conf['plugins'].'".$file."');\n";
								$code .= "\t\$this->code_filters['output'][] = 'opt_outputfilter_".$matches[2]."';\n";
								break;
							case 'resource':
								$code .= "\trequire(\$this -> conf['plugins'].'".$file."');\n";
								$code .= "\t\$this->resources[".$matches[2]."] = new \$".$matches[4]."(\$this);\n";
								break;
						}	
					}
				}
				closedir($dir);
				file_put_contents($this -> conf['plugins'].'plugins.php', "<?php\n".$code."?>");
				file_put_contents($this -> conf['plugins'].'compile.php', "<?php\n".$this -> compile_code."?>");
				eval($code);
			}
			return 1;
		} // end load_plugins();
		
		public function get_resource_info($path, &$file)
		{
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
					$this -> error(E_USER_ERROR, 'Specified resource type: '.$res_name.' does not exist.', 12);
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
		} // end get_resource_info();
	}
?>
