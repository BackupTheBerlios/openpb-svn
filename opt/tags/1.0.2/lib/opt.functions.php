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
  // $Id: opt.functions.php 41 2006-02-26 16:48:09Z zyxist $
	 
	function optPredefParseInt($tpl, $bigint)
	{
		//return number_format($bigint, $tpl -> parseintDecimals, $tpl -> parseintDecPoint, $tpl -> parseintThousands);
		return rtrim(number_format($bigint, $tpl -> parseintDecimals, $tpl -> parseintDecPoint, $tpl -> parseintThousands), $tpl->parseintDecPoint.'0');
	} // end optPredefParseInt();
	
	function optPredefWordwrap($tpl, $text, $width, $break = 0)
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
	} // end optPredefWordwrap();
	
	function optPredefApply($tpl, $group, $item)
	{
		$args = func_get_args();
		unset($args[0]);
		unset($args[1]);
		unset($args[2]);
		$tpl -> i18n[$group][$item] = vsprintf($tpl -> i18n[$group][$item], $args);
	} // end optPredefApply();
	
	function optPredefCycle($tpl)
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
	} // end optPredefCycle();
?>
