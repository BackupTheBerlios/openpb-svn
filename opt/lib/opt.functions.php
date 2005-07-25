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
	 
	function opt_predef_parse_int(opt_template $tpl, $bigint)
	{
		if(!isset($tpl -> conf['parse_int_separator']))
		{
			$sep = ',';
		}
		else
		{
			$sep = $tpl -> conf['parse_int_separator'];
		}
	
		$bigint = (string)$bigint;
		for($i = strlen($bigint) - 1, $x = 0; $i >= 0; $i--, $x++)
		{
			$ret .= $bigint{$i};
			if($x == 2 && $i != 0)
			{
				$ret .= $sep;
				$x = -1;
			}
		}
		return strrev($ret);
	} // end opt_predef_parse_int();
	
	function opt_predef_wordwrap(opt_template $tpl, $text, $width, $break = 0)
	{
		if(is_string($break))
		{
			$break = str_replace('\\n', "\n", $break);
		}
		else
		{
			$break = "\n";
		}
 
		return wordwrap($text, $width, $break);
	} // end opt_predef_wordwrap();
	
	function opt_predef_apply(opt_template $tpl, $group, $item)
	{
		$args = func_get_args();
		unset($args[0]);
		unset($args[1]);
		unset($args[2]);
		$tpl -> lang[$group][$item] = vsprintf($tpl -> lang[$group][$item], $args);
	} // end opt_predef_apply();
	
	function opt_predef_cycle(opt_template $tpl)
	{
		$args = func_get_args();
	
		static $i;
		if(!isset($i))
		{
			$i = 1;
		}
		
		if($i >= count($args))
		{
			$i = 1;
		}
		
		return $args[$i++];
	} // end opt_predef_cycle();
?>
