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
  // $Id: opt.error.php 55 2006-06-10 12:00:48Z zyxist $
  
	// Error message codes
	define('OPT_E_CONTENT_TYPE', 1);
	define('OPT_E_ARRAY_REQUIRED', 2);
	define('OPT_E_RESOURCE', 3);
	define('OPT_E_FILTER', 4);
	define('OPT_E_RESOURCE_NOT_FOUND', 5);
	define('OPT_E_FILE_NOT_FOUND', 6);
	define('OPT_E_WRITEABLE', 7);
	define('OPT_E_ENCLOSING_STATEMENT', 101);
	define('OPT_E_UNKNOWN', 102);
	define('OPT_E_FUNCTION_NOT_FOUND', 103);
	define('OPT_E_CONSTANT_NOT_FOUND', 104);
	define('OPT_E_COMMAND_NOT_FOUND', 105);
	define('OPT_E_EXPRESSION', 106);
	define('OPT_E_REQUIRED_NOT_FOUND', 107);
	define('OPT_E_INVALID_PARAMETER', 108);
	define('OPT_E_DEFAULT_MARKER', 109);
	define('OPT_E_UNKNOWN_PARAM', 110);
	define('OPT_E_PARAM_STYLE', 111);
	define('OPT_W_LANG_NOT_FOUND', 151);
	define('OPT_E_IF_ELSEIF', 201);
	define('OPT_E_IF_ELSE', 202);
	define('OPT_E_IF_END', 203);
	define('OPT_E_CAPTURE_SUB', 204);
	define('OPT_E_FOR_END', 205);
	define('OPT_E_FOREACH_ELSE', 206);
	define('OPT_E_FOREACH_END', 207);
	define('OPT_E_BIND_NOT_FOUND', 208);
	define('OPT_W_DYNAMIC_OPENED', 301);
	define('OPT_W_DYNAMIC_CLOSED', 302);

	class optException extends Exception
	{
		private $func;
		private $type;
		private $filename;
		public $directories;

		public function __construct($message = null, $code = null, $type=null, $file = null, $line = null, $function = null, $filename = null)
		{
			$this -> message = $message;
			$this -> code = $code;
			$this -> file = $file;
			$this -> line = $line;
			$this -> func = $function;
			$this -> type = $type;
			$this -> filename = $filename;
		} // end __construct();
		
		public function getFunction()
		{
			return $this -> func;
		} // end getFunction();
		
		public function getType()
		{
			return $this -> type;
		} // end getType();
		
		public function getFilename()
		{
			return $this -> filename;
		} // end getFilename();
	}
	
	function optErrorHandler(optException $exc)
	{
		echo '<div class="error opt">
			<p class="message"><strong> '.$exc->getType().' internal error #'.$exc->getCode().'</strong>:  '.$exc->getMessage().'</p>';
		if($exc->getCode() >= 100)
		{
			echo '<p class="location">Method: "<em>'.$exc->getFunction().'</em>"; Template: "<em>'.$exc->getFilename().'</em>"; File: "<em>'.$exc->getFile().'</em>"; Line: "<em>'.$exc->getLine().'</em>"</p>';
		}
		else
		{
			echo '<p class="location">Method: "<em>'.$exc->getFunction().'</em>"; File: "<em>'.$exc->getFile().'</em>"; Line: "<em>'.$exc->getLine().'</em>"</p>';			
		}
		echo '</div>';
		$trace = array_reverse($exc -> getTrace());
			
		
		echo '<div class="debug opt">
			<h3>Debug backtrace</h3>
			<table style="width: 70%; border: 1px solid #000000;">';
		echo '<tr>
			<td style="width: 20; background: #DDDDDD; font-weight: bold;">#</td>
			<td style="width: 30%; background: #DDDDDD; font-weight: bold;">In file</td>
			<td style="width: *; background: #DDDDDD; font-weight: bold;">Call</td>
			<td style="width: 7%; background: #DDDDDD; font-weight: bold;">Line</td>
		</tr>';
		foreach($trace as $number => $item)
		{
			if(isset($item['class']))
			{
				$callback = $item['class'].$item['type'].$item['function'];				
			}
			else
			{
				$callback = $item['function'];
			}
			echo '<tr>
				<td>'.$number.'</td>
				<td>'.basename($item['file']).'</td>
				<td>'.$callback.'</td>
				<td>'.$item['line'].'</td>
			</tr>';
		}
		echo '</table>';
		echo '<h3>Directories</h3>
			<table style="width: 50%; border: 1px solid #000000;">';
		echo '<tr>
			<td style="width: 30%; background: #DDDDDD; font-weight: bold;">Directory</td>
			<td style="width: 30%; background: #DDDDDD; font-weight: bold;">Value</td>
			<td style="width: 40%; background: #DDDDDD; font-weight: bold;">Status</td>
		</tr>';
			
		foreach($exc -> directories as $type => $data)
		{
			// checking status
			if($data == NULL)
			{
				$status = 'Not set';				
			}
			elseif(is_dir($data))
			{
				$status = '<span style="color: green; font-weight: bold;">Exists</span>';
			}
			else
			{
				$status = '<span style="color: red; font-weight: bold;">Not exists</span>';
			}
			echo '<tr>
				<td>'.$type.'</td>
				<td>'.$data.'</td>
				<td>'.$status.'</td>
			</tr>';
		}
		echo '</table>
		<p>Open Power Template '.OPT_VERSION.'</p>
		</div>';
	} // end optErrorHandler();

?>
